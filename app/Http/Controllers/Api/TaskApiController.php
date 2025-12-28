<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MainTask;
use App\Models\TaskActivity;
use App\Models\ActivityAssignment;
use App\Models\ActivityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskApiController extends Controller
{
    /**
     * Get all tasks
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'CEO', 'HOD', 'Manager', 'Director']);
        
        $query = MainTask::with(['teamLeader:id,name,email', 'activities']);
        
        if (!$isManager) {
            $query->where(function($q) use ($user) {
                $q->where('team_leader_id', $user->id)
                  ->orWhereHas('activities.assignedUsers', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  });
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $tasks->map(function ($task) {
                return $this->formatTask($task);
            }),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'last_page' => $tasks->lastPage(),
            ]
        ]);
    }

    /**
     * Get my tasks
     */
    public function myTasks(Request $request)
    {
        $user = Auth::user();
        
        $tasks = MainTask::where('team_leader_id', $user->id)
            ->with(['activities'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return $this->formatTask($task);
            });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get assigned tasks
     */
    public function assigned(Request $request)
    {
        $user = Auth::user();
        
        $tasks = MainTask::whereHas('activities.assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['teamLeader:id,name,email', 'activities'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return $this->formatTask($task);
            });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get single task
     */
    public function show($id)
    {
        $user = Auth::user();
        $task = MainTask::with(['teamLeader', 'activities.assignedUsers.user'])
            ->findOrFail($id);

        // Check access
        if ($task->team_leader_id != $user->id && 
            !$task->activities->pluck('assignedUsers')->flatten()->pluck('user_id')->contains($user->id) &&
            !$user->hasAnyRole(['System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTask($task, true)
        ]);
    }

    /**
     * Create task
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'CEO', 'HOD', 'Manager', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'team_leader_id' => 'required|exists:users,id',
            'priority' => 'nullable|in:Low,Normal,High,Urgent',
            'status' => 'nullable|in:Not Started,In Progress,On Hold,Completed,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = MainTask::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'team_leader_id' => $request->team_leader_id,
            'priority' => $request->priority ?? 'Normal',
            'status' => $request->status ?? 'Not Started',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $this->formatTask($task)
        ], 201);
    }

    /**
     * Update task
     */
    public function update(Request $request, $id)
    {
        $task = MainTask::findOrFail($id);
        $user = Auth::user();

        if ($task->team_leader_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:Low,Normal,High,Urgent',
            'status' => 'nullable|in:Not Started,In Progress,On Hold,Completed,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only(['name', 'description', 'start_date', 'end_date', 'priority', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $this->formatTask($task->load('teamLeader'))
        ]);
    }

    /**
     * Complete task
     */
    public function complete($id)
    {
        $task = MainTask::findOrFail($id);
        $user = Auth::user();

        if ($task->team_leader_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $task->update(['status' => 'Completed']);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as completed'
        ]);
    }

    /**
     * Assign users to task
     */
    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'activity_id' => 'required|exists:task_activities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $activity = TaskActivity::findOrFail($request->activity_id);
        
        // Remove existing assignments
        ActivityAssignment::where('activity_id', $activity->id)->delete();
        
        // Add new assignments
        foreach ($request->user_ids as $userId) {
            ActivityAssignment::create([
                'activity_id' => $activity->id,
                'user_id' => $userId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Users assigned successfully'
        ]);
    }

    /**
     * Get task activities
     */
    public function activities($id)
    {
        $task = MainTask::with(['activities.assignedUsers.user'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $task->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'status' => $activity->status,
                    'priority' => $activity->priority,
                    'start_date' => $activity->start_date,
                    'end_date' => $activity->end_date,
                    'assigned_users' => $activity->assignedUsers->map(function ($assignment) {
                        return [
                            'id' => $assignment->user->id,
                            'name' => $assignment->user->name,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * Create activity
     */
    public function storeActivity(Request $request, $id)
    {
        $task = MainTask::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:Low,Normal,High,Urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $activity = TaskActivity::create([
            'main_task_id' => $task->id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'priority' => $request->priority ?? 'Normal',
            'status' => 'Not Started',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => $activity
        ], 201);
    }

    /**
     * Complete activity
     */
    public function completeActivity($id, $activityId)
    {
        $activity = TaskActivity::findOrFail($activityId);
        
        if ($activity->main_task_id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Activity does not belong to this task'
            ], 422);
        }

        $activity->update(['status' => 'Completed']);

        return response()->json([
            'success' => true,
            'message' => 'Activity marked as completed'
        ]);
    }

    /**
     * Submit activity report
     */
    public function submitReport(Request $request, $id, $activityId)
    {
        $validator = Validator::make($request->all(), [
            'report' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $activity = TaskActivity::findOrFail($activityId);
        
        ActivityReport::create([
            'activity_id' => $activityId,
            'user_id' => Auth::id(),
            'report' => $request->report,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully'
        ]);
    }

    private function formatTask($task, $detailed = false)
    {
        $data = [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'team_leader' => $task->teamLeader ? [
                'id' => $task->teamLeader->id,
                'name' => $task->teamLeader->name,
            ] : null,
            'activities_count' => $task->activities->count(),
            'created_at' => $task->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['activities'] = $task->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'status' => $activity->status,
                    'priority' => $activity->priority,
                ];
            });
        }

        return $data;
    }
}







