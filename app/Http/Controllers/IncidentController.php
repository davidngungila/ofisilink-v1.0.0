<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\IncidentEmailConfig;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use App\Services\EmailSyncService;
use App\Services\ActivityLogService;
use Carbon\Carbon;

class IncidentController extends Controller
{
    protected $notificationService;
    protected $emailSyncService;

    public function __construct(NotificationService $notificationService, EmailSyncService $emailSyncService)
    {
        $this->notificationService = $notificationService;
        $this->emailSyncService = $emailSyncService;
    }

    /**
     * Display incidents list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Incident::with(['reporter', 'assignedTo', 'assignedBy', 'resolvedBy']);

        // Apply tab filter
        $tab = $request->get('tab', 'all');
        if ($tab === 'new') {
            $query->where('status', 'New');
        } elseif ($tab === 'assigned') {
            $query->where('status', 'Assigned');
        } elseif ($tab === 'in_progress') {
            $query->where('status', 'In Progress');
        } elseif ($tab === 'resolved') {
            $query->where('status', 'Resolved');
        } elseif ($tab === 'closed') {
            $query->where('status', 'Closed');
        } elseif ($tab === 'my_incidents') {
            $query->where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('reported_by', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }
        
        // Apply additional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('title', 'like', "%{$search}%")
                       ->orWhere('subject', 'like', "%{$search}%");
                })
                  ->orWhere('incident_code', 'like', "%{$search}%")
                  ->orWhere('reporter_name', 'like', "%{$search}%")
                  ->orWhere('reporter_email', 'like', "%{$search}%");
            });
        }

        // Role-based filtering
        if ($user->hasAnyRole(['HR Officer', 'System Admin'])) {
            // HR and Admins see all incidents
        } elseif ($user->hasRole('HOD')) {
            // HOD sees all incidents in their department
            if ($user->primary_department_id) {
                $query->where(function($q) use ($user) {
                    $q->whereHas('assignedTo', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    })->orWhereHas('reporter', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    })->orWhere('created_by', $user->id); // Also show incidents they created
                });
            }
        } else {
            // Staff only see incidents assigned to them or reported by them
            $query->where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('reported_by', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Get filtered incidents for current tab
        $incidents = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all incidents for stats (separate query for accuracy)
        $allIncidentsQuery = Incident::query();
        
        // Apply same role-based filtering for all queries
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            if ($user->hasRole('HOD')) {
                if ($user->primary_department_id) {
                    $allIncidentsQuery->where(function($q) use ($user) {
                        $q->whereHas('assignedTo', function($sq) use ($user) {
                            $sq->where('primary_department_id', $user->primary_department_id);
                        })->orWhereHas('reporter', function($sq) use ($user) {
                            $sq->where('primary_department_id', $user->primary_department_id);
                        })->orWhere('created_by', $user->id);
                    });
                }
            } else {
                $allIncidentsQuery->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('reported_by', $user->id)
                      ->orWhere('created_by', $user->id);
                });
            }
        }
        
        // Create base query with role filtering for tabs (before tab filter)
        $baseQuery = Incident::with(['reporter', 'assignedTo', 'assignedBy', 'resolvedBy']);
        
        // Apply role-based filtering to base query
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            if ($user->hasRole('HOD')) {
                if ($user->primary_department_id) {
                    $baseQuery->where(function($q) use ($user) {
                        $q->whereHas('assignedTo', function($sq) use ($user) {
                            $sq->where('primary_department_id', $user->primary_department_id);
                        })->orWhereHas('reporter', function($sq) use ($user) {
                            $sq->where('primary_department_id', $user->primary_department_id);
                        })->orWhere('created_by', $user->id);
                    });
                }
            } else {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('reported_by', $user->id)
                      ->orWhere('created_by', $user->id);
                });
            }
        }
        
        // Create separate queries for each tab
        $tab = $request->get('tab', 'all');
        $allIncidents = (clone $baseQuery)->orderBy('created_at', 'desc')->get();
        $newIncidents = (clone $baseQuery)->where('status', 'New')->orderBy('created_at', 'desc')->get();
        $assignedIncidents = (clone $baseQuery)->where('status', 'Assigned')->orderBy('created_at', 'desc')->get();
        $inProgressIncidents = (clone $baseQuery)->where('status', 'In Progress')->orderBy('created_at', 'desc')->get();
        $resolvedIncidents = (clone $baseQuery)->where('status', 'Resolved')->orderBy('created_at', 'desc')->get();
        $closedIncidents = (clone $baseQuery)->where('status', 'Closed')->orderBy('created_at', 'desc')->get();
        $myIncidents = (clone $baseQuery)->where(function($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('reported_by', $user->id)
              ->orWhere('created_by', $user->id);
        })->orderBy('created_at', 'desc')->get();
        // Statistics with counts (using baseQuery for consistent filtering)
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'new' => (clone $baseQuery)->where('status', 'New')->count(),
            'assigned' => (clone $baseQuery)->where('status', 'Assigned')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'In Progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'Resolved')->count(),
            'closed' => (clone $baseQuery)->where('status', 'Closed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'Cancelled')->count(),
            'my_assigned' => (clone $baseQuery)->where('assigned_to', $user->id)->whereIn('status', ['Assigned', 'In Progress'])->count(),
            'critical' => (clone $baseQuery)->where('priority', 'Critical')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
            'high' => (clone $baseQuery)->where('priority', 'High')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
        ];

        // Get staff list for assignment dropdown (only if manager)
        $staff = [];
        if ($user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            $staff = \App\Models\User::where('is_active', true)
                ->whereHas('roles', function($q) {
                    $q->where('name', '!=', 'System Admin');
                })
                ->orderBy('name')
                ->get();
        }

        return view('modules.incidents.index', compact(
            'incidents', 
            'stats', 
            'staff',
            'allIncidents',
            'newIncidents',
            'assignedIncidents',
            'inProgressIncidents',
            'resolvedIncidents',
            'closedIncidents',
            'myIncidents'
        ));
    }

    /**
     * Show incident details
     */
    public function show($id)
    {
        $incident = Incident::with([
            'reporter', 
            'assignedTo', 
            'assignedBy', 
            'resolvedBy', 
            'closedBy',
            'creator',
            'updater',
            'updates.user'
        ])->findOrFail($id);
        
        $user = Auth::user();
        
        // Check permissions
        $canView = $user->hasAnyRole(['HR Officer', 'System Admin']) ||
                   ($user->hasRole('HOD') && $this->isInSameDepartment($incident, $user)) ||
                   $incident->assigned_to === $user->id ||
                   $incident->reported_by === $user->id;

        if (!$canView) {
            abort(403, 'You do not have permission to view this incident.');
        }

        // Get updates/comments
        $updates = IncidentUpdate::where('incident_id', $incident->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get activity logs
        $activities = ActivityLog::where('model_type', Incident::class)
            ->where('model_id', $incident->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get staff for assignment (if manager)
        $staff = [];
        if ($user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            $staff = User::where('is_active', true)
                ->whereHas('roles', function($q) {
                    $q->where('name', '!=', 'System Admin');
                })
                ->orderBy('name')
                ->get();
        }

        return view('modules.incidents.show', compact('incident', 'updates', 'activities', 'staff'));
    }

    /**
     * Create new incident (form page)
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403, 'You do not have permission to create incidents.');
        }
        
        // Get staff for assignment dropdown
        $staff = User::where('is_active', true)
            ->whereHas('roles', function($q) {
                $q->where('name', '!=', 'System Admin');
            })
            ->orderBy('name')
            ->get();
        
        return view('modules.incidents.create', compact('staff', 'user'));
    }

    /**
     * Store new incident
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and HODs can create incidents.'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Critical,low,medium,high,critical',
            'category' => 'required|in:technical,hr,facilities,security,other',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'nullable|email',
            'reporter_phone' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        try {
            DB::beginTransaction();

            // Normalize priority to match database enum format
            $priority = ucfirst(strtolower($request->priority));
            
            // Build incident data - handle both old and new column names
            $incidentData = [
                'subject' => $request->title,
                'description' => $request->description,
                'priority' => $priority,
                'category' => $request->category ?? 'technical',
                'reported_by' => $user->id,
                'assigned_to' => $request->assigned_to,
                'assigned_by' => $request->assigned_to ? $user->id : null,
                'assigned_at' => $request->assigned_to ? now() : null,
                'status' => $request->assigned_to ? 'Assigned' : 'New',
                'source' => $request->source ?? 'manual',
                'created_by' => $user->id,
            ];
            
            // Add due_date if provided
            if ($request->filled('due_date')) {
                $incidentData['due_date'] = $request->due_date;
            }

            // Add internal_notes only if column exists
            if (Schema::hasColumn('incidents', 'internal_notes') && $request->filled('internal_notes')) {
                $incidentData['internal_notes'] = $request->internal_notes;
            }

            // Add reporter fields - check which columns exist in database
            if (Schema::hasColumn('incidents', 'reporter_name')) {
                $incidentData['reporter_name'] = $request->reporter_name;
            } elseif (Schema::hasColumn('incidents', 'reported_by_name')) {
                $incidentData['reported_by_name'] = $request->reporter_name;
            }
            
            if (Schema::hasColumn('incidents', 'reporter_email')) {
                $incidentData['reporter_email'] = $request->reporter_email;
            } elseif (Schema::hasColumn('incidents', 'reported_by_email')) {
                $incidentData['reported_by_email'] = $request->reporter_email;
            }
            
            if (Schema::hasColumn('incidents', 'reporter_phone')) {
                $incidentData['reporter_phone'] = $request->reporter_phone;
            } elseif (Schema::hasColumn('incidents', 'reported_by_phone')) {
                $incidentData['reported_by_phone'] = $request->reporter_phone;
            }
            
            $incident = Incident::create($incidentData);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('public/incidents/' . $incident->id);
                    $attachments[] = str_replace('public/', '', $path);
                }
                $incident->update(['attachments' => $attachments]);
            }

            DB::commit();

            // Log activity
            ActivityLogService::logCreated($incident, "Created incident {$incident->incident_no}: {$incident->subject}", [
                'incident_no' => $incident->incident_no,
                'subject' => $incident->subject,
                'priority' => $incident->priority,
                'category' => $incident->category,
                'status' => $incident->status,
                'assigned_to' => $incident->assigned_to,
            ]);

            // Notify reporter
            $this->notifyReporter($incident);

            // Notify assigned staff if assigned
            if ($incident->assigned_to) {
                $this->notifyAssignment($incident);
            }

            // Notify HR/Managers for new incidents
            $this->notifyManagers($incident);

            return response()->json([
                'success' => true,
                'message' => 'Incident created successfully. Notifications sent.',
                'redirect' => route('modules.incidents.show', $incident->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating incident: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating incident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign incident to staff
     */
    public function assign(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and HODs can assign incidents.'
            ], 403);
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $incident = Incident::findOrFail($id);
            
            $updateData = [
                'assigned_to' => $request->assigned_to,
                'assigned_by' => $user->id,
                'status' => 'Assigned',
            ];

            // Add assigned_at only if column exists
            if (Schema::hasColumn('incidents', 'assigned_at')) {
                $updateData['assigned_at'] = now();
            }

            // Add updated_by only if column exists
            if (Schema::hasColumn('incidents', 'updated_by')) {
                $updateData['updated_by'] = $user->id;
            }

            // Add internal_notes only if column exists
            if (Schema::hasColumn('incidents', 'internal_notes')) {
                if ($request->internal_notes) {
                    $existingNotes = $incident->internal_notes ?? '';
                    $updateData['internal_notes'] = $existingNotes 
                        ? $existingNotes . "\n\n" . $request->internal_notes 
                        : $request->internal_notes;
                }
            }
            
            $incident->update($updateData);

            DB::commit();

            // Notify assigned staff
            $this->notifyAssignment($incident);

            return response()->json([
                'success' => true,
                'message' => 'Incident assigned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning incident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update incident status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:New,Assigned,In Progress,Resolved,Closed,Cancelled',
            'resolution_notes' => 'nullable|required_if:status,Resolved|string',
        ]);

        try {
            DB::beginTransaction();

            $incident = Incident::findOrFail($id);
            $user = Auth::user();
            
            // Check permissions
            $canUpdate = $user->hasAnyRole(['HR Officer', 'System Admin']) ||
                        ($user->hasRole('HOD') && $this->isInSameDepartment($incident, $user)) ||
                        $incident->assigned_to === $user->id;

            if (!$canUpdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this incident.'
                ], 403);
            }

