<?php

namespace App\Http\Controllers;

use App\Models\MainTask;
use App\Models\TaskActivity;
use App\Models\ActivityAssignment;
use App\Models\ActivityReport;
use App\Models\TaskCategory;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
        $isManager = count(array_intersect($userRoleNames, ['System Admin','CEO','HOD','Manager','Director'])) > 0;

        $statusFilter = $request->query('status', '');
        $leaderFilter = $request->query('leader', '');
        $priorityFilter = $request->query('priority', '');
        $searchFilter = $request->query('search', '');

        $query = MainTask::with(['teamLeader', 'activities']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($priorityFilter) {
            $query->where('priority', $priorityFilter);
        }

        if ($searchFilter) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('name', 'like', "%{$searchFilter}%")
                  ->orWhere('description', 'like', "%{$searchFilter}%")
                  ->orWhere('category', 'like', "%{$searchFilter}%");
            });
        }

        if ($isManager && $leaderFilter) {
            $query->where('team_leader_id', $leaderFilter);
        }

        if ($isManager) {
            $mainTasks = $query->orderByDesc('created_at')->get();
        } else {
            // Staff view - only tasks they're assigned to or leading
            $mainTasks = MainTask::with(['teamLeader', 'activities'])
                ->where(function($q) use ($user) {
                    $q->where('team_leader_id', $user->id)
                      ->orWhereHas('activities.assignedUsers', function($subQ) use ($user) {
                          $subQ->where('user_id', $user->id);
                      });
                })
                ->when($statusFilter, function($q) use ($statusFilter) {
                    $q->where('status', $statusFilter);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        $users = User::orderBy('name')->get(['id','name']);

        // Calculate dashboard stats
        $dashboardStats = [];
        if ($isManager) {
            $dashboardStats = [
                'total' => $mainTasks->count(),
                'in_progress' => $mainTasks->where('status', 'in_progress')->count(),
                'completed' => $mainTasks->where('status', 'completed')->count(),
                'overdue' => $mainTasks->filter(function($task) {
                    return !empty($task->end_date) && 
                           $task->end_date < now() && 
                           $task->status !== 'completed';
                })->count(),
            ];
        } else {
            $pendingActivities = TaskActivity::whereHas('assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['Not Started', 'In Progress'])->count();

            $overdueActivities = TaskActivity::whereHas('assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', '!=', 'Completed')
              ->whereNotNull('end_date')
              ->where('end_date', '<', now())
              ->count();

            $dashboardStats = [
                'total_tasks' => $mainTasks->count(),
                'pending' => $pendingActivities,
                'overdue' => $overdueActivities,
            ];
        }

        // Eager load extra context for the new simplified UI
        $mainTasks->load([
            'teamLeader:id,name',
            'activities.assignedUsers:id,name',
            'activities.reports' => function ($q) {
                $q->latest('report_date')->latest();
            },
        ]);

        // Flatten activities for quick selects in the UI
        $flatActivities = $mainTasks->flatMap(function ($task) {
            return $task->activities->map(function ($activity) use ($task) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'task' => $task->name,
                    'task_id' => $task->id,
                    'status' => $activity->status,
                    'end_date' => $activity->end_date,
                    'priority' => $activity->priority ?? 'Normal',
                ];
            });
        })->values();

        // Surface the most recent reports that are waiting for action
        $pendingReportsQuery = ActivityReport::with([
            'user:id,name',
            'approver:id,name',
            'activity:id,name,main_task_id',
            'activity.mainTask:id,name,team_leader_id',
        ])->where('status', 'Pending')
          ->orderByDesc('created_at');

        if (!$isManager) {
            $pendingReportsQuery->where('user_id', $user->id);
        }

        $pendingReports = $pendingReportsQuery->limit(8)->get();

        $categories = TaskCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modules.tasks.simple', [
            'mainTasks' => $mainTasks,
            'users' => $users,
            'isManager' => $isManager,
            'dashboardStats' => $dashboardStats,
            'categories' => $categories,
            'pendingReports' => $pendingReports,
            'flatActivities' => $flatActivities,
            'filters' => [
                'status' => $statusFilter,
                'leader' => $leaderFilter,
                'priority' => $priorityFilter,
            ],
        ]);
    }

    public function action(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401)->header('Content-Type', 'application/json');
            }

            $userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
            $isManager = count(array_intersect($userRoleNames, ['System Admin','CEO','HOD','Manager','Director'])) > 0;

            $action = $request->input('action');
            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action parameter is required'
                ], 400)->header('Content-Type', 'application/json');
            }

            return DB::transaction(function () use ($request, $user, $isManager, $action) {
            switch ($action) {
                case 'task_create_main':
                    if (!$isManager) abort(403);
                    
                    $tags = $request->input('tags');
                    $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];
                    
                    $mainTask = MainTask::create([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'team_leader_id' => $request->integer('team_leader_id'),
                        'status' => $request->string('status', 'in_progress'),
                        'priority' => $request->string('priority', 'Normal'),
                        'category' => $request->input('category'),
                        'tags' => $tagsArray,
                        'budget' => $request->input('budget'),
                        'created_by' => $user->id,
                    ]);

                    // Create initial activities if provided
                    $assignedUserIds = [];
                    if ($request->has('activities') && is_array($request->input('activities'))) {
                        foreach ($request->input('activities') as $activityData) {
                            if (!empty($activityData['name'])) {
                                $activity = TaskActivity::create([
                                    'main_task_id' => $mainTask->id,
                                    'name' => $activityData['name'],
                                    'start_date' => $activityData['start_date'] ?? null,
                                    'status' => 'Not Started',
                                ]);

                                // Assign users to activity
                                if (isset($activityData['users']) && is_array($activityData['users'])) {
                                    foreach ($activityData['users'] as $userId) {
                                        ActivityAssignment::create([
                                            'activity_id' => $activity->id,
                                            'user_id' => $userId,
                                            'assigned_by' => $user->id,
                                        ]);
                                        $assignedUserIds[] = $userId;
                                    }
                                }
                            }
                        }
                    }

                    // Send SMS notifications
                    try {
                        // Notify team leader
                        if ($mainTask->team_leader_id) {
                            $teamLeader = User::find($mainTask->team_leader_id);
                            if ($teamLeader) {
                                $message = "New Task Assigned: You have been assigned as team leader for task '{$mainTask->name}'. Priority: {$mainTask->priority}.";
                                $this->notificationService->notify(
                                    $teamLeader->id,
                                    $message,
                                    route('modules.tasks'),
                                    'New Task Assignment'
                                );
                            }
                        }

                        // Notify assigned users
                        if (!empty($assignedUserIds)) {
                            $uniqueUserIds = array_unique($assignedUserIds);
                            foreach ($uniqueUserIds as $userId) {
                                $assignedUser = User::find($userId);
                                if ($assignedUser && $userId != $mainTask->team_leader_id) {
                                    $message = "Task Activity Assigned: You have been assigned to activity in task '{$mainTask->name}'.";
                                    $this->notificationService->notify(
                                        $userId,
                                        $message,
                                        route('modules.tasks'),
                                        'Task Activity Assignment'
                                    );
                                }
                            }
                        }

                        \Log::info('Task created - SMS notifications sent', [
                            'task_id' => $mainTask->id,
                            'team_leader_id' => $mainTask->team_leader_id,
                            'assigned_users' => $uniqueUserIds ?? []
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_create_main: ' . $e->getMessage());
                    }

                    // Log activity
                    ActivityLogService::logCreated($mainTask, "Created main task: {$mainTask->name}", [
                        'task_name' => $mainTask->name,
                        'status' => $mainTask->status,
                        'priority' => $mainTask->priority,
                        'team_leader_id' => $mainTask->team_leader_id,
                        'activities_count' => count($request->input('activities', [])),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Main task created successfully!']);

                case 'task_edit_main':
                    if (!$isManager) abort(403);
                    
                    $mainTask = MainTask::findOrFail($request->integer('main_task_id'));
                    $oldTeamLeaderId = $mainTask->team_leader_id;
                    $oldStatus = $mainTask->status;
                    $tags = $request->input('tags');
                    $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];
                    
                    $mainTask->update([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'team_leader_id' => $request->integer('team_leader_id'),
                        'status' => $request->string('status'),
                        'priority' => $request->string('priority', 'Normal'),
                        'category' => $request->input('category'),
                        'tags' => $tagsArray,
                        'budget' => $request->input('budget'),
                    ]);

                    // Create new activities if provided
                    $newActivityIds = [];
                    if ($request->has('new_activities') && is_array($request->input('new_activities'))) {
                        foreach ($request->input('new_activities') as $activityData) {
                            if (!empty($activityData['name'])) {
                                // Calculate timeframe if not provided
                                $timeframe = $activityData['timeframe'] ?? '';
                                if (empty($timeframe) && !empty($activityData['start_date']) && !empty($activityData['end_date'])) {
                                    $start = \Carbon\Carbon::parse($activityData['start_date']);
                                    $end = \Carbon\Carbon::parse($activityData['end_date']);
                                    $diffDays = $start->diffInDays($end);
                                    $timeframe = $diffDays . ' Day(s)';
                                }

                                $activity = TaskActivity::create([
                                    'main_task_id' => $mainTask->id,
                                    'name' => $activityData['name'],
                                    'start_date' => $activityData['start_date'] ?? null,
                                    'end_date' => $activityData['end_date'] ?? null,
                                    'timeframe' => $timeframe,
                                    'status' => $activityData['status'] ?? 'Not Started',
                                    'priority' => $activityData['priority'] ?? 'Normal',
                                    'estimated_hours' => isset($activityData['estimated_hours']) ? (int)$activityData['estimated_hours'] : null,
                                ]);

                                $newActivityIds[] = $activity->id;

                                // Assign users to activity
                                if (isset($activityData['user_ids']) && is_array($activityData['user_ids'])) {
                                    foreach ($activityData['user_ids'] as $userId) {
                                        ActivityAssignment::create([
                                            'activity_id' => $activity->id,
                                            'user_id' => $userId,
                                            'assigned_by' => $user->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    // Send SMS notifications
                    try {
                        $newTeamLeaderId = $mainTask->team_leader_id;
                        $newStatus = $mainTask->status;

                        // Notify if team leader changed
                        if ($oldTeamLeaderId != $newTeamLeaderId && $newTeamLeaderId) {
                            $newLeader = User::find($newTeamLeaderId);
                            if ($newLeader) {
                                $message = "Task Leader Assignment: You have been assigned as team leader for task '{$mainTask->name}'.";
                                $this->notificationService->notify(
                                    $newLeader->id,
                                    $message,
                                    route('modules.tasks'),
                                    'Task Leader Assignment'
                                );
                            }
                        }

                        // Notify team leader if status changed
                        if ($oldStatus != $newStatus && $newTeamLeaderId) {
                            $teamLeader = User::find($newTeamLeaderId);
                            if ($teamLeader) {
                                $message = "Task Status Updated: Task '{$mainTask->name}' status has been changed to '{$newStatus}'.";
                                $this->notificationService->notify(
                                    $teamLeader->id,
                                    $message,
                                    route('modules.tasks'),
                                    'Task Status Updated'
                                );
                            }
                        }

                        // Notify users assigned to new activities
                        if (!empty($newActivityIds)) {
                            foreach ($newActivityIds as $activityId) {
                                $activity = TaskActivity::with('assignedUsers')->find($activityId);
                                if ($activity && $activity->assignedUsers) {
                                    foreach ($activity->assignedUsers as $assignedUser) {
                                        if ($assignedUser->id != $mainTask->team_leader_id) {
                                            $message = "Activity Assigned: You have been assigned to activity '{$activity->name}' in task '{$mainTask->name}'.";
                                            $this->notificationService->notify(
                                                $assignedUser->id,
                                                $message,
                                                route('modules.tasks'),
                                                'Activity Assignment'
                                            );
                                        }
                                    }
                                }
                            }
                        }

                        \Log::info('Task updated - SMS notifications sent', [
                            'task_id' => $mainTask->id,
                            'status_changed' => $oldStatus != $newStatus,
                            'leader_changed' => $oldTeamLeaderId != $newTeamLeaderId,
                            'new_activities_count' => count($newActivityIds)
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_edit_main: ' . $e->getMessage());
                    }

                    // Log activity
                    $oldValues = array_intersect_key($mainTask->getOriginal(), $mainTask->getChanges());
                    ActivityLogService::logUpdated($mainTask, $oldValues, $mainTask->getChanges(), "Updated main task: {$mainTask->name}", [
                        'task_name' => $mainTask->name,
                        'status_changed' => $oldStatus != $newStatus,
                        'new_activities_count' => count($newActivityIds),
                    ]);

                    $message = 'Main task updated successfully!';
                    if (!empty($newActivityIds)) {
                        $message .= ' ' . count($newActivityIds) . ' new activity(ies) added.';
                    }

                    return response()->json(['success' => true, 'message' => $message]);

                case 'task_add_activity':
                    $mainTask = MainTask::findOrFail($request->integer('main_task_id'));
                    $activity = TaskActivity::create([
                        'main_task_id' => $mainTask->id,
                        'name' => $request->string('name'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'status' => 'Not Started',
                    ]);

                    // Send SMS notification to team leader
                    try {
                        if ($mainTask->team_leader_id) {
                            $message = "New Activity Added: Activity '{$activity->name}' has been added to task '{$mainTask->name}'.";
                            $this->notificationService->notify(
                                $mainTask->team_leader_id,
                                $message,
                                route('modules.tasks'),
                                'New Activity Added'
                            );
                            
                            \Log::info('Activity added - SMS notification sent', [
                                'activity_id' => $activity->id,
                                'task_id' => $mainTask->id,
                                'team_leader_id' => $mainTask->team_leader_id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_add_activity: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'New activity added successfully!']);

                case 'task_get_details':
                    $mainTaskId = $request->integer('main_task_id');
                    $activities = TaskActivity::with('assignedUsers')
                        ->where('main_task_id', $mainTaskId)
                        ->orderBy('created_at')
                        ->get();

                    return response()->json(['success' => true, 'activities' => $activities]);

                case 'task_update_activity':
                    $activity = TaskActivity::with('mainTask')->findOrFail($request->integer('activity_id'));
                    $oldAssignedUserIds = $activity->assignedUsers()->pluck('user_id')->toArray();
                    
                    $activity->update([
                        'name' => $request->string('name'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'priority' => $request->string('priority', 'Normal'),
                        'estimated_hours' => $request->integer('estimated_hours'),
                    ]);

                    // Update assignments
                    $activity->assignments()->delete();
                    $userIds = $request->input('user_ids', []);
                    $newAssignedUserIds = [];
                    foreach ($userIds as $userId) {
                        ActivityAssignment::create([
                            'activity_id' => $activity->id,
                            'user_id' => $userId,
                            'assigned_by' => $user->id,
                        ]);
                        $newAssignedUserIds[] = $userId;
                    }

                    // Send SMS notifications
                    try {
                        // Notify newly assigned users
                        $newlyAssigned = array_diff($newAssignedUserIds, $oldAssignedUserIds);
                        foreach ($newlyAssigned as $userId) {
                            $assignedUser = User::find($userId);
                            if ($assignedUser) {
                                $message = "Activity Assigned: You have been assigned to activity '{$activity->name}' in task '{$activity->mainTask->name}'.";
                                $this->notificationService->notify(
                                    $userId,
                                    $message,
                                    route('modules.tasks'),
                                    'Activity Assignment'
                                );
                            }
                        }

                        // Notify team leader if assignments changed
                        if ($activity->mainTask && $activity->mainTask->team_leader_id) {
                            $message = "Activity Updated: Activity '{$activity->name}' in task '{$activity->mainTask->name}' has been updated.";
                            $this->notificationService->notify(
                                $activity->mainTask->team_leader_id,
                                $message,
                                route('modules.tasks'),
                                'Activity Updated'
                            );
                        }

                        \Log::info('Activity updated - SMS notifications sent', [
                            'activity_id' => $activity->id,
                            'newly_assigned_users' => $newlyAssigned
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_update_activity: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Activity updated successfully.']);

                case 'task_submit_report':
                    $activityId = $request->integer('activity_id');
                    $activity = TaskActivity::findOrFail($activityId);

                    // Check if user is assigned to this activity
                    $isAssigned = $activity->assignedUsers()->where('user_id', $user->id)->exists();
                    if (!$isAssigned) {
                        return response()->json(['success' => false, 'message' => 'You are not assigned to this activity.']);
                    }

                    // Check for pending reports
                    $hasPendingReport = ActivityReport::where('activity_id', $activityId)
                        ->where('user_id', $user->id)
                        ->where('status', 'Pending')
                        ->exists();

                    if ($hasPendingReport) {
                        return response()->json(['success' => false, 'message' => 'You have a pending report for this activity.']);
                    }

                    $attachmentPath = null;
                    if ($request->hasFile('attachment')) {
                        $file = $request->file('attachment');
                        $attachmentPath = $file->store('activity_reports', 'public');
                    }

                    $report = ActivityReport::create([
                        'activity_id' => $activityId,
                        'user_id' => $user->id,
                        'report_date' => $request->date('report_date'),
                        'work_description' => $request->input('work_description'),
                        'next_activities' => $request->input('next_activities'),
                        'attachment_path' => $attachmentPath,
                        'completion_status' => $request->string('completion_status'),
                        'reason_if_delayed' => $request->input('reason_if_delayed'),
                        'status' => 'Pending',
                    ]);

                    // Update activity status based on completion status
                    $mainTask = $activity->mainTask;
                    $completionStatus = $request->string('completion_status');
                    $allCompleted = false;
                    
                    if ($completionStatus === 'Completed') {
                        $activity->update([
                            'status' => 'Completed',
                            'actual_end_date' => now()->toDateString(),
                        ]);

                        // Check if all activities are completed to auto-complete main task
                        $allCompleted = $mainTask->activities()->where('status', '!=', 'Completed')->count() === 0;
                        if ($allCompleted) {
                            // Don't auto-complete - only HOD can complete
                            // $mainTask->update(['status' => 'completed']);
                        }
                    } else {
                        $activity->update(['status' => 'In Progress']);
                        
                        // Update main task from planning to in_progress
                        if ($mainTask->status === 'planning') {
                            $mainTask->update(['status' => 'in_progress']);
                        }
                    }

                    // Calculate progress (will update when report is approved)
                    // Note: Progress is calculated when report is approved, not on submission

                    // Send SMS notifications
                    try {
                        $reporterName = $user->name;
                        $activityName = $activity->name;
                        $taskName = $mainTask->name;

                        // Notify team leader
                        if ($mainTask->team_leader_id) {
                            $message = "Progress Report Submitted: {$reporterName} has submitted a progress report for activity '{$activityName}' in task '{$taskName}'. Status: {$completionStatus}.";
                            $this->notificationService->notify(
                                $mainTask->team_leader_id,
                                $message,
                                route('modules.tasks'),
                                'Progress Report Submitted'
                            );
                        }

                        // Notify reporter
                        $reporterMessage = "Report Submitted: Your progress report for activity '{$activityName}' has been submitted successfully. It is pending approval.";
                        $this->notificationService->notify(
                            $user->id,
                            $reporterMessage,
                            route('modules.tasks'),
                            'Report Submitted'
                        );

                        // Notify all action owners (leaders + approvers) as SMS when a report arrives
                        $actionUserIds = [];
                        if ($mainTask->team_leader_id) {
                            $actionUserIds[] = $mainTask->team_leader_id;
                        }
                        if ($mainTask->created_by) {
                            $actionUserIds[] = $mainTask->created_by;
                        }

                        $managerRoles = ['System Admin','CEO','HOD','Manager','Director'];
                        $departmentId = optional($mainTask->teamLeader)->primary_department_id;
                        $managers = User::whereHas('roles', function($q) use ($managerRoles) {
                                $q->whereIn('name', $managerRoles);
                            })
                            ->when($departmentId, function($q) use ($departmentId) {
                                $q->where('primary_department_id', $departmentId);
                            })
                            ->pluck('id')
                            ->toArray();

                        $actionUserIds = array_unique(array_merge($actionUserIds, $managers));
                        $actionUserIds = array_values(array_filter($actionUserIds, function ($id) use ($user) {
                            return $id && $id != $user->id;
                        }));

                        if (!empty($actionUserIds)) {
                            $actionMessage = "Action Needed: Progress report for '{$activityName}' in task '{$taskName}' was submitted by {$reporterName}. Please review and act. Status: {$completionStatus}.";
                            $this->notificationService->notify(
                                $actionUserIds,
                                $actionMessage,
                                route('modules.tasks'),
                                'Progress Report Action Needed'
                            );
                        }

                        // Notify HOD if task is completed
                        if ($allCompleted && $mainTask->team_leader_id) {
                            $teamLeader = User::find($mainTask->team_leader_id);
                            if ($teamLeader && $teamLeader->primary_department_id) {
                                $message = "Task Completed: Task '{$taskName}' has been completed by the team.";
                                $this->notificationService->notifyHOD(
                                    $teamLeader->primary_department_id,
                                    $message,
                                    route('modules.tasks'),
                                    'Task Completed'
                                );
                            }
                        }

                        \Log::info('Progress report submitted - SMS notifications sent', [
                            'report_id' => $report->id,
                            'activity_id' => $activity->id,
                            'task_id' => $mainTask->id,
                            'reporter_id' => $user->id,
                            'action_owners_notified' => $actionUserIds
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_submit_report: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Report submitted successfully.']);

                case 'task_get_report_details':
                    $activityId = $request->integer('activity_id');
                    $activity = TaskActivity::with('assignedUsers')->findOrFail($activityId);
                    
                    $reports = ActivityReport::with(['user', 'approver'])
                        ->where('activity_id', $activityId)
                        ->orderByDesc('report_date')
                        ->orderByDesc('created_at')
                        ->get();

                    $isAssigned = $activity->assignedUsers()->where('user_id', $user->id)->exists();
                    $hasPendingReport = ActivityReport::where('activity_id', $activityId)
                        ->where('user_id', $user->id)
                        ->where('status', 'Pending')
                        ->exists();

                    return response()->json([
                        'success' => true,
                        'activity' => $activity,
                        'reports' => $reports,
                        'is_assigned' => $isAssigned,
                        'has_pending_report' => $hasPendingReport,
                    ]);

                case 'task_approve_report':
                    // Check authorization - only managers can approve reports
                    if (!$isManager) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only managers can approve activity reports.'
                        ], 403);
                    }

                    $reportId = $request->integer('report_id');
                    if (!$reportId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report ID is required.'
                        ], 400);
                    }

                    $report = ActivityReport::with(['user', 'activity.mainTask'])->find($reportId);
                    if (!$report) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report not found.'
                        ], 404);
                    }

                    // Check if report is pending
                    if ($report->status !== 'Pending') {
                        return response()->json([
                            'success' => false,
                            'message' => 'This report has already been reviewed. Current status: ' . $report->status
                        ], 400);
                    }

                    // HOD can only approve reports from their department
                    $isHOD = $user->hasRole('HOD') && !$user->hasAnyRole(['System Admin', 'CEO', 'HR Officer']);
                    if ($isHOD) {
                        $reportUser = $report->user;
                        if (!$reportUser || ($reportUser->primary_department_id !== $user->primary_department_id)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only approve reports from staff in your department.'
                            ], 403);
                        }
                    }

                    $report->update([
                        'status' => 'Approved',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'approver_comments' => $request->input('comments', ''),
                    ]);

                    // Auto-calculate task progress based on approved reports
                    if ($report->activity && $report->activity->mainTask) {
                        $mainTask = $report->activity->mainTask;
                        $this->calculateTaskProgress($mainTask);
                    }

                    // Send SMS notifications
                    try {
                        $reporter = $report->user;
                        $activity = $report->activity;
                        $mainTask = $activity ? $activity->mainTask : null;
                        $approverName = $user->name;

                        if ($reporter && $activity) {
                            $activityName = $activity->name ?? 'Activity';
                            $taskName = $mainTask ? $mainTask->name : 'Task';
                            $message = "Report Approved: Your progress report for activity '{$activityName}' in task '{$taskName}' has been approved by {$approverName}.";
                            $this->notificationService->notify(
                                $reporter->id,
                                $message,
                                route('modules.tasks'),
                                'Report Approved'
                            );

                            \Log::info('Report approved - SMS notification sent', [
                                'report_id' => $report->id,
                                'reporter_id' => $reporter->id,
                                'approver_id' => $user->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_approve_report: ' . $e->getMessage());
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Report approved successfully.'
                    ]);

                case 'task_reject_report':
                    // Check authorization - only managers can reject reports
                    if (!$isManager) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only managers can reject activity reports.'
                        ], 403);
                    }

                    $reportId = $request->integer('report_id');
                    if (!$reportId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report ID is required.'
                        ], 400);
                    }

                    $report = ActivityReport::with(['user', 'activity.mainTask'])->find($reportId);
                    if (!$report) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report not found.'
                        ], 404);
                    }

                    // Check if report is pending
                    if ($report->status !== 'Pending') {
                        return response()->json([
                            'success' => false,
                            'message' => 'This report has already been reviewed. Current status: ' . $report->status
                        ], 400);
                    }

                    // HOD can only reject reports from their department
                    $isHOD = $user->hasRole('HOD') && !$user->hasAnyRole(['System Admin', 'CEO', 'HR Officer']);
                    if ($isHOD) {
                        $reportUser = $report->user;
                        if (!$reportUser || ($reportUser->primary_department_id !== $user->primary_department_id)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only reject reports from staff in your department.'
                            ], 403);
                        }
                    }

                    $comments = $request->input('comments', '');
                    
                    if (empty(trim($comments))) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A comment is required to reject a report.'
                        ], 422);
                    }

                    $report->update([
                        'status' => 'Rejected',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'approver_comments' => $comments,
                    ]);

                    // Send SMS notifications
                    try {
                        $reporter = $report->user;
                        $activity = $report->activity;
                        $mainTask = $activity ? $activity->mainTask : null;
                        $approverName = $user->name;

                        if ($reporter && $activity) {
                            $activityName = $activity->name ?? 'Activity';
                            $taskName = $mainTask ? $mainTask->name : 'Task';
                            $message = "Report Rejected: Your progress report for activity '{$activityName}' in task '{$taskName}' has been rejected by {$approverName}. Please check comments.";
                            $this->notificationService->notify(
                                $reporter->id,
                                $message,
                                route('modules.tasks'),
                                'Report Rejected'
                            );

                            \Log::info('Report rejected - SMS notification sent', [
                                'report_id' => $report->id,
                                'reporter_id' => $reporter->id,
                                'approver_id' => $user->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_reject_report: ' . $e->getMessage());
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Report rejected with feedback.'
                    ]);

                case 'get_activities_for_date':
                    $date = $request->date('date');
                    
                    // Get all tasks that include this date
                    $tasksOnDate = MainTask::where(function($query) use ($date) {
                        $query->where('start_date', '<=', $date)
                              ->where('end_date', '>=', $date);
                    })->pluck('id');
                    
                    // Get all activities for these tasks that are active on this date
                    $activities = TaskActivity::whereIn('main_task_id', $tasksOnDate)
                        ->where(function($query) use ($date) {
                            $query->where(function($q) use ($date) {
                                $q->where('start_date', '<=', $date)
                                  ->where('end_date', '>=', $date);
                            })
                            ->whereIn('status', ['Not Started', 'In Progress', 'Completed']);
                        })
                        ->with(['mainTask:id,name'])
                        ->get()
                        ->map(function($activity) {
                            return [
                                'id' => $activity->id,
                                'name' => $activity->name,
                                'status' => $activity->status,
                                'priority' => $activity->priority,
                                'start_date' => $activity->start_date,
                                'end_date' => $activity->end_date,
                                'task_name' => $activity->mainTask->name ?? 'N/A',
                            ];
                        });
                    
                    return response()->json([
                        'success' => true,
                        'activities' => $activities,
                        'date' => $date->format('Y-m-d')
                    ]);

                case 'get_task_full_details':
                    $taskId = $request->integer('task_id');
                    $task = MainTask::with([
                        'teamLeader:id,name',
                        'creator:id,name',
                        'activities.assignedUsers:id,name',
                        'activities.reports.user:id,name',
                        'activities.reports.approver:id,name',
                        'activities.comments.user:id,name',
                        'activities.attachments.user:id,name',
                        'comments.user:id,name',
                        'attachments.user:id,name'
                    ])->findOrFail($taskId);
                    
                    // Calculate current progress
                    $this->calculateTaskProgress($task);
                    $task->refresh();
                    
                    return response()->json(['success' => true, 'task' => $task]);

                case 'update_task_status':
                    if (!$isManager) abort(403);
                    
                    $task = MainTask::with(['activities.assignedUsers'])->findOrFail($request->integer('task_id'));
                    $oldStatus = $task->status;
                    $newStatus = $request->string('status');
                    
                    // Only HOD can mark task as completed or closed
                    if (in_array($newStatus, ['completed', 'closed'])) {
                        $isHOD = $user->hasRole('HOD') || $user->hasRole('System Admin');
                        if (!$isHOD) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Only HOD (Head of Department) can mark tasks as completed or closed.'
                            ], 403);
                        }
                    }
                    
                    $task->update(['status' => $newStatus]);

                    // Send SMS notifications
                    try {
                        // Notify team leader
                        if ($task->team_leader_id) {
                            $message = "Task Status Updated: Task '{$task->name}' status has been changed from '{$oldStatus}' to '{$newStatus}'.";
                            $this->notificationService->notify(
                                $task->team_leader_id,
                                $message,
                                route('modules.tasks'),
                                'Task Status Updated'
                            );
                        }

                        // Notify all assigned users
                        $assignedUserIds = [];
                        foreach ($task->activities as $activity) {
                            foreach ($activity->assignedUsers as $assignedUser) {
                                if (!in_array($assignedUser->id, $assignedUserIds)) {
                                    $assignedUserIds[] = $assignedUser->id;
                                    if ($assignedUser->id != $task->team_leader_id) {
                                        $message = "Task Status Updated: Task '{$task->name}' status has been changed to '{$newStatus}'.";
                                        $this->notificationService->notify(
                                            $assignedUser->id,
                                            $message,
                                            route('modules.tasks'),
                                            'Task Status Updated'
                                        );
                                    }
                                }
                            }
                        }

                        \Log::info('Task status updated - SMS notifications sent', [
                            'task_id' => $task->id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'notified_users' => count($assignedUserIds) + ($task->team_leader_id ? 1 : 0)
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in update_task_status: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Task status updated successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Unknown action.'], 400);
        });
        } catch (\Throwable $e) {
            \Log::error('Task action error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $request->input('action', 'unknown')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Calculate task progress based on approved reports
     */
    private function calculateTaskProgress(MainTask $mainTask)
    {
        $totalActivities = $mainTask->activities()->count();
        if ($totalActivities === 0) {
            $mainTask->update(['progress_percentage' => 0]);
            return;
        }

        $totalProgress = 0;
        $activitiesWithReports = 0;

        foreach ($mainTask->activities as $activity) {
            $approvedReports = $activity->reports()->where('status', 'Approved')->count();
            $totalReports = $activity->reports()->count();
            
            // If activity has reports, calculate progress based on reports
            if ($totalReports > 0) {
                // Each approved report contributes to progress
                // Completed activities contribute 100%, others based on report count
                if ($activity->status === 'Completed') {
                    $activityProgress = 100;
                } else {
                    // Progress based on approved reports (assume multiple reports needed for completion)
                    // Each report contributes a portion, max 90% until completed
                    $baseProgress = min(90, ($approvedReports * 30)); // 30% per approved report, max 90%
                    $activityProgress = $baseProgress;
                }
                $totalProgress += $activityProgress;
                $activitiesWithReports++;
            } else {
                // Activities without reports: use status-based progress
                if ($activity->status === 'Completed') {
                    $activityProgress = 100;
                } elseif ($activity->status === 'In Progress') {
                    $activityProgress = 50;
                } else {
                    $activityProgress = 0;
                }
                $totalProgress += $activityProgress;
            }
        }

        // Calculate overall progress percentage
        $overallProgress = round($totalProgress / $totalActivities);
        $mainTask->update(['progress_percentage' => $overallProgress]);
        
        \Log::info('Task progress calculated', [
            'task_id' => $mainTask->id,
            'progress_percentage' => $overallProgress,
            'total_activities' => $totalActivities
        ]);
    }

    public function generatePdf(Request $request)
    {
        $type = $request->query('type');
        $taskId = $request->query('task_id');
        $month = $request->query('month');
        $year = $request->query('year');

        // Handle calendar export
        if ($type === 'calendar' && $month && $year) {
            return $this->generateCalendarPdf($month, $year);
        }

        // Handle summary or detailed reports
        if ($type === 'summary' || $type === 'detailed') {
            return $this->generateTasksReportPdf($type);
        }

        // Handle single task PDF (default behavior)
        if ($taskId) {
            $mainTask = MainTask::with([
                'teamLeader',
                'creator',
                'activities.assignedUsers',
                'activities.reports.user',
                'activities.reports.approver',
                'activities.comments.user',
                'comments.user',
                'attachments.user'
            ])->findOrFail($taskId);

            // Calculate current progress
            $this->calculateTaskProgress($mainTask);
            $mainTask->refresh();

            // Collect all issues/delays from reports
            $allIssues = [];
            $allDelays = [];
            foreach ($mainTask->activities as $activity) {
                foreach ($activity->reports as $report) {
                    if ($report->reason_if_delayed) {
                        $allDelays[] = [
                            'activity' => $activity->name,
                            'reporter' => $report->user->name ?? 'Unknown',
                            'date' => $report->report_date,
                            'reason' => $report->reason_if_delayed,
                            'status' => $report->status
                        ];
                    }
                    if ($report->completion_status === 'Delayed' || $report->completion_status === 'Behind Schedule') {
                        $allIssues[] = [
                            'activity' => $activity->name,
                            'reporter' => $report->user->name ?? 'Unknown',
                            'date' => $report->report_date,
                            'issue' => $report->work_description,
                            'status' => $report->completion_status
                        ];
                    }
                }
            }

            $data = [
                'mainTask' => $mainTask,
                'logoPath' => public_path('assets/img/logo.png'),
                'allIssues' => $allIssues,
                'allDelays' => $allDelays,
            ];

            $pdf = Pdf::loadView('modules.tasks.pdf-report', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $fileName = 'Task_Report_' . str_replace(' ', '_', $mainTask->name) . '_' . $taskId . '.pdf';
            
            return $pdf->stream($fileName);
        }

        // Default: return error if no valid parameters
        return redirect()->back()->with('error', 'Invalid PDF export parameters');
    }

    private function generateTasksReportPdf($type)
    {
        $mainTasks = MainTask::with(['teamLeader', 'activities'])
            ->orderBy('start_date', 'desc')
            ->get();

        foreach ($mainTasks as $task) {
            $this->calculateTaskProgress($task);
        }

        $data = [
            'mainTasks' => $mainTasks,
            'type' => $type,
            'logoPath' => public_path('assets/img/logo.png'),
        ];

        $view = $type === 'summary' ? 'modules.tasks.pdf-summary' : 'modules.tasks.pdf-detailed';
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', $type === 'summary' ? 'portrait' : 'landscape');
        
        $fileName = 'Tasks_' . ucfirst($type) . '_Report_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }

    private function generateCalendarPdf($month, $year)
    {
        $month = (int)$month;
        $year = (int)$year;
        
        // Get all tasks that overlap with the specified month
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        
        $mainTasks = MainTask::with(['teamLeader'])
            ->where(function($query) use ($monthStart, $monthEnd) {
                $query->where(function($q) use ($monthStart, $monthEnd) {
                    $q->where('start_date', '<=', $monthEnd)
                      ->where('end_date', '>=', $monthStart);
                });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Group tasks by date
        $tasksByDate = [];
        foreach ($mainTasks as $task) {
            $start = \Carbon\Carbon::parse($task->start_date);
            $end = \Carbon\Carbon::parse($task->end_date);
            $taskStart = $start->copy()->max($monthStart);
            $taskEnd = $end->copy()->min($monthEnd);
            
            for ($date = $taskStart->copy(); $date->lte($taskEnd); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                if (!isset($tasksByDate[$dateKey])) {
                    $tasksByDate[$dateKey] = [];
                }
                $tasksByDate[$dateKey][] = $task;
            }
        }

        $data = [
            'mainTasks' => $mainTasks,
            'tasksByDate' => $tasksByDate,
            'month' => $month,
            'year' => $year,
            'monthName' => \Carbon\Carbon::create($year, $month, 1)->format('F Y'),
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'logoPath' => public_path('assets/img/logo.png'),
        ];

        $pdf = Pdf::loadView('modules.tasks.pdf-calendar', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F_Y');
        $fileName = 'Calendar_Export_' . $monthName . '.pdf';
        
        return $pdf->stream($fileName);
    }

    public function analyticsPdf(Request $request)
    {
        $user = Auth::user();
        $userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
        $isManager = count(array_intersect($userRoleNames, ['System Admin','CEO','HOD','Manager','Director'])) > 0;
        
        if (!$isManager) {
            abort(403, 'Unauthorized access');
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $statusFilter = $request->query('status');

        $query = MainTask::with(['teamLeader', 'activities']);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $mainTasks = $query->orderByDesc('created_at')->get();

        // Calculate statistics
        foreach ($mainTasks as $task) {
            $this->calculateTaskProgress($task);
        }

        $totalTasks = $mainTasks->count();
        $completedTasks = $mainTasks->where('status', 'completed')->count();
        $inProgressTasks = $mainTasks->where('status', 'in_progress')->count();
        $planningTasks = $mainTasks->where('status', 'planning')->count();
        $delayedTasks = $mainTasks->where('status', 'delayed')->count();
        $avgProgress = round($mainTasks->avg('progress_percentage') ?? 0);
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Priority breakdown
        $lowPriority = $mainTasks->where('priority', 'Low')->count();
        $normalPriority = $mainTasks->filter(function($task) {
            return $task->priority === 'Normal' || $task->priority === null;
        })->count();
        $highPriority = $mainTasks->where('priority', 'High')->count();
        $criticalPriority = $mainTasks->where('priority', 'Critical')->count();

        // Category statistics
        $categoryStats = $mainTasks->groupBy('category')->map(function($tasks) {
            return [
                'count' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'avg_progress' => round($tasks->avg('progress_percentage') ?? 0)
            ];
        });

        // Team leader statistics
        $leaderStats = $mainTasks->groupBy('team_leader_id')->map(function($tasks) {
            $leader = $tasks->first()->teamLeader ?? null;
            return [
                'name' => $leader ? $leader->name : 'Unassigned',
                'count' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
            ];
        });

        $data = [
            'mainTasks' => $mainTasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'planningTasks' => $planningTasks,
            'delayedTasks' => $delayedTasks,
            'avgProgress' => $avgProgress,
            'completionRate' => $completionRate,
            'lowPriority' => $lowPriority,
            'normalPriority' => $normalPriority,
            'highPriority' => $highPriority,
            'criticalPriority' => $criticalPriority,
            'categoryStats' => $categoryStats,
            'leaderStats' => $leaderStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statusFilter' => $statusFilter,
            'generatedAt' => now()->format('d M Y, H:i:s'),
        ];

        $pdf = Pdf::loadView('modules.tasks.analytics-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $fileName = 'Tasks_Analytics_Report_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }
}
