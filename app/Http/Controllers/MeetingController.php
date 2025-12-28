<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MeetingController extends Controller
{
    /**
     * Display meeting management page
     */
    public function index()
    {
        $user = Auth::user();
        $canManageMeetings = $user->hasPermission('manage_meetings') || $user->hasRole(['admin', 'super_admin', 'hod', 'ceo']);
        $canApproveMeetings = $user->hasPermission('approve_meetings') || $user->hasRole(['admin', 'super_admin', 'hod', 'ceo']);
        
        $stats = $this->getDashboardStats();
        $departments = DB::table('departments')->where('status', 'active')->get();
        
        return view('modules.meetings.index', compact('canManageMeetings', 'canApproveMeetings', 'stats', 'departments'));
    }

    /**
     * Handle AJAX requests
     */
    public function ajax(Request $request)
    {
        $action = $request->input('action');
        
        try {
            switch ($action) {
                case 'get_dashboard_stats':
                    return $this->getDashboardStatsJson();
                case 'get_categories':
                    return $this->getCategories();
                case 'create_category':
                    return $this->createCategory($request);
                case 'update_category':
                    return $this->updateCategory($request);
                case 'delete_category':
                    return $this->deleteCategory($request);
                case 'get_staff':
                    return $this->getStaff();
                case 'get_meetings':
                    return $this->getMeetings($request);
                case 'get_meeting':
                    return $this->getMeeting($request);
                case 'save_meeting_step':
                    return $this->saveMeetingStep($request);
                case 'submit_meeting':
                    return $this->submitMeeting($request);
                case 'delete_meeting':
                    return $this->deleteMeeting($request);
                case 'approve_meeting':
                    return $this->approveMeeting($request);
                case 'reject_meeting':
                    return $this->rejectMeeting($request);
                case 'get_meetings_without_minutes':
                    return $this->getMeetingsWithoutMinutes();
                case 'get_meeting_for_minutes':
                    return $this->getMeetingForMinutes($request);
                case 'save_minutes_section':
                    return $this->saveMinutesSection($request);
                case 'save_all_minutes':
                    return $this->saveAllMinutes($request);
                case 'finalize_minutes':
                    return $this->finalizeMinutes($request);
                case 'preview_minutes':
                    return $this->previewMinutes($request);
                case 'get_recent_activity':
                    return $this->getRecentActivity();
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid action']);
            }
        } catch (\Exception $e) {
            Log::error('Meeting AJAX Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'total_meetings' => DB::table('meetings')->count(),
            'upcoming' => DB::table('meetings')
                ->where('meeting_date', '>=', Carbon::today())
                ->where('status', 'approved')
                ->count(),
            'pending_approval' => DB::table('meetings')
                ->where('status', 'pending_approval')
                ->count(),
            'minutes_pending' => DB::table('meetings')
                ->where('status', 'approved')
                ->where('meeting_date', '<', Carbon::today())
                ->whereNull('minutes_finalized_at')
                ->count()
        ];
    }

    private function getDashboardStatsJson()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->getDashboardStats()
        ]);
    }

    /**
     * Get meeting categories
     */
    private function getCategories()
    {
        $categories = DB::table('meeting_categories')
            ->select('meeting_categories.*')
            ->selectRaw('(SELECT COUNT(*) FROM meetings WHERE category_id = meeting_categories.id) as meetings_count')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    /**
     * Create category
     */
    private function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:meeting_categories,name'
        ]);

        DB::table('meeting_categories')->insert([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Category created successfully']);
    }

    /**
     * Update category
     */
    private function updateCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:meeting_categories,id',
            'name' => 'required|string|max:255'
        ]);

        DB::table('meeting_categories')
            ->where('id', $request->category_id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Category updated successfully']);
    }

    /**
     * Delete category
     */
    private function deleteCategory(Request $request)
    {
        $categoryId = $request->category_id;
        
        $meetingsCount = DB::table('meetings')->where('category_id', $categoryId)->count();
        if ($meetingsCount > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with existing meetings']);
        }

        DB::table('meeting_categories')->where('id', $categoryId)->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }

    /**
     * Get staff list
     */
    private function getStaff()
    {
        $staff = DB::table('users')
            ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
            ->select('users.id', 'users.name', 'users.email', 'departments.name as department')
            ->selectRaw("CASE WHEN users.role IN ('admin', 'super_admin', 'hod', 'ceo') THEN 1 ELSE 0 END as is_approver")
            ->selectRaw("users.role")
            ->where('users.status', 'active')
            ->orderBy('users.name')
            ->get();

        return response()->json(['success' => true, 'staff' => $staff]);
    }

    /**
     * Get meetings list
     */
    private function getMeetings(Request $request)
    {
        $query = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->leftJoin('users', 'meetings.created_by', '=', 'users.id')
            ->select(
                'meetings.*',
                'meeting_categories.name as category_name',
                'users.name as creator_name'
            )
            ->selectRaw('(SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = meetings.id) as participants_count')
            ->selectRaw('(SELECT COUNT(*) FROM meeting_minutes WHERE meeting_id = meetings.id) as has_minutes');

        if ($request->status) {
            $query->where('meetings.status', $request->status);
        }
        if ($request->category_id) {
            $query->where('meetings.category_id', $request->category_id);
        }
        if ($request->search) {
            $query->where('meetings.title', 'like', '%' . $request->search . '%');
        }
        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('meetings.meeting_date', $dates);
            }
        }

        $meetings = $query->orderBy('meetings.meeting_date', 'desc')
            ->orderBy('meetings.start_time', 'desc')
            ->get();

        return response()->json(['success' => true, 'meetings' => $meetings]);
    }

    /**
     * Get single meeting details
     */
    private function getMeeting(Request $request)
    {
        $meetingId = $request->meeting_id;
        
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->select('meetings.*', 'meeting_categories.name as category_name')
            ->where('meetings.id', $meetingId)
            ->first();

        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found']);
        }

        // Get staff participants
        $meeting->staff_participants = DB::table('meeting_participants')
            ->join('users', 'meeting_participants.user_id', '=', 'users.id')
            ->where('meeting_participants.meeting_id', $meetingId)
            ->where('meeting_participants.participant_type', 'staff')
            ->select('users.id', 'users.name', 'users.email', 'meeting_participants.attended')
            ->get();

        // Get external participants
        $meeting->external_participants = DB::table('meeting_participants')
            ->where('meeting_id', $meetingId)
            ->where('participant_type', 'external')
            ->select('id', 'name', 'email', 'phone', 'institution', 'attended')
            ->get();

        // Get agendas
        $meeting->agendas = DB::table('meeting_agendas')
            ->leftJoin('users', 'meeting_agendas.presenter_id', '=', 'users.id')
            ->where('meeting_agendas.meeting_id', $meetingId)
            ->select('meeting_agendas.*', 'users.name as presenter_name')
            ->orderBy('order_index')
            ->get();

        return response()->json(['success' => true, 'meeting' => $meeting]);
    }

    /**
     * Save meeting step
     */
    private function saveMeetingStep(Request $request)
    {
        $step = $request->step;
        $meetingId = $request->meeting_id;

        DB::beginTransaction();
        try {
            if ($step == 1 || !$meetingId) {
                // Basic info
                $data = [
                    'title' => $request->title,
                    'category_id' => $request->category_id,
                    'meeting_date' => $request->meeting_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'venue' => $request->venue,
                    'meeting_type' => $request->meeting_type ?? 'physical',
                    'description' => $request->description,
                    'updated_at' => now()
                ];

                if ($meetingId) {
                    DB::table('meetings')->where('id', $meetingId)->update($data);
                } else {
                    $data['created_by'] = Auth::id();
                    $data['status'] = 'draft';
                    $data['created_at'] = now();
                    $meetingId = DB::table('meetings')->insertGetId($data);
                }
            }

            if ($step == 2 && $meetingId) {
                // Participants
                // Clear existing participants
                DB::table('meeting_participants')->where('meeting_id', $meetingId)->delete();

                // Add staff participants
                $staffIds = $request->staff_participants ?? [];
                foreach ($staffIds as $staffId) {
                    DB::table('meeting_participants')->insert([
                        'meeting_id' => $meetingId,
                        'user_id' => $staffId,
                        'participant_type' => 'staff',
                        'created_at' => now()
                    ]);
                }

                // Add external participants
                $externalNames = $request->external_name ?? [];
                $externalEmails = $request->external_email ?? [];
                $externalPhones = $request->external_phone ?? [];
                $externalInstitutions = $request->external_institution ?? [];

                foreach ($externalNames as $i => $name) {
                    if ($name) {
                        DB::table('meeting_participants')->insert([
                            'meeting_id' => $meetingId,
                            'participant_type' => 'external',
                            'name' => $name,
                            'email' => $externalEmails[$i] ?? null,
                            'phone' => $externalPhones[$i] ?? null,
                            'institution' => $externalInstitutions[$i] ?? null,
                            'created_at' => now()
                        ]);
                    }
                }
            }

            if ($step == 3 && $meetingId) {
                // Agenda items
                DB::table('meeting_agendas')->where('meeting_id', $meetingId)->delete();

                $titles = $request->agenda_title ?? [];
                $durations = $request->agenda_duration ?? [];
                $presenters = $request->agenda_presenter ?? [];
                $documents = $request->agenda_documents ?? [];
                $descriptions = $request->agenda_description ?? [];

                foreach ($titles as $i => $title) {
                    if ($title) {
                        DB::table('meeting_agendas')->insert([
                            'meeting_id' => $meetingId,
                            'title' => $title,
                            'duration' => $durations[$i] ?? null,
                            'presenter_id' => $presenters[$i] ?: null,
                            'documents' => $documents[$i] ?? null,
                            'description' => $descriptions[$i] ?? null,
                            'order_index' => $i + 1,
                            'created_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'meeting_id' => $meetingId, 'message' => 'Step saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Submit meeting for approval
     */
    private function submitMeeting(Request $request)
    {
        $meetingId = $request->meeting_id;
        
        // First save all data
        $this->saveMeetingStep($request);

        DB::table('meetings')->where('id', $meetingId)->update([
            'status' => 'pending_approval',
            'approver_id' => $request->approver_id,
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
            'updated_at' => now()
        ]);

        // Log activity
        $this->logActivity('meeting_submitted', $meetingId, 'Meeting submitted for approval');

        return response()->json(['success' => true, 'message' => 'Meeting submitted for approval']);
    }

    /**
     * Delete meeting
     */
    private function deleteMeeting(Request $request)
    {
        $meetingId = $request->meeting_id;
        
        DB::beginTransaction();
        try {
            DB::table('meeting_participants')->where('meeting_id', $meetingId)->delete();
            DB::table('meeting_agendas')->where('meeting_id', $meetingId)->delete();
            DB::table('meeting_minutes')->where('meeting_id', $meetingId)->delete();
            DB::table('meeting_action_items')->where('meeting_id', $meetingId)->delete();
            DB::table('meetings')->where('id', $meetingId)->delete();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Meeting deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Approve meeting and send SMS
     */
    private function approveMeeting(Request $request)
    {
        $meetingId = $request->meeting_id;
        $customMessage = $request->custom_message;

        $meeting = DB::table('meetings')->where('id', $meetingId)->first();
        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found']);
        }

        DB::table('meetings')->where('id', $meetingId)->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'updated_at' => now()
        ]);

        // Send SMS to participants
        $this->sendMeetingSMS($meetingId, $customMessage);

        // Log activity
        $this->logActivity('meeting_approved', $meetingId, 'Meeting approved and SMS sent');

        return response()->json(['success' => true, 'message' => 'Meeting approved and SMS notifications sent']);
    }

    /**
     * Reject meeting
     */
    private function rejectMeeting(Request $request)
    {
        $meetingId = $request->meeting_id;
        
        DB::table('meetings')->where('id', $meetingId)->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'updated_at' => now()
        ]);

        $this->logActivity('meeting_rejected', $meetingId, 'Meeting rejected: ' . $request->reason);

        return response()->json(['success' => true, 'message' => 'Meeting rejected']);
    }

    /**
     * Send SMS to meeting participants
     */
    private function sendMeetingSMS($meetingId, $customMessage = null)
    {
        $meeting = DB::table('meetings')->where('id', $meetingId)->first();
        $participants = DB::table('meeting_participants')
            ->leftJoin('users', 'meeting_participants.user_id', '=', 'users.id')
            ->where('meeting_participants.meeting_id', $meetingId)
            ->select('meeting_participants.phone', 'users.phone as user_phone', 'meeting_participants.name', 'users.name as user_name')
            ->get();

        $message = $customMessage ?: "Dear {name}, You are invited to attend: {$meeting->title} on " . 
            Carbon::parse($meeting->meeting_date)->format('D, d M Y') . 
            " at {$meeting->start_time}. Venue: {$meeting->venue}. Please confirm your attendance.";

        foreach ($participants as $participant) {
            $phone = $participant->phone ?: $participant->user_phone;
            $name = $participant->name ?: $participant->user_name;
            
            if ($phone) {
                $personalizedMessage = str_replace('{name}', $name, $message);
                // Call your SMS service here
                // SMSService::send($phone, $personalizedMessage);
                Log::info("SMS would be sent to {$phone}: {$personalizedMessage}");
            }
        }
    }

    /**
     * Get meetings without minutes
     */
    private function getMeetingsWithoutMinutes()
    {
        $meetings = DB::table('meetings')
            ->where('status', 'approved')
            ->where('meeting_date', '<=', Carbon::today())
            ->whereNull('minutes_finalized_at')
            ->select('id', 'title', 'meeting_date')
            ->orderBy('meeting_date', 'desc')
            ->get();

        return response()->json(['success' => true, 'meetings' => $meetings]);
    }

    /**
     * Get meeting data for minutes preparation
     */
    private function getMeetingForMinutes(Request $request)
    {
        return $this->getMeeting($request);
    }

    /**
     * Save minutes section
     */
    private function saveMinutesSection(Request $request)
    {
        $meetingId = $request->meeting_id;
        $section = $request->section;

        DB::beginTransaction();
        try {
            switch ($section) {
                case 'previous_actions':
                    DB::table('meeting_previous_actions')->where('meeting_id', $meetingId)->delete();
                    $descriptions = $request->prev_action_description ?? [];
                    $statuses = $request->prev_action_status ?? [];
                    $responsibles = $request->prev_action_responsible ?? [];
                    $remarks = $request->prev_action_remarks ?? [];
                    
                    foreach ($descriptions as $i => $desc) {
                        if ($desc) {
                            DB::table('meeting_previous_actions')->insert([
                                'meeting_id' => $meetingId,
                                'description' => $desc,
                                'status' => $statuses[$i] ?? 'pending',
                                'responsible_id' => $responsibles[$i] ?: null,
                                'remarks' => $remarks[$i] ?? null,
                                'created_at' => now()
                            ]);
                        }
                    }
                    break;

                case 'attendance':
                    $attendance = $request->attendance ?? [];
                    $externalAttendance = $request->external_attendance ?? [];
                    
                    DB::table('meeting_participants')
                        ->where('meeting_id', $meetingId)
                        ->where('participant_type', 'staff')
                        ->update(['attended' => 0]);
                    
                    foreach ($attendance as $userId) {
                        DB::table('meeting_participants')
                            ->where('meeting_id', $meetingId)
                            ->where('user_id', $userId)
                            ->update(['attended' => 1]);
                    }
                    break;

                case 'action_items':
                    DB::table('meeting_action_items')->where('meeting_id', $meetingId)->delete();
                    $descriptions = $request->action_description ?? [];
                    $responsibles = $request->action_responsible ?? [];
                    $deadlines = $request->action_deadline ?? [];
                    $priorities = $request->action_priority ?? [];
                    
                    foreach ($descriptions as $i => $desc) {
                        if ($desc) {
                            DB::table('meeting_action_items')->insert([
                                'meeting_id' => $meetingId,
                                'description' => $desc,
                                'responsible_id' => $responsibles[$i] ?: null,
                                'deadline' => $deadlines[$i] ?: null,
                                'priority' => $priorities[$i] ?? 'normal',
                                'status' => 'pending',
                                'created_at' => now()
                ]);
            }
        }
                    break;

                case 'aob':
                    DB::table('meeting_minutes')->updateOrInsert(
                        ['meeting_id' => $meetingId],
                        ['aob' => $request->aob, 'updated_at' => now()]
                    );
                    break;

                case 'next_meeting':
                    DB::table('meeting_minutes')->updateOrInsert(
                        ['meeting_id' => $meetingId],
                        [
                            'next_meeting_date' => $request->next_meeting_date,
                            'next_meeting_time' => $request->next_meeting_time,
                            'next_meeting_venue' => $request->next_meeting_venue,
                            'updated_at' => now()
                        ]
                    );
                    break;
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Section saved']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Save all minutes
     */
    private function saveAllMinutes(Request $request)
    {
        $meetingId = $request->meeting_id;

        DB::beginTransaction();
        try {
            // Save agenda discussions
            $discussions = $request->agenda_discussion ?? [];
            $resolutions = $request->agenda_resolution ?? [];
            
            foreach ($discussions as $agendaId => $discussion) {
                DB::table('meeting_agendas')
                    ->where('id', $agendaId)
                    ->update([
                        'discussion_notes' => $discussion,
                        'resolution' => $resolutions[$agendaId] ?? null,
                        'updated_at' => now()
                    ]);
            }

            // Save main minutes record
            DB::table('meeting_minutes')->updateOrInsert(
                ['meeting_id' => $meetingId],
                [
                    'aob' => $request->aob,
                    'closing_time' => $request->closing_time,
                    'closing_remarks' => $request->closing_remarks,
                    'next_meeting_date' => $request->next_meeting_date,
                    'next_meeting_time' => $request->next_meeting_time,
                    'next_meeting_venue' => $request->next_meeting_venue,
                    'prepared_by' => Auth::id(),
                    'updated_at' => now()
                ]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'All minutes saved']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Finalize minutes
     */
    private function finalizeMinutes(Request $request)
    {
        $meetingId = $request->meeting_id;

        $this->saveAllMinutes($request);

        DB::table('meetings')->where('id', $meetingId)->update([
            'status' => 'completed',
            'minutes_finalized_at' => now(),
            'minutes_finalized_by' => Auth::id(),
            'updated_at' => now()
        ]);

        $this->logActivity('minutes_finalized', $meetingId, 'Meeting minutes finalized');

        return response()->json(['success' => true, 'message' => 'Minutes finalized']);
    }

    /**
     * Preview minutes
     */
    private function previewMinutes(Request $request)
    {
        $meetingId = $request->meeting_id;
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->select('meetings.*', 'meeting_categories.name as category_name')
            ->where('meetings.id', $meetingId)
            ->first();

        $agendas = DB::table('meeting_agendas')
            ->leftJoin('users', 'meeting_agendas.presenter_id', '=', 'users.id')
            ->where('meeting_agendas.meeting_id', $meetingId)
            ->select('meeting_agendas.*', 'users.name as presenter_name')
            ->orderBy('order_index')
            ->get();

        $attendees = DB::table('meeting_participants')
            ->leftJoin('users', 'meeting_participants.user_id', '=', 'users.id')
            ->where('meeting_participants.meeting_id', $meetingId)
            ->where('meeting_participants.attended', 1)
            ->select('meeting_participants.name', 'users.name as user_name', 'meeting_participants.participant_type')
            ->get();

        $actionItems = DB::table('meeting_action_items')
            ->leftJoin('users', 'meeting_action_items.responsible_id', '=', 'users.id')
            ->where('meeting_action_items.meeting_id', $meetingId)
            ->select('meeting_action_items.*', 'users.name as responsible_name')
            ->get();

        $minutes = DB::table('meeting_minutes')->where('meeting_id', $meetingId)->first();

        $html = view('modules.meetings.partials.minutes-preview', compact('meeting', 'agendas', 'attendees', 'actionItems', 'minutes'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        $activities = DB::table('meeting_activity_log')
            ->leftJoin('users', 'meeting_activity_log.user_id', '=', 'users.id')
            ->select('meeting_activity_log.*', 'users.name as user_name')
            ->orderBy('meeting_activity_log.created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['success' => true, 'activities' => $activities]);
    }

    /**
     * Log activity
     */
    private function logActivity($type, $meetingId, $description)
    {
        DB::table('meeting_activity_log')->insert([
            'meeting_id' => $meetingId,
            'user_id' => Auth::id(),
            'type' => $type,
            'description' => $description,
            'created_at' => now()
        ]);
    }
}