            $oldStatus = $incident->status;
            $updateData = [
                'status' => $request->status,
            ];
            
            // Add updated_by only if column exists
            if (Schema::hasColumn('incidents', 'updated_by')) {
                $updateData['updated_by'] = $user->id;
            }

            if ($request->status === 'Resolved') {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = $user->id;
                $updateData['resolution_details'] = $request->resolution_notes; // Use resolution_details for existing table
                $updateData['resolution_notes'] = $request->resolution_notes;
            } elseif ($request->status === 'Closed') {
                $updateData['closed_at'] = now();
                $updateData['closed_by'] = $user->id;
            } elseif ($request->status === 'In Progress') {
                // Auto-update when assigned staff starts working
            }

            $incident->update($updateData);

            DB::commit();

            // Log activity
            ActivityLogService::logAction('incident_status_updated', "Updated incident {$incident->incident_no} status from {$oldStatus} to {$request->status}", $incident, [
                'incident_no' => $incident->incident_no,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'resolution_notes' => $request->resolution_notes ?? null,
            ]);

            // Notify reporter if resolved
            if ($request->status === 'Resolved' && $oldStatus !== 'Resolved') {
                $this->notifyResolution($incident);
            }

            return response()->json([
                'success' => true,
                'message' => 'Incident status updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync emails manually (with filters)
     */
    public function syncEmails(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and System Admins can sync emails.'
            ], 403);
        }

        $request->validate([
            'sync_mode' => 'nullable|in:all,range,live',
            'date_from' => 'nullable|date|required_if:sync_mode,range',
            'date_to' => 'nullable|date|required_if:sync_mode,range|after_or_equal:date_from',
            'config_id' => 'nullable|exists:incident_email_config,id',
        ]);

        try {
            $syncMode = $request->input('sync_mode', 'all');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $configId = $request->input('config_id');

            // If live mode, enable automatic syncing for selected config
            if ($syncMode === 'live') {
                if ($configId) {
                    $config = IncidentEmailConfig::findOrFail($configId);
                    $syncSettings = $config->sync_settings ?? [];
                    $syncSettings['live_mode'] = true;
                    $config->update(['sync_settings' => $syncSettings]);
                } else {
                    // Enable live mode for all active configs
                    IncidentEmailConfig::where('is_active', true)->get()->each(function($config) {
                        $syncSettings = $config->sync_settings ?? [];
                        $syncSettings['live_mode'] = true;
                        $config->update(['sync_settings' => $syncSettings]);
                    });
                }
            }

            // Dispatch job to background to prevent blocking/crash
            \App\Jobs\SyncIncidentEmailsJob::dispatch($configId, $dateFrom, $dateTo, $syncMode);
            
            $message = 'Email sync started in background. ';
            if ($syncMode === 'live') {
                $message .= 'Live mode enabled - new emails will be synced automatically every 5 minutes.';
            } else {
                $message .= 'The sync will process in the background.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'sync_mode' => $syncMode,
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting email sync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error starting email sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status
     */
    public function getSyncStatus()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::where('is_active', true)->get();
        $status = [];

        foreach ($configs as $config) {
            $status[] = [
                'id' => $config->id,
                'email' => $config->email_address,
                'last_sync' => $config->last_sync_at ? $config->last_sync_at->format('Y-m-d H:i:s') : null,
                'sync_count' => $config->sync_count ?? 0,
                'failed_count' => $config->failed_sync_count ?? 0,
                'connection_status' => $config->connection_status ?? 'unknown',
            ];
        }

        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }

    /**
     * Get email configurations
     */
    public function emailConfig()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        // Auto-check connection status for all configs
        foreach ($configs as $config) {
            if ($config->is_active) {
                // Check if last test was more than 5 minutes ago, then test again
                if (!$config->last_connection_test_at || 
                    $config->last_connection_test_at->diffInMinutes(now()) > 5) {
                    try {
                        $result = $this->testEmailConnection($config);
                        $config->update([
                            'connection_status' => $result['status'],
                            'last_connection_test_at' => now(),
                            'connection_error' => $result['error'] ?? null,
                        ]);
                    } catch (\Exception $e) {
                        // Silent fail, will show in UI
                    }
                }
            }
        }
        
        // Refresh configs after testing
        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        return view('modules.incidents.email-config', compact('configs'));
    }

    /**
     * Configured Email Accounts page
     */
    public function emailAccounts()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        // Load the full email accounts page with all modals and functionality
        // For now, we'll use a view that includes all the original email-config functionality
        // The email-accounts.blade.php will be updated to include all modals and scripts
        return view('modules.incidents.email-accounts', compact('configs'));
    }

    /**
     * Email connection testing page
     */
    public function emailConnectionTest()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        return view('modules.incidents.email-connection-test', compact('configs'));
    }

    /**
     * Email retrieval page
     */
    public function emailRetrieve()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        return view('modules.incidents.email-retrieve', compact('configs'));
    }

    /**
     * Email transfer to incidents page
     */
    public function emailTransfer()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        // Get staff for assignment (if manager)
        $staff = [];
        if ($user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            $staff = User::where('is_active', true)
                ->whereHas('roles', function($q) {
                    $q->where('name', '!=', 'System Admin');
                })
                ->orderBy('name')
                ->get();
        }
        
        return view('modules.incidents.email-transfer', compact('configs', 'staff'));
    }

    /**
     * Get email configurations (AJAX endpoint)
     */
    public function getEmailConfigs()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'configs' => $configs,
        ]);
    }

    /**
     * Get all connection statuses (AJAX endpoint)
     */
    public function getAllConnectionStatuses()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $configs = IncidentEmailConfig::orderBy('created_at', 'desc')->get();
        $statuses = [];

        foreach ($configs as $config) {
            $syncSettings = $config->sync_settings ?? [];
            $statuses[] = [
                'id' => $config->id,
                'email_address' => $config->email_address,
                'connection_status' => $config->connection_status ?? 'unknown',
                'connection_error' => $config->connection_error,
                'last_connection_test_at' => $config->last_connection_test_at ? $config->last_connection_test_at->format('Y-m-d H:i:s') : null,
                'last_sync_at' => $config->last_sync_at ? $config->last_sync_at->format('Y-m-d H:i:s') : null,
                'is_active' => $config->is_active,
                'sync_count' => $config->sync_count ?? 0,
                'failed_sync_count' => $config->failed_sync_count ?? 0,
                'is_live_mode' => isset($syncSettings['live_mode']) && $syncSettings['live_mode'] === true,
            ];
        }

        return response()->json([
            'success' => true,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Store email configuration
     */
    public function storeEmailConfig(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $request->validate([
            'email_address' => 'required|email|unique:incident_email_config,email_address',
            'protocol' => 'required|in:imap,pop3',
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|in:ssl,tls',
            'ssl_enabled' => 'nullable',
            'folder' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        try {
            // Force approach: Build data array explicitly to ensure all fields are set
            $data = [
                'email_address' => $request->input('email_address'),
                'protocol' => $request->input('protocol'),
                'host' => $request->input('host'),
                'port' => (int)$request->input('port'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'folder' => $request->input('folder', 'INBOX'),
            ];
            
            // Handle encryption field - check if it's sent as 'encryption' or 'ssl_enabled'
            $encryption = $request->input('encryption');
            if ($encryption !== null) {
                // If encryption field is provided, convert to ssl_enabled
                $data['ssl_enabled'] = ($encryption === 'ssl' || $encryption === 'SSL');
            } else {
                // Otherwise use ssl_enabled directly
                $sslEnabled = $request->input('ssl_enabled');
                if ($sslEnabled !== null) {
                    if (is_string($sslEnabled)) {
                        $data['ssl_enabled'] = in_array(strtolower($sslEnabled), ['1', 'on', 'true', 'yes']);
                    } else {
                        $data['ssl_enabled'] = (bool)$sslEnabled;
                    }
                } else {
                    $data['ssl_enabled'] = true; // Default to SSL enabled
                }
            }
            
            // Handle is_active
            $isActive = $request->input('is_active');
            if ($isActive !== null) {
                if (is_string($isActive)) {
                    $data['is_active'] = in_array(strtolower($isActive), ['1', 'on', 'true', 'yes']);
                } else {
                    $data['is_active'] = (bool)$isActive;
                }
            } else {
                $data['is_active'] = true; // Default to active
            }
            
            // Ensure boolean values are properly cast
            $data['ssl_enabled'] = (bool)$data['ssl_enabled'];
            $data['is_active'] = (bool)$data['is_active'];
            
            // Remove spaces from password (Gmail app passwords should not have spaces)
            if (isset($data['password'])) {
                $data['password'] = str_replace(' ', '', $data['password']);
            }
            
            // Enable live mode by default for automatic email syncing
            $data['sync_settings'] = [
                'live_mode' => true, // Enable automatic syncing every 1 minute
                'auto_create_incidents' => true,
            ];
            
            // Log for debugging (excluding password)
            \Log::info('Creating email config', [
                'email' => $data['email_address'] ?? 'N/A',
                'protocol' => $data['protocol'] ?? 'N/A',
                'host' => $data['host'] ?? 'N/A',
                'port' => $data['port'] ?? 'N/A',
                'is_active' => $data['is_active'],
                'ssl_enabled' => $data['ssl_enabled'],
                'has_password' => !empty($data['password']),
                'folder' => $data['folder'] ?? null,
                'live_mode' => true,
                'raw_request' => $request->except(['password']),
                'data_array' => array_merge($data, ['password' => '***HIDDEN***'])
            ]);
            
            // Force create with explicit data - use DB transaction for safety
            try {
                $config = IncidentEmailConfig::create($data);
            } catch (\Illuminate\Database\QueryException $e) {
                \Log::error('Database error creating email config', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? []
                ]);
                throw new \Exception('Database error: ' . $e->getMessage());
            }
            
            // Test connection automatically
            $testResult = $this->testEmailConnection($config);
            $config->update([
                'connection_status' => $testResult['status'],
                'last_connection_test_at' => now(),
                'connection_error' => $testResult['error'] ?? null,
            ]);

            \Log::info('Email config saved successfully', [
                'config_id' => $config->id,
                'email' => $config->email_address,
                'connection_status' => $testResult['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email configuration saved successfully. ' . ($testResult['status'] === 'connected' ? 'Connection test successful!' : 'Connection test failed: ' . ($testResult['error'] ?? 'Unknown error')),
                'connection_status' => $testResult['status'],
                'config_id' => $config->id,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error saving email config: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password'])
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email connection without saving (for form testing)
     */
    public function testConnectionWithoutSave(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $request->validate([
            'email_address' => 'required|email',
            'protocol' => 'required|in:imap,pop3',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'password' => 'required|string',
            'ssl_enabled' => 'boolean',
            'folder' => 'nullable|string',
        ]);

        try {
            // Create a temporary config object for testing
            // Use a simple object instead of the model to avoid password encryption
            $tempConfig = (object)[
                'email_address' => $request->email_address,
                'protocol' => $request->protocol,
                'host' => $request->host,
                'port' => $request->port,
                'username' => $request->username,
                'password' => $request->password, // Plain password for testing
                'ssl_enabled' => filter_var($request->ssl_enabled, FILTER_VALIDATE_BOOLEAN),
                'folder' => $request->folder ?? 'INBOX',
            ];
            
            $result = $this->testEmailConnection($tempConfig);

            return response()->json([
                'success' => $result['status'] === 'connected',
                'message' => $result['message'],
                'connection_status' => $result['status'],
                'error' => $result['error'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error testing connection without save: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email connection
     */
    public function testConnection($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $config = IncidentEmailConfig::findOrFail($id);
            $result = $this->testEmailConnection($config);
            
            $config->update([
                'connection_status' => $result['status'],
                'last_connection_test_at' => now(),
                'connection_error' => $result['error'] ?? null,
            ]);

            return response()->json([
                'success' => $result['status'] === 'connected',
                'message' => $result['message'],
                'connection_status' => $result['status'],
                'error' => $result['error'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update email configuration
     */
    public function updateEmailConfig(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $config = IncidentEmailConfig::findOrFail($id);

        $request->validate([
            'email_address' => 'required|email|unique:incident_email_config,email_address,' . $id,
            'protocol' => 'required|in:imap,pop3',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'ssl_enabled' => 'boolean',
            'folder' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $data = $request->all();
            // Only update password if provided
            if (empty($data['password'])) {
                unset($data['password']);
            }
            // Convert string '1'/'0' to boolean for is_active
            if (isset($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }
            // Convert string '1'/'0' to boolean for ssl_enabled
            if (isset($data['ssl_enabled'])) {
                $data['ssl_enabled'] = filter_var($data['ssl_enabled'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $config->update($data);

            // Test connection after update
            $testResult = $this->testEmailConnection($config);
            $config->update([
                'connection_status' => $testResult['status'],
                'last_connection_test_at' => now(),
                'connection_error' => $testResult['error'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email configuration updated successfully. ' . ($testResult['status'] === 'connected' ? 'Connection test successful!' : 'Connection test failed.'),
                'connection_status' => $testResult['status'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete email configuration
     */
    public function deleteEmailConfig($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $config = IncidentEmailConfig::findOrFail($id);
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Email configuration deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle email configuration active status
     */
    public function toggleEmailConfigStatus($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $config = IncidentEmailConfig::findOrFail($id);
            $config->update(['is_active' => !$config->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Email configuration status updated successfully.',
                'is_active' => $config->is_active,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch live emails from email account
     */
    public function fetchLiveEmails(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $config = IncidentEmailConfig::findOrFail($id);
            
            if (!$config->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email configuration is not active.'
                ], 400);
            }

            $limit = (int)$request->input('limit', 50);
            $fetchMode = $request->input('fetch_mode', 'unseen'); // 'unseen', 'all', 'recent'
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $messageNumber = $request->input('message_number');

            $emails = $this->fetchEmailsFromAccount($config, $limit, $fetchMode, $dateFrom, $dateTo, $messageNumber);

            // Log for debugging
            \Log::info("Fetched emails for {$config->email_address}", [
                'fetch_mode' => $fetchMode,
                'count' => count($emails),
                'limit' => $limit,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]);

            $response = [
                'success' => true,
                'emails' => $emails,
                'count' => count($emails),
                'email_address' => $config->email_address,
                'fetch_mode' => $fetchMode,
            ];

            if (count($emails) === 0) {
                $response['message'] = "No emails found for mode: {$fetchMode}. The mailbox may be empty, or emails may be outside the date range. Try changing the filter mode or check if emails exist in the account.";
                $response['debug_info'] = [
                    'search_used' => $fetchMode,
                    'suggestion' => $fetchMode === 'unseen' 
                        ? 'Try "All Emails" or "Recent Emails" mode' 
                        : ($fetchMode === 'all' 
                            ? 'Try "Recent Emails" mode or check if emails exist in the account' 
                            : 'Try "All Emails" mode')
                ];
            } else {
                $response['message'] = "Found " . count($emails) . " email(s)";
            }

            return response()->json($response);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email configuration not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching live emails: ' . $e->getMessage(), [
                'config_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = $e->getMessage();
            
            // Provide user-friendly error messages
            if (stripos($errorMessage, 'too many login failures') !== false) {
                $errorMessage = 'Too many login failures. The email account may be temporarily locked. Please wait a few minutes and try again, or check your email account settings.';
            } elseif (stripos($errorMessage, 'IMAP extension not available') !== false) {
                $errorMessage = 'IMAP extension is not available on the server. Please contact your system administrator.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_type' => class_basename($e)
            ], 500);
        }
    }

    /**
     * Fetch emails from email account
     */
    protected function fetchEmailsFromAccount(IncidentEmailConfig $config, $limit = 50, $fetchMode = 'unseen', $dateFrom = null, $dateTo = null, $specificMessageNumber = null)
    {
        if (!function_exists('imap_open')) {
            throw new \Exception('IMAP extension not available');
        }
        
        // Set execution time limit for email fetching
        // Increased to allow time for IMAP operations
        set_time_limit(60); // 60 seconds max

        $mailbox = '{' . $config->host . ':' . $config->port . '/' . ($config->ssl_enabled ? 'ssl' : 'notls') . '}' . ($config->folder ?? 'INBOX');
        
        // Set IMAP timeouts to prevent hanging (reduced for faster response)
        @imap_timeout(IMAP_OPENTIMEOUT, 10);
        @imap_timeout(IMAP_READTIMEOUT, 10);
        @imap_timeout(IMAP_WRITETIMEOUT, 10);
        @imap_timeout(IMAP_CLOSETIMEOUT, 10);
        
        // Don't use OP_HALFOPEN - we need full access to search emails
        // OP_HALFOPEN prevents searching and fetching emails
        $connection = @imap_open($mailbox, $config->username, $config->password, 0, 1);
        
        if (!$connection) {
            $error = imap_last_error() ?: 'Unknown error';
            imap_errors(); // Clear error stack
            imap_alerts(); // Clear alert stack
            
            // Provide more helpful error messages
            if (stripos($error, 'too many login failures') !== false || stripos($error, 'login failures') !== false) {
                throw new \Exception('Too many login failures. The email account may be temporarily locked. Please wait a few minutes and try again, or check your email account credentials.');
            } elseif (stripos($error, 'authentication failed') !== false || stripos($error, 'invalid credentials') !== false) {
                throw new \Exception('Authentication failed. Please check your username and password.');
            } elseif (stripos($error, 'connection refused') !== false || stripos($error, 'cannot connect') !== false) {
                throw new \Exception('Cannot connect to email server. Please check the host and port settings.');
            } elseif (stripos($error, 'certificate') !== false || stripos($error, 'ssl') !== false) {
                throw new \Exception('SSL/TLS certificate error. Please check SSL settings or try disabling SSL if not required.');
            } else {
                throw new \Exception('Failed to connect to email server: ' . $error);
            }
        }

        try {
            $messageNumbers = [];
            
            // If specific message number requested, fetch only that
            if ($specificMessageNumber) {
                $messageNumber = (int)$specificMessageNumber;
                // Verify the message exists before adding to array
                $header = @imap_headerinfo($connection, $messageNumber);
                if ($header) {
                    $messageNumbers = [$messageNumber];
                } else {
                    // Message doesn't exist, return empty
                    imap_close($connection);
                    return [];
                }
            } else {
                // Build search criteria
                $searchCriteria = [];
                
                if ($fetchMode === 'all') {
                    // For 'all' mode, try to get all emails first
                    // Only apply date restrictions if explicitly provided
                    if ($dateFrom) {
                        $from = date('d-M-Y', strtotime($dateFrom));
                        $searchCriteria[] = "SINCE \"{$from}\"";
                    }
                    // Don't add default date restriction - get all emails
                    // If no date specified, we'll try ALL first, then fallback to recent if needed
                    if ($dateTo) {
                        $to = date('d-M-Y', strtotime($dateTo . ' +1 day'));
                        $searchCriteria[] = "BEFORE \"{$to}\"";
                    }
                } elseif ($fetchMode === 'recent') {
                    // For 'recent' mode, get emails from last 7 days
                    $from = date('d-M-Y', strtotime('-7 days'));
                    $searchCriteria[] = "SINCE \"{$from}\"";
                } else {
                    // Default: UNSEEN
                    $searchCriteria[] = 'UNSEEN';
                }

                // Build search string - use ALL if no criteria specified
                $searchString = !empty($searchCriteria) ? implode(' ', $searchCriteria) : 'ALL';
                
                \Log::info("IMAP search for {$config->email_address}", [
                    'fetch_mode' => $fetchMode,
                    'search_string' => $searchString,
                    'mailbox' => $mailbox,
                    'has_criteria' => !empty($searchCriteria)
                ]);
                
                // First, try to get total message count to verify connection works
                $totalMessages = @imap_num_msg($connection);
                \Log::info("Total messages in mailbox for {$config->email_address}: {$totalMessages}");
                
                if ($totalMessages === false || $totalMessages == 0) {
                    \Log::warning("No messages found in mailbox for {$config->email_address}");
                    imap_close($connection);
                    return [];
                }
                
                // Try search first
                $messageNumbers = @imap_search($connection, $searchString);
                
                // Check for IMAP errors
                $imapError = imap_last_error();
                if ($imapError) {
                    \Log::warning("IMAP search error for {$config->email_address}: {$imapError}");
                    // Clear error stack
                    imap_errors();
                }
                
                // If search returns false or empty, use fallback approach
                if ($messageNumbers === false || !is_array($messageNumbers) || empty($messageNumbers)) {
                    \Log::info("Search returned no results, using fallback for {$config->email_address}");
                    
                    // Fallback: Get messages directly by message number
                    if ($fetchMode === 'all' || $fetchMode === 'recent') {
                        // Get the most recent messages (up to limit)
                        $startMsg = max(1, $totalMessages - $limit + 1);
                        $messageNumbers = range($startMsg, $totalMessages);
                        \Log::info("Using direct message range: {$startMsg} to {$totalMessages} (total: {$totalMessages})");
                    } elseif ($fetchMode === 'unseen') {
                        // For unseen, check flags on recent messages
                        $messageNumbers = [];
                        $checkLimit = min($totalMessages, 200); // Check last 200 messages for unseen
                        $startCheck = max(1, $totalMessages - $checkLimit + 1);
                        for ($i = $totalMessages; $i >= $startCheck && count($messageNumbers) < $limit; $i--) {
                            $overview = @imap_fetch_overview($connection, $i, 0);
                            if ($overview && isset($overview[0]) && !($overview[0]->seen ?? false)) {
                                $messageNumbers[] = $i;
                            }
                        }
                        \Log::info("Found " . count($messageNumbers) . " unseen messages by checking flags");
                    }
                    
                    if (empty($messageNumbers)) {
                        \Log::warning("No messages found after fallback for {$config->email_address}");
                        imap_close($connection);
                        return [];
                    }
                } else {
                    \Log::info("Search found " . count($messageNumbers) . " messages for {$config->email_address}");
                }

                // Sort by message number (newest first)
                rsort($messageNumbers);
                
                // Limit results
                if (count($messageNumbers) > $limit) {
                    $messageNumbers = array_slice($messageNumbers, 0, $limit);
                }
                
                \Log::info("Final message numbers to fetch for {$config->email_address}: " . count($messageNumbers) . " messages");
            }

            $emails = [];
            
            // Optimize: Fetch all overviews at once instead of one by one
            // This is much faster than fetching individually
            $overviews = @imap_fetch_overview($connection, implode(',', $messageNumbers), 0);
            $overviewMap = [];
            if ($overviews && is_array($overviews)) {
                foreach ($overviews as $ov) {
                    $overviewMap[$ov->msgno] = $ov;
                }
            }
            
            foreach ($messageNumbers as $msgNumber) {
                try {
                    // Use cached overview if available, otherwise fetch
                    $overview = $overviewMap[$msgNumber] ?? @imap_fetch_overview($connection, $msgNumber, 0)[0] ?? null;
                    
                    if (!$overview) {
                        \Log::warning("Could not fetch overview for message {$msgNumber}");
                        continue;
                    }
                    
                    // Fetch header only when needed (for from/date info)
                    $header = @imap_headerinfo($connection, $msgNumber);
                    if (!$header) {
                        // Use overview data if header fails
                        $fromEmail = $overview->from ?? 'unknown@unknown.com';
                        $fromName = null;
                        $date = isset($overview->date) ? date('Y-m-d H:i:s', strtotime($overview->date)) : now()->format('Y-m-d H:i:s');
                    } else {
                        $fromEmail = ($header->from[0]->mailbox ?? '') . '@' . ($header->from[0]->host ?? '');
                        $fromName = $header->from[0]->personal ?? null;
                        $date = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : (isset($overview->date) ? date('Y-m-d H:i:s', strtotime($overview->date)) : now()->format('Y-m-d H:i:s'));
                    }
                    
                    // SKIP body fetching entirely for list view to maximize speed
                    // Body will be fetched on-demand when viewing individual email
                    // This significantly reduces execution time
                    $body = '';
                    $bodyPreview = '';

                    $emails[] = [
                        'message_number' => $msgNumber,
                        'message_id' => $overview->message_id ?? null,
                        'from_name' => $fromName,
                        'from_email' => $fromEmail,
                        'subject' => $overview->subject ?? '(No subject)',
                        'body' => '',
                        'body_preview' => '',
                        'date' => $date,
                        'received_at' => $date,
                        'seen' => ($overview->seen ?? 0) == 1,
                        'is_seen' => ($overview->seen ?? 0) == 1,
                        'is_recent' => ($overview->recent ?? 0) == 1,
                        'size' => $overview->size ?? 0,
                        'to' => $header->toaddress ?? ($overview->to ?? null),
                    ];
                } catch (\Exception $e) {
                    \Log::warning("Error processing email {$msgNumber}: " . $e->getMessage());
                    continue;
                }
            }

            imap_close($connection);
            
            \Log::info("Successfully fetched " . count($emails) . " emails for {$config->email_address}");
            return $emails;

        } catch (\Exception $e) {
            if (isset($connection) && $connection) {
                @imap_close($connection);
            }
            
            // Check if it's a timeout error
            if (stripos($e->getMessage(), 'maximum execution time') !== false || 
                stripos($e->getMessage(), 'timeout') !== false) {
                \Log::error("Email fetch timeout for {$config->email_address}: " . $e->getMessage());
                throw new \Exception('Email fetching timed out. The mailbox may be too large. Try reducing the limit or using a more specific filter mode.');
            }
            
            throw $e;
        }
    }

    /**
     * Test email connection helper
     */
    protected function testEmailConnection($config)
    {
        try {
            // Check if IMAP extension is available
            if (!function_exists('imap_open')) {
                $os = PHP_OS;
                $phpVersion = PHP_VERSION;
                $installInstructions = '';
                
                // Provide OS-specific installation instructions
                if (strtoupper(substr($os, 0, 3)) === 'WIN') {
                    $installInstructions = 'On Windows: Edit php.ini and uncomment the line: extension=imap. Then restart your web server.';
                } elseif (strtoupper($os) === 'LINUX' || strtoupper($os) === 'DARWIN') {
                    $installInstructions = 'On Linux/Mac: Run "sudo apt-get install php-imap" (Ubuntu/Debian) or "sudo yum install php-imap" (CentOS/RHEL), then restart your web server.';
                } else {
                    $installInstructions = 'Please install the php-imap extension for your operating system and restart your web server.';
                }
                
                return [
                    'status' => 'failed',
                    'message' => 'IMAP extension is not installed. ' . $installInstructions . ' After installation, verify with: php -m | grep imap',
                    'error' => 'IMAP extension not available. The php-imap extension must be installed and enabled in php.ini.',
                ];
            }

            if ($config->protocol === 'imap') {
                $mailbox = '{' . $config->host . ':' . $config->port . '/' . ($config->ssl_enabled ? 'ssl' : 'notls') . '}' . ($config->folder ?? 'INBOX');
                
                // Set IMAP timeouts to prevent hanging
                @imap_timeout(IMAP_OPENTIMEOUT, 10);
                @imap_timeout(IMAP_READTIMEOUT, 10);
                @imap_timeout(IMAP_WRITETIMEOUT, 10);
                @imap_timeout(IMAP_CLOSETIMEOUT, 10);
                
                // Get password - handle both encrypted (from model) and plain (from test)
                $password = $config->password;
                // If it's an IncidentEmailConfig model, the password getter will decrypt it automatically
                // If it's a plain object, use password as-is
                
                // Suppress IMAP errors for testing
                $connection = @imap_open($mailbox, $config->username, $password, OP_HALFOPEN, 1);
                
                if (!$connection) {
                    $error = imap_last_error();
                    imap_errors(); // Clear error stack
                    imap_alerts(); // Clear alert stack
                    
                    // Provide more helpful error messages
                    $errorMessage = $error ?: 'Unknown error';
                    if (stripos($errorMessage, 'too many login failures') !== false || stripos($errorMessage, 'login failures') !== false) {
                        $errorMessage = 'Too many login failures. The email account may be temporarily locked. Please wait a few minutes and try again.';
                    } elseif (stripos($errorMessage, 'authentication failed') !== false || stripos($errorMessage, 'invalid credentials') !== false) {
                        $errorMessage = 'Authentication failed. Please check your username and password.';
                    } elseif (stripos($errorMessage, 'connection refused') !== false || stripos($errorMessage, 'cannot connect') !== false) {
                        $errorMessage = 'Cannot connect to email server. Please check the host and port settings.';
                    } elseif (stripos($errorMessage, 'certificate') !== false || stripos($errorMessage, 'ssl') !== false) {
                        $errorMessage = 'SSL/TLS certificate error. Please check SSL settings or try disabling SSL if not required.';
                    }
                    
                    return [
                        'status' => 'failed',
                        'message' => 'Connection failed: ' . $errorMessage,
                        'error' => $error ?: 'Failed to connect to IMAP server',
                    ];
                }
                
                // Try to get mailbox info to verify full connection
                $mailboxInfo = @imap_status($connection, $mailbox, SA_ALL);
                
                imap_close($connection);
                
                if ($mailboxInfo) {
                    return [
                        'status' => 'connected',
                        'message' => 'Connection successful! Mailbox accessible.',
                    ];
                } else {
                    return [
                        'status' => 'connected',
                        'message' => 'Connection successful! (Limited mailbox access)',
                    ];
                }
            } else {
                // POP3 connection test
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]);
                
                $host = ($config->ssl_enabled ? 'ssl://' : '') . $config->host;
                $connection = @fsockopen($host, $config->port, $errno, $errstr, 10);
                
                if (!$connection) {
                    $errorMsg = $errstr ?: 'Failed to connect to POP3 server';
                    if ($errno == 0) {
                        $errorMsg = 'Connection timeout. Please check host and port settings.';
                    }
                    return [
                        'status' => 'failed',
                        'message' => 'Connection failed: ' . $errorMsg,
                        'error' => $errorMsg,
                    ];
                }
                
                fclose($connection);
                return [
                    'status' => 'connected',
                    'message' => 'Connection successful!',
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Email connection test error: ' . $e->getMessage(), [
                'config_id' => $config->id,
                'email' => $config->email_address,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'failed',
                'message' => 'Connection error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Notify reporter when incident is created
     */
    protected function notifyReporter(Incident $incident)
    {
        // SMS notification to reporter - try both column names
        $reporterPhone = $incident->reporter_phone ?? 
                        ($incident->reported_by_phone ?? null);
        
        if ($reporterPhone) {
            $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
            $reporterName = $incident->reporter_name ?? 'Valued Customer';
            $message = "Dear {$reporterName}, We have received your issue and reported it under reference number {$incidentNo}. We apologize for any inconvenience caused.";
            
            try {
                $this->notificationService->sendSMS($reporterPhone, $message);
            } catch (\Exception $e) {
                Log::warning('Failed to send reporter SMS: ' . $e->getMessage());
            }
        }

        // Email notification to reporter - try both column names
        $reporterEmail = $incident->reporter_email ?? 
                        ($incident->reported_by_email ?? null);
        
        if ($reporterEmail) {
            try {
                $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
                $title = $incident->title ?? $incident->subject ?? 'Incident';
                $subject = 'Incident ' . $incidentNo . ' - Registered Successfully';
                $message = "Your incident has been registered successfully.\n\nIncident Number: {$incidentNo}\nTitle: {$title}\n\nWe will keep you updated on the progress.";
                
                // Use NotificationService which uses EmailService (PHPMailer)
                $this->notificationService->sendEmail(
                    $reporterEmail,
                    $subject,
                    $message,
                    ['incident' => $incident]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send reporter email: ' . $e->getMessage());
            }
        }

        // System notification if reported by system user
        if ($incident->reported_by) {
            $link = route('modules.incidents.show', $incident->id);
            $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
            $reporterName = $incident->reporter_name ?? 'Valued Customer';
            $message = "Dear {$reporterName}, We have received your issue and reported it under reference number {$incidentNo}. We apologize for any inconvenience caused.";
            $this->notificationService->notify(
                $incident->reported_by,
                $message,
                $link,
                'Incident Registered'
            );
        }
    }

    /**
     * Notify staff when incident is assigned
     */
    protected function notifyAssignment(Incident $incident)
    {
        if ($incident->assignedTo) {
            // SMS notification
            $phone = $incident->assignedTo->mobile ?? $incident->assignedTo->phone;
            if ($phone) {
                $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
                $title = $incident->title ?? $incident->subject ?? 'Incident';
                $priority = ucfirst(strtolower($incident->priority ?? 'medium'));
                $message = "New incident assigned to you: {$incidentNo} - {$title}. Priority: {$priority}. Please check the system for details.";
                
                try {
                    $this->notificationService->sendSMS($phone, $message);
                } catch (\Exception $e) {
                    Log::warning('Failed to send assignment SMS: ' . $e->getMessage());
                }
            }

            // System notification
            $link = route('modules.incidents.show', $incident->id);
            $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
            $this->notificationService->notify(
                $incident->assigned_to,
                "Incident {$incidentNo} has been assigned to you",
                $link,
                'New Incident Assignment'
            );
        }
    }

    /**
     * Notify managers (HR/HOD) about new incidents
     */
    protected function notifyManagers(Incident $incident)
    {
        // Get HR Officers and System Admins
        $managers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['HR Officer', 'System Admin']);
        })->where('is_active', true)->get();

        $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
        $title = $incident->title ?? $incident->subject ?? 'New Incident';
        $link = route('modules.incidents.show', $incident->id);

        foreach ($managers as $manager) {
            // System notification
            $this->notificationService->notify(
                $manager->id,
                "New incident registered: {$incidentNo} - {$title}",
                $link,
                'New Incident'
            );

            // SMS notification for critical/high priority
            if (in_array(strtolower($incident->priority ?? 'medium'), ['critical', 'high'])) {
                $phone = $manager->mobile ?? $manager->phone;
                if ($phone) {
                    $priority = ucfirst(strtolower($incident->priority));
                    $message = "URGENT: New {$priority} priority incident {$incidentNo} - {$title}. Please review immediately.";
                    try {
                        $this->notificationService->sendSMS($phone, $message);
                    } catch (\Exception $e) {
                        Log::warning('Failed to send manager SMS: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Notify reporter when incident is resolved
     */
    protected function notifyResolution(Incident $incident)
    {
        // Notify via email if email available
        if ($incident->reporter_email) {
            try {
                $incidentNo = $incident->incident_no ?? $incident->incident_code ?? 'N/A';
                $subject = 'Incident ' . $incidentNo . ' - Resolved';
                $message = "Your incident {$incidentNo} has been resolved. Thank you for reporting.";
                
                // Use NotificationService which uses EmailService (PHPMailer)
                $this->notificationService->sendEmail(
                    $incident->reporter_email,
                    $subject,
                    $message,
                    ['incident' => $incident]
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send resolution email: ' . $e->getMessage());
            }
        }

        // Notify via SMS if phone available
        if ($incident->reporter_phone) {
            $message = "Your incident {$incident->incident_no} has been resolved. Thank you for reporting.";
            try {
                $this->notificationService->sendSMS($incident->reporter_phone, $message);
            } catch (\Exception $e) {
                \Log::warning('Failed to send resolution SMS: ' . $e->getMessage());
            }
        }

        // Notify system user if reported by system user
        if ($incident->reported_by) {
                $link = route('modules.incidents.show', $incident->id);
            $this->notificationService->notify(
                $incident->reported_by,
                "Your reported incident {$incident->incident_no} has been resolved",
                $link,
                'Incident Resolved'
            );
        }
    }

    /**
     * Check if incident is in same department as user
     */
    protected function isInSameDepartment(Incident $incident, $user)
    {
        if (!$user->primary_department_id) {
            return false;
        }

        if ($incident->assignedTo && $incident->assignedTo->primary_department_id === $user->primary_department_id) {
            return true;
        }

        if ($incident->reporter && $incident->reporter->primary_department_id === $user->primary_department_id) {
            return true;
        }

        return false;
    }

    /**
     * Update incident
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $incident = Incident::findOrFail($id);

        // Check permissions
        $canUpdate = $user->hasAnyRole(['HR Officer', 'System Admin']) ||
                    ($user->hasRole('HOD') && $this->isInSameDepartment($incident, $user)) ||
                    $incident->assigned_to === $user->id;

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this incident.'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Critical,low,medium,high,critical',
            'category' => 'required|in:technical,hr,facilities,security,other',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'nullable|email',
            'reporter_phone' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $priority = ucfirst(strtolower($request->priority));
            
            $updateData = [
                'subject' => $request->title,
                'description' => $request->description,
                'priority' => $priority,
                'category' => $request->category,
                'reporter_name' => $request->reporter_name,
                'reporter_email' => $request->reporter_email,
                'reporter_phone' => $request->reporter_phone,
                'due_date' => $request->due_date,
            ];
            
            // Add updated_by only if column exists
            if (Schema::hasColumn('incidents', 'updated_by')) {
                $updateData['updated_by'] = $user->id;
            }
            
            $incident->update($updateData);

            // Log activity
            ActivityLog::log($user->id, 'incident_updated', "Updated incident {$incident->incident_no}", $incident);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Incident updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating incident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add comment/update to incident
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|min:1',
            'is_internal' => 'boolean',
        ]);

        try {
            $incident = Incident::findOrFail($id);
            $user = Auth::user();

            // Check permissions
            $canComment = $user->hasAnyRole(['HR Officer', 'System Admin']) ||
                         ($user->hasRole('HOD') && $this->isInSameDepartment($incident, $user)) ||
                         $incident->assigned_to === $user->id ||
                         $incident->reported_by === $user->id;

            if (!$canComment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to comment on this incident.'
                ], 403);
            }

            $update = IncidentUpdate::create([
                'incident_id' => $incident->id,
                'user_id' => $user->id,
                'update_text' => $request->comment,
                'is_internal_note' => $request->is_internal ?? false,
            ]);

            // Log activity
            ActivityLogService::logAction('incident_comment_added', "Added comment to incident {$incident->incident_no}", $incident, [
                'incident_no' => $incident->incident_no,
                'comment_id' => $update->id,
                'is_internal' => $request->is_internal ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully.',
                'update' => $update->load('user'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get incident timeline/updates
     */
    public function getTimeline($id)
    {
        $incident = Incident::findOrFail($id);
        $user = Auth::user();

        // Check permissions
        $canView = $user->hasAnyRole(['HR Officer', 'System Admin']) ||
                  ($user->hasRole('HOD') && $this->isInSameDepartment($incident, $user)) ||
                  $incident->assigned_to === $user->id ||
                  $incident->reported_by === $user->id;

        if (!$canView) {
            abort(403);
        }

        $updates = IncidentUpdate::where('incident_id', $incident->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get activity logs
        $activities = ActivityLog::where('model_type', Incident::class)
            ->where('model_id', $incident->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'updates' => $updates,
            'activities' => $activities,
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and HODs can perform bulk actions.'
            ], 403);
        }

        // Handle incident_ids - can be array or JSON string
        $incidentIds = $request->input('incident_ids', []);
        if (is_string($incidentIds)) {
            $incidentIds = json_decode($incidentIds, true) ?? [];
        }
        if (!is_array($incidentIds) || empty($incidentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one incident.'
            ], 400);
        }

        // Validate action and related fields
        $request->validate([
            'action' => 'required|in:assign,update_status,delete,export',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
            'status' => 'required_if:action,update_status|in:New,Assigned,In Progress,Resolved,Closed,Cancelled',
        ]);

        // Validate incident IDs exist
        $validIds = Incident::whereIn('id', $incidentIds)->pluck('id')->toArray();
        if (empty($validIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid incidents found.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $incidents = Incident::whereIn('id', $validIds);
            $count = 0;

            switch ($request->action) {
                case 'assign':
                    $assignData = [
                        'assigned_to' => $request->assigned_to,
                        'assigned_by' => $user->id,
                        'status' => 'Assigned',
                    ];
                    // Add assigned_at only if column exists
                    if (Schema::hasColumn('incidents', 'assigned_at')) {
                        $assignData['assigned_at'] = now();
                    }
                    // Add updated_by only if column exists
                    if (Schema::hasColumn('incidents', 'updated_by')) {
                        $assignData['updated_by'] = $user->id;
                    }
                    $incidents->update($assignData);
                    $count = $incidents->count();
                    break;

                case 'update_status':
                    $updateData = [
                        'status' => $request->status,
                    ];
                    // Add updated_by only if column exists
                    if (Schema::hasColumn('incidents', 'updated_by')) {
                        $updateData['updated_by'] = $user->id;
                    }
                    if ($request->status === 'Resolved') {
                        $updateData['resolved_at'] = now();
                        $updateData['resolved_by'] = $user->id;
                    } elseif ($request->status === 'Closed') {
                        $updateData['closed_at'] = now();
                        $updateData['closed_by'] = $user->id;
                    }
                    $incidents->update($updateData);
                    $count = $incidents->count();
                    break;

                case 'delete':
                    $count = count($validIds);
                    $incidents->delete();
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$count} incident(s)."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export incidents page (with filters)
     */
    public function exportPage()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403);
        }
        
        return view('modules.incidents.export', compact('user'));
    }
    
    /**
     * Export incidents (download)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403);
        }

        $query = Incident::with(['reporter', 'assignedTo', 'resolvedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Role-based filtering
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            if ($user->hasRole('HOD') && $user->primary_department_id) {
                $query->where(function($q) use ($user) {
                    $q->whereHas('assignedTo', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    })->orWhereHas('reporter', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    });
                });
            }
        }

        $incidents = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'incidents_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($incidents) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Incident #',
                'Title',
                'Description',
                'Priority',
                'Status',
                'Category',
                'Reporter Name',
                'Reporter Email',
                'Reporter Phone',
                'Assigned To',
                'Created At',
                'Resolved At',
                'Resolution Notes',
            ]);

            // Data
            foreach ($incidents as $incident) {
                fputcsv($file, [
                    $incident->incident_no ?? $incident->incident_code ?? 'N/A',
                    $incident->title ?? $incident->subject ?? 'N/A',
                    $incident->description,
                    $incident->priority,
                    $incident->status,
                    $incident->category,
                    $incident->reporter_name ?? ($incident->reporter->name ?? 'N/A'),
                    $incident->reporter_email ?? ($incident->reporter->email ?? 'N/A'),
                    $incident->reporter_phone ?? 'N/A',
                    $incident->assignedTo->name ?? 'Unassigned',
                    $incident->created_at->format('Y-m-d H:i:s'),
                    $incident->resolved_at ? $incident->resolved_at->format('Y-m-d H:i:s') : 'N/A',
                    $incident->resolution_notes ?? $incident->resolution_details ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Dashboard - Main page showing incident overview
     */
    public function dashboard()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageIncidents = in_array('System Admin', $userRoles) || 
                             in_array('HR Officer', $userRoles) || 
                             in_array('HOD', $userRoles) || 
                             in_array('CEO', $userRoles);
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                     in_array('CEO', $userRoles) || 
                     in_array('HR Officer', $userRoles);
        
        $currentDeptId = $user->primary_department_id ?? null;
        
        // Build base query with role-based filtering
        $baseQuery = Incident::with(['reporter', 'assignedTo', 'assignedBy', 'resolvedBy']);
        
        if (!$canViewAll) {
            if (in_array('HOD', $userRoles) && $currentDeptId) {
                $baseQuery->where(function($q) use ($user, $currentDeptId) {
                    $q->whereHas('assignedTo', function($sq) use ($currentDeptId) {
                        $sq->where('primary_department_id', $currentDeptId);
                    })->orWhereHas('reporter', function($sq) use ($currentDeptId) {
                        $sq->where('primary_department_id', $currentDeptId);
                    })->orWhere('created_by', $user->id);
                });
            } else {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('reported_by', $user->id)
                      ->orWhere('created_by', $user->id);
                });
            }
        }
        
        // Get statistics
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Get all incidents for the dashboard (limit to 50 for performance)
        $allIncidents = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
        
        // Get recent incidents for activity section
        $recentIncidents = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get incidents by status for dashboard
        $incidentsByStatus = [
            'New' => (clone $baseQuery)->where('status', 'New')->count(),
            'Assigned' => (clone $baseQuery)->where('status', 'Assigned')->count(),
            'In Progress' => (clone $baseQuery)->where('status', 'In Progress')->count(),
            'Resolved' => (clone $baseQuery)->where('status', 'Resolved')->count(),
            'Closed' => (clone $baseQuery)->where('status', 'Closed')->count(),
        ];
        
        // Get incidents by priority
        $incidentsByPriority = [
            'Critical' => (clone $baseQuery)->where('priority', 'Critical')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
            'High' => (clone $baseQuery)->where('priority', 'High')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
            'Medium' => (clone $baseQuery)->where('priority', 'Medium')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
            'Low' => (clone $baseQuery)->where('priority', 'Low')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count(),
        ];
        
        // Get my assigned incidents
        $myAssignedIncidents = (clone $baseQuery)
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('modules.incidents.dashboard', compact(
            'stats',
            'allIncidents',
            'recentIncidents',
            'incidentsByStatus',
            'incidentsByPriority',
            'myAssignedIncidents',
            'canManageIncidents',
            'canViewAll',
            'user'
        ));
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($user, $canViewAll, $currentDeptId)
    {
        // Build base query with role-based filtering
        $baseQuery = Incident::query();
        
        if (!$canViewAll) {
            $userRoles = $user->roles()->pluck('name')->toArray();
            if (in_array('HOD', $userRoles) && $currentDeptId) {
                $baseQuery->where(function($q) use ($user, $currentDeptId) {
                    $q->whereHas('assignedTo', function($sq) use ($currentDeptId) {
                        $sq->where('primary_department_id', $currentDeptId);
                    })->orWhereHas('reporter', function($sq) use ($currentDeptId) {
                        $sq->where('primary_department_id', $currentDeptId);
                    })->orWhere('created_by', $user->id);
                });
            } else {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('reported_by', $user->id)
                      ->orWhere('created_by', $user->id);
                });
            }
        }
        
        $totalIncidents = (clone $baseQuery)->count();
        $newIncidents = (clone $baseQuery)->where('status', 'New')->count();
        $assignedIncidents = (clone $baseQuery)->where('status', 'Assigned')->count();
        $inProgressIncidents = (clone $baseQuery)->where('status', 'In Progress')->count();
        $resolvedIncidents = (clone $baseQuery)->where('status', 'Resolved')->count();
        $closedIncidents = (clone $baseQuery)->where('status', 'Closed')->count();
        $myAssigned = (clone $baseQuery)->where('assigned_to', $user->id)->whereIn('status', ['Assigned', 'In Progress'])->count();
        $criticalIncidents = (clone $baseQuery)->where('priority', 'Critical')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count();
        $highIncidents = (clone $baseQuery)->where('priority', 'High')->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled'])->count();
        $incidentsThisMonth = (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $resolvedThisMonth = (clone $baseQuery)->where('status', 'Resolved')->whereMonth('resolved_at', now()->month)->whereYear('resolved_at', now()->year)->count();
        
        // Calculate average resolution time (in days)
        $resolvedWithTime = (clone $baseQuery)
            ->where('status', 'Resolved')
            ->whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->get();
        
        $avgResolutionTime = 0;
        if ($resolvedWithTime->count() > 0) {
            $totalDays = $resolvedWithTime->sum(function($incident) {
                return $incident->created_at->diffInDays($incident->resolved_at);
            });
            $avgResolutionTime = round($totalDays / $resolvedWithTime->count(), 1);
        }
        
        return [
            'total_incidents' => $totalIncidents,
            'new_incidents' => $newIncidents,
            'assigned_incidents' => $assignedIncidents,
            'in_progress_incidents' => $inProgressIncidents,
            'resolved_incidents' => $resolvedIncidents,
            'closed_incidents' => $closedIncidents,
            'my_assigned' => $myAssigned,
            'critical_incidents' => $criticalIncidents,
            'high_incidents' => $highIncidents,
            'incidents_this_month' => $incidentsThisMonth,
            'resolved_this_month' => $resolvedThisMonth,
            'avg_resolution_time' => $avgResolutionTime,
        ];
    }
    
    /**
     * Analytics page
     */
    public function analyticsPage()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403);
        }
        
        return view('modules.incidents.analytics', compact('user'));
    }
    
    /**
     * Get analytics/dashboard data (AJAX)
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'System Admin'])) {
            abort(403);
        }

        $baseQuery = Incident::query();

        // Role-based filtering
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            if ($user->hasRole('HOD') && $user->primary_department_id) {
                $baseQuery->where(function($q) use ($user) {
                    $q->whereHas('assignedTo', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    })->orWhereHas('reporter', function($sq) use ($user) {
                        $sq->where('primary_department_id', $user->primary_department_id);
                    });
                });
            }
        }

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $baseQuery->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        // Status distribution
        $statusDistribution = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Priority distribution
        $priorityDistribution = (clone $baseQuery)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Category distribution
        $categoryDistribution = (clone $baseQuery)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Resolution time (average days)
        $resolvedIncidents = (clone $baseQuery)
            ->whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->get();

        $avgResolutionTime = 0;
        if ($resolvedIncidents->count() > 0) {
            $avgResolutionTime = $resolvedIncidents->map(function($incident) {
                if ($incident->created_at && $incident->resolved_at) {
                    return $incident->created_at->diffInDays($incident->resolved_at);
                }
                return 0;
            })->filter(function($days) {
                return $days >= 0;
            })->avg();
        }

        // Monthly trends
        $monthlyTrends = (clone $baseQuery)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Top assignees
        $topAssigneesRaw = (clone $baseQuery)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as count')
            ->groupBy('assigned_to')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $userIds = $topAssigneesRaw->pluck('assigned_to')->toArray();
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        $topAssignees = $topAssigneesRaw->map(function($item) use ($users) {
            return [
                'user' => $users[$item->assigned_to] ?? 'Unknown',
                'count' => $item->count,
            ];
        });

        // Daily trends (last 30 days or date range)
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $daysDiff = $startDate->diffInDays($endDate);
        $dailyTrends = [];
        
        if ($daysDiff <= 90) {
            // Daily for <= 90 days
            $dailyTrendsRaw = (clone $baseQuery)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();
            
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dailyTrends[$dateStr] = $dailyTrendsRaw[$dateStr] ?? 0;
                $currentDate->addDay();
            }
        }

        // Department distribution
        $departmentDistribution = [];
        $incidentsWithDept = (clone $baseQuery)
            ->whereHas('assignedTo', function($q) {
                $q->whereNotNull('primary_department_id');
            })
            ->with('assignedTo.department')
            ->get();
        
        $deptGroups = $incidentsWithDept->groupBy(function($incident) {
            if ($incident->assignedTo && $incident->assignedTo->department) {
                return $incident->assignedTo->department->name ?? 'Unassigned';
            }
            return 'Unassigned';
        });
        
        $departmentDistribution = $deptGroups->map(function($group) {
            return $group->count();
        })->toArray();

        // Resolution time distribution (buckets)
        $resolutionTimeBuckets = [
            '0-1 days' => 0,
            '1-3 days' => 0,
            '3-7 days' => 0,
            '7-14 days' => 0,
            '14-30 days' => 0,
            '30+ days' => 0,
        ];

        $resolvedIncidents->each(function($incident) use (&$resolutionTimeBuckets) {
            if ($incident->created_at && $incident->resolved_at) {
                $days = $incident->created_at->diffInDays($incident->resolved_at);
                if ($days <= 1) {
                    $resolutionTimeBuckets['0-1 days']++;
                } elseif ($days <= 3) {
                    $resolutionTimeBuckets['1-3 days']++;
                } elseif ($days <= 7) {
                    $resolutionTimeBuckets['3-7 days']++;
                } elseif ($days <= 14) {
                    $resolutionTimeBuckets['7-14 days']++;
                } elseif ($days <= 30) {
                    $resolutionTimeBuckets['14-30 days']++;
                } else {
                    $resolutionTimeBuckets['30+ days']++;
                }
            }
        });

        // Status over time (monthly)
        $statusOverTime = [];
        $statuses = ['New', 'Assigned', 'In Progress', 'Resolved', 'Closed', 'Rejected'];
        foreach ($statuses as $status) {
            $statusMonthly = (clone $baseQuery)
                ->where('status', $status)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();
            $statusOverTime[$status] = $statusMonthly;
        }

        // Priority trends (monthly)
        $priorityTrends = [];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        foreach ($priorities as $priority) {
            $priorityMonthly = (clone $baseQuery)
                ->where('priority', $priority)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();
            $priorityTrends[$priority] = $priorityMonthly;
        }

        // Response time (time to first assignment)
        $responseTimes = (clone $baseQuery)
            ->whereNotNull('assigned_to')
            ->whereNotNull('assigned_at')
            ->get()
            ->map(function($incident) {
                if ($incident->created_at && $incident->assigned_at) {
                    return $incident->created_at->diffInHours($incident->assigned_at);
                }
                return null;
            })
            ->filter()
            ->values();

        $avgResponseTime = $responseTimes->count() > 0 ? round($responseTimes->avg(), 2) : 0;
        $medianResponseTime = $responseTimes->count() > 0 ? round($responseTimes->median(), 2) : 0;

        // Additional counts
        $newCount = (clone $baseQuery)->where('status', 'New')->count();
        $assignedCount = (clone $baseQuery)->where('status', 'Assigned')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'In Progress')->count();
        $resolvedCount = (clone $baseQuery)->where('status', 'Resolved')->count();
        $closedCount = (clone $baseQuery)->where('status', 'Closed')->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'Rejected')->count();

        // Priority counts
        $lowPriorityCount = (clone $baseQuery)->where('priority', 'Low')->count();
        $mediumPriorityCount = (clone $baseQuery)->where('priority', 'Medium')->count();
        $highPriorityCount = (clone $baseQuery)->where('priority', 'High')->count();
        $criticalPriorityCount = (clone $baseQuery)->where('priority', 'Critical')->count();

        // Top reporters
        $topReportersRaw = (clone $baseQuery)
            ->whereNotNull('reported_by')
            ->selectRaw('reported_by, COUNT(*) as count')
            ->groupBy('reported_by')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $reporterIds = $topReportersRaw->pluck('reported_by')->toArray();
        $reporters = User::whereIn('id', $reporterIds)->pluck('name', 'id');

        $topReporters = $topReportersRaw->map(function($item) use ($reporters) {
            return [
                'user' => $reporters[$item->reported_by] ?? 'Unknown',
                'count' => $item->count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'status_distribution' => $statusDistribution,
                'priority_distribution' => $priorityDistribution,
                'category_distribution' => $categoryDistribution,
                'department_distribution' => $departmentDistribution,
                'avg_resolution_time' => round($avgResolutionTime, 2),
                'avg_response_time' => $avgResponseTime,
                'median_response_time' => $medianResponseTime,
                'resolution_time_buckets' => $resolutionTimeBuckets,
                'monthly_trends' => $monthlyTrends,
                'daily_trends' => $dailyTrends,
                'status_over_time' => $statusOverTime,
                'priority_trends' => $priorityTrends,
                'top_assignees' => $topAssignees,
                'top_reporters' => $topReporters,
                'total_incidents' => (clone $baseQuery)->count(),
                'resolved_count' => $resolvedCount,
                'closed_count' => $closedCount,
                'open_count' => (clone $baseQuery)->whereIn('status', ['New', 'Assigned', 'In Progress'])->count(),
                'new_count' => $newCount,
                'assigned_count' => $assignedCount,
                'in_progress_count' => $inProgressCount,
                'rejected_count' => $rejectedCount,
                'low_priority_count' => $lowPriorityCount,
                'medium_priority_count' => $mediumPriorityCount,
                'high_priority_count' => $highPriorityCount,
                'critical_priority_count' => $criticalPriorityCount,
                'resolution_rate' => (clone $baseQuery)->count() > 0 
                    ? round((($resolvedCount + $closedCount) / (clone $baseQuery)->count()) * 100, 2) 
                    : 0,
            ],
        ]);
    }
}

