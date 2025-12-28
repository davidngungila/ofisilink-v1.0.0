<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Notification;
use App\Models\IncidentInbox;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncidentsController extends Controller
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
        $isManager = count(array_intersect($userRoleNames, ['System Admin','CEO','HOD','HR Officer'])) > 0;
        $isHodOrHr = count(array_intersect($userRoleNames, ['HOD','HR Officer'])) > 0;

		$activeTab = $request->query('tab', $isManager ? 'dashboard' : 'my_incidents');
		$filterStatus = $request->query('status', '');
		$filterPriority = $request->query('priority', '');
		$filterSearch = $request->query('search', '');
		$filterDateFrom = $request->query('date_from', '');
		$filterDateTo = $request->query('date_to', '');
		$filterAssignedTo = $request->query('assigned_to', '');
		$filterShowResolved = $request->query('show_resolved', '0');

		$incidentsByStatus = [];
        $dashboardStats = ['Awaiting Review' => 0, 'New' => 0, 'Assigned' => 0, 'In Progress' => 0, 'Pending Approval' => 0, 'Resolved' => 0, 'Overdue' => 0, 'Total' => 0];
		$allIncidents = [];

		$query = Incident::query()->with(['assignee','creator']);

		if ($filterStatus && $filterStatus !== 'all') {
			$query->where('status', $filterStatus);
		}
		if ($filterPriority && $filterPriority !== 'all') {
			$query->where('priority', $filterPriority);
		}
		if ($filterSearch) {
			$query->where(function($q) use ($filterSearch) {
				$q->where('subject', 'like', "%".self::cleanText($filterSearch)."%")
					->orWhere('description', 'like', "%".self::cleanText($filterSearch)."%")
					->orWhere('reported_by_name', 'like', "%".self::cleanText($filterSearch)."%")
					->orWhere('reported_by_email', 'like', "%".self::cleanText($filterSearch)."%");
			});
		}
		if ($filterDateFrom) {
			$query->whereDate('created_at', '>=', $filterDateFrom);
		}
		if ($filterDateTo) {
			$query->whereDate('created_at', '<=', $filterDateTo);
		}
		if ($filterAssignedTo && $filterAssignedTo !== 'all') {
			$query->where('assigned_to', $filterAssignedTo);
		}
		if ($filterShowResolved === '0') {
			$query->where('status', '!=', 'Resolved');
		}

		if ($isManager) {
            $stats = Incident::selectRaw('COUNT(*) as total')
				->selectRaw("SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new_count")
				->selectRaw("SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) as assigned_count")
				->selectRaw("SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count")
				->selectRaw("SUM(CASE WHEN status = 'Pending Approval' THEN 1 ELSE 0 END) as pending_count")
				->selectRaw("SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_count")
                ->selectRaw("SUM(CASE WHEN status = 'Awaiting Review' THEN 1 ELSE 0 END) as awaiting_count")
				->selectRaw("SUM(CASE WHEN due_date < CURDATE() AND status NOT IN ('Resolved','Closed') THEN 1 ELSE 0 END) as overdue_count")
				->first();
			$dashboardStats = [
                'Awaiting Review' => (int)($stats->awaiting_count ?? 0),
				'New' => (int)$stats->new_count,
				'Assigned' => (int)$stats->assigned_count,
				'In Progress' => (int)$stats->in_progress_count,
				'Pending Approval' => (int)$stats->pending_count,
				'Resolved' => (int)$stats->resolved_count,
				'Overdue' => (int)$stats->overdue_count,
				'Total' => (int)$stats->total,
			];

			$allIncidents = $query->orderByRaw("FIELD(priority, 'Critical','High','Medium','Low')")
				->orderByDesc('created_at')->get();
			foreach ($allIncidents as $row) {
				$incidentsByStatus[$row->status] = $incidentsByStatus[$row->status] ?? [];
				$incidentsByStatus[$row->status][] = $row;
			}
		} else {
			$allIncidents = Incident::with('creator')
				->where('assigned_to', $user->id)
				->when($filterShowResolved === '0', fn($q) => $q->where('status','!=','Resolved'))
				->orderByRaw("FIELD(priority, 'Critical','High','Medium','Low')")
				->orderByDesc('created_at')
				->get();
			foreach ($allIncidents as $row) {
				$incidentsByStatus[$row->status] = $incidentsByStatus[$row->status] ?? [];
				$incidentsByStatus[$row->status][] = $row;
			}
		}

        $users = User::orderBy('name')->get(['id','name']);
        $inboxItems = [];
        if ($isManager) {
            $inboxItems = IncidentInbox::where('status','Pending')->orderByDesc('received_at')->limit(100)->get();
        }

		return view('modules.incidents.index', [
			'activeTab' => $activeTab,
			'isManager' => $isManager,
            'dashboardStats' => $dashboardStats,
			'incidentsByStatus' => $incidentsByStatus,
			'allIncidents' => $allIncidents,
			'users' => $users,
            'inboxItems' => $inboxItems,
            'isHodOrHr' => $isHodOrHr,
			'filters' => [
				'status' => $filterStatus,
				'priority' => $filterPriority,
				'search' => $filterSearch,
				'date_from' => $filterDateFrom,
				'date_to' => $filterDateTo,
				'assigned_to' => $filterAssignedTo,
				'show_resolved' => $filterShowResolved,
			],
		]);
	}

	public function action(Request $request)
	{
		$user = Auth::user();
		$userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
		$isManager = count(array_intersect($userRoleNames, ['System Admin','CEO','HOD','HR Officer'])) > 0;
		$isHodOrHr = count(array_intersect($userRoleNames, ['HOD','HR Officer', 'System Admin'])) > 0;

		$action = $request->input('action');

		return DB::transaction(function () use ($request, $user, $isManager, $isHodOrHr, $action) {
			switch ($action) {
                case 'create_incident':
                    if (!$isHodOrHr) abort(403);
                    // Implementation would go here - for now just break
                    break;
                case 'fetch_inbox_from_email':
                    if (!$isManager) abort(403);
                    
                    // Check if config_id is provided (new way)
                    $configId = $request->input('config_id');
                    if ($configId) {
                        $config = \App\Models\IncidentEmailConfig::findOrFail($configId);
                        $host = $config->host;
                        $usern = $config->username;
                        $pass = $config->password;
                        $mailbox = '{' . $config->host . ':' . $config->port . '/' . ($config->ssl_enabled ? 'ssl' : 'notls') . '}' . ($config->folder ?? 'INBOX');
                    } else {
                        // Fallback to env variables (old way)
                        $host = env('INCIDENT_IMAP_HOST');
                        $usern = env('INCIDENT_IMAP_USERNAME');
                        $pass = env('INCIDENT_IMAP_PASSWORD');
                        $mailbox = env('INCIDENT_IMAP_MAILBOX', '{imap.gmail.com:993/imap/ssl}INBOX');
                    }
                    
                    // Get parameters for date range and fetch mode
                    $dateFrom = $request->input('date_from');
                    $dateTo = $request->input('date_to');
                    $fetchMode = $request->input('fetch_mode', 'unseen'); // 'unseen', 'all', or 'date_range'
                    $maxEmails = (int)$request->input('max_emails', 500); // Limit to prevent crashes
                    
                    $imported = 0;
                    $skipped = 0;
                    
                    if (function_exists('imap_open') && $host && $usern && $pass) {
                        set_time_limit(300); // 5 minutes max
                        
                        $inbox = @imap_open($mailbox, $usern, $pass);
                        if ($inbox) {
                            // Build search criteria based on fetch mode
                            $searchCriteria = [];
                            
                            if ($fetchMode === 'all') {
                                // Fetch all emails (not just unseen)
                                if ($dateFrom) {
                                    $from = date('d-M-Y', strtotime($dateFrom));
                                    $searchCriteria[] = "SINCE \"{$from}\"";
                                } else {
                                    // Default: last 30 days if no date specified
                                    $from = date('d-M-Y', strtotime('-30 days'));
                                    $searchCriteria[] = "SINCE \"{$from}\"";
                                }
                                
                                if ($dateTo) {
                                    $to = date('d-M-Y', strtotime($dateTo . ' +1 day'));
                                    $searchCriteria[] = "BEFORE \"{$to}\"";
                                }
                            } elseif ($fetchMode === 'date_range' && $dateFrom && $dateTo) {
                                // Fetch emails in date range (including seen)
                                $from = date('d-M-Y', strtotime($dateFrom));
                                $to = date('d-M-Y', strtotime($dateTo . ' +1 day'));
                                $searchCriteria[] = "SINCE \"{$from}\"";
                                $searchCriteria[] = "BEFORE \"{$to}\"";
                            } else {
                                // Default: UNSEEN emails only
                                $searchCriteria[] = 'UNSEEN';
                                if ($dateFrom) {
                                    $from = date('d-M-Y', strtotime($dateFrom));
                                    $searchCriteria[] = "SINCE \"{$from}\"";
                                }
                                if ($dateTo) {
                                    $to = date('d-M-Y', strtotime($dateTo . ' +1 day'));
                                    $searchCriteria[] = "BEFORE \"{$to}\"";
                                }
                            }
                            
                            $searchString = implode(' ', $searchCriteria) ?: 'ALL';
                            
                            \Log::info("Fetching emails with criteria: {$searchString}");
                            
                            $emails = @imap_search($inbox, $searchString);
                            
                            if ($emails === false) {
                                $error = imap_last_error();
                                \Log::warning("IMAP search failed: {$error}. Criteria: {$searchString}");
                                imap_close($inbox);
                                return response()->json([
                                    'success' => false,
                                    'message' => "Failed to search emails: " . ($error ?: 'Unknown error')
                                ]);
                            }
                            
                            if (!is_array($emails) || empty($emails)) {
                                imap_close($inbox);
                                return response()->json([
                                    'success' => true,
                                    'message' => "No emails found with the specified criteria."
                                ]);
                            }
                            
                            // Limit number of emails to process
                            if (count($emails) > $maxEmails) {
                                $emails = array_slice($emails, 0, $maxEmails);
                                \Log::warning("Limiting fetch to {$maxEmails} emails (found " . count($emails) . " total)");
                            }
                            
                            \Log::info("Processing " . count($emails) . " email(s)");
                            
                            // Sort emails by message number (newest first)
                            rsort($emails);
                            
                            foreach ($emails as $email_number) {
                                try {
                                    $header = @imap_headerinfo($inbox, $email_number);
                                    if (!$header) {
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    $overview = imap_fetch_overview($inbox, $email_number, 0)[0] ?? null;
                                    $messageId = $overview->message_id ?? null;
                                    
                                    // Check if email already exists (by message_id or by subject+from+date)
                                    if ($messageId && IncidentInbox::where('message_id', $messageId)->exists()) {
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Also check by subject, from email, and date to catch duplicates without message_id
                                    $fromEmail = ($header->from[0]->mailbox ?? '') . '@' . ($header->from[0]->host ?? '');
                                    $subject = $overview->subject ?? '(No subject)';
                                    $emailDate = isset($header->date) ? date('Y-m-d', strtotime($header->date)) : null;
                                    
                                    if ($emailDate && IncidentInbox::where('from_email', $fromEmail)
                                        ->where('subject', $subject)
                                        ->whereDate('received_at', $emailDate)
                                        ->exists()) {
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Get email body - handle multipart emails
                                    $structure = @imap_fetchstructure($inbox, $email_number);
                                    $body = '';
                                    
                                    if ($structure) {
                                        // Try to get HTML body first, then plain text
                                        if (isset($structure->parts) && is_array($structure->parts)) {
                                            foreach ($structure->parts as $partNum => $part) {
                                                $partNumber = $partNum + 1;
                                                if (isset($part->subtype) && strtolower($part->subtype) === 'html') {
                                                    $body = @imap_fetchbody($inbox, $email_number, $partNumber);
                                                    if ($body) {
                                                        $body = quoted_printable_decode($body);
                                                        break;
                                                    }
                                                }
                                            }
                                            // If no HTML, try plain text
                                            if (empty($body)) {
                                                foreach ($structure->parts as $partNum => $part) {
                                                    $partNumber = $partNum + 1;
                                                    if (isset($part->subtype) && strtolower($part->subtype) === 'plain') {
                                                        $body = @imap_fetchbody($inbox, $email_number, $partNumber);
                                                        if ($body) {
                                                            $body = quoted_printable_decode($body);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Fallback: try simple fetch
                                    if (empty($body)) {
                                        $body = @imap_fetchbody($inbox, $email_number, 1.2);
                                        if (empty($body)) {
                                            $body = @imap_fetchbody($inbox, $email_number, 1);
                                        }
                                        if ($body) {
                                            $body = quoted_printable_decode($body);
                                        }
                                    }
                                    
                                    // Decode if needed
                                    if (!empty($body)) {
                                        $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
                                    }
                                    
                                    $receivedAt = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : now();
                                    
                                    IncidentInbox::create([
                                        'message_id' => $messageId,
                                        'from_name' => $header->from[0]->personal ?? null,
                                        'from_email' => $fromEmail,
                                        'subject' => $subject,
                                        'body' => $body ?: '(No body)',
                                        'received_at' => $receivedAt,
                                        'status' => 'Pending',
                                    ]);
                                    $imported++;
                                    
                                } catch (\Exception $e) {
                                    \Log::error("Error processing email {$email_number}: " . $e->getMessage());
                                    $skipped++;
                                    continue;
                                }
                            }
                            
                            imap_close($inbox);
                        } else {
                            $error = imap_last_error() ?: 'Unknown error';
                            \Log::error("IMAP connection failed: {$error}");
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to connect to email server: {$error}"
                            ]);
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'IMAP extension not available or credentials not configured.'
                        ]);
                    }
                    
                    $message = "Fetched {$imported} email(s)";
                    if ($skipped > 0) {
                        $message .= ", skipped {$skipped} duplicate(s)";
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'imported' => $imported,
                        'skipped' => $skipped
                    ]);

                case 'promote_inbox_to_incident':
                    if (!$isHodOrHr) abort(403);
                    $inbox = IncidentInbox::findOrFail((int)$request->input('inbox_id'));
                    $incidentCode = 'ICD-' . now()->format('Ymd') . '-' . str_pad((Incident::whereDate('created_at', today())->max('id') % 1000) + 1, 3, '0', STR_PAD_LEFT);
                    $incident = Incident::create([
                        'incident_code' => $incidentCode,
                        'subject' => $request->string('subject', $inbox->subject ?? ''),
                        'description' => $request->input('description', $inbox->body ?? ''),
                        'reported_by_name' => $request->input('reported_by_name', $inbox->from_name ?? ''),
                        'reported_by_email' => $request->input('reported_by_email', $inbox->from_email ?? ''),
                        'priority' => $request->string('priority','Medium'),
                        'due_date' => $request->date('due_date'),
                        'created_by' => $user->id,
                    ]);
                    $inbox->update(['status'=>'Promoted']);
                    return response()->json(['success'=>true,'message'=>'Incident created from inbox.','id'=>$incident->id,'incident_code'=>$incidentCode]);
					
					// Generate incident code (ICD-DATE+001)
					$today = now()->format('Ymd');
					$lastIncident = Incident::whereDate('created_at', today())
						->orderBy('id', 'desc')
						->first();
					
					$sequence = 1;
					if ($lastIncident && $lastIncident->incident_code) {
						$lastSequence = (int)substr($lastIncident->incident_code, -3);
						$sequence = $lastSequence + 1;
					}
					
					$incidentCode = 'ICD-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
					
                    $incident = Incident::create([
						'incident_code' => $incidentCode,
                        'source' => 'manual',
						'subject' => $request->string('subject'),
						'description' => $request->input('description'),
						'reported_by_name' => $request->input('reported_by_name'),
						'reported_by_email' => $request->input('reported_by_email'),
						'reported_by_phone' => $request->input('reported_by_phone'),
						'priority' => $request->string('priority','Medium'),
						'due_date' => $request->date('due_date'),
						'created_by' => $user->id,
                        'status' => 'New',
                    ]);
                case 'ingest_email':
                    // Webhook to ingest incident from email gateway
                    $token = $request->header('X-Incident-Token') ?: $request->input('token');
                    if ($token !== config('app.key')) abort(403);

                    $subject = (string)$request->input('subject');
                    $body = (string)$request->input('body');
                    $fromEmail = (string)$request->input('from_email');
                    $fromName = (string)$request->input('from_name');
                    $rawId = (string)$request->input('message_id');

                    $today = now()->format('Ymd');
                    $lastIncident = Incident::whereDate('created_at', today())->orderBy('id','desc')->first();
                    $sequence = 1;
                    if ($lastIncident && $lastIncident->incident_code) {
                        $lastSequence = (int)substr($lastIncident->incident_code, -3);
                        $sequence = $lastSequence + 1;
                    }
                    $incidentCode = 'ICD-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

                    $incident = Incident::create([
                        'incident_code' => $incidentCode,
                        'source' => 'email',
                        'raw_email_id' => $rawId ?: null,
                        'subject' => $subject ?: '(no subject)',
                        'description' => $body ?: '(no content)',
                        'reported_by_name' => $fromName ?: null,
                        'reported_by_email' => $fromEmail ?: null,
                        'priority' => 'Medium',
                        'status' => 'Awaiting Review',
                        'created_by' => 0,
                    ]);

                    return response()->json(['success' => true, 'message' => 'Email ingested', 'id' => $incident->id, 'incident_code' => $incidentCode]);

                case 'review_incident':
                    if (!$isManager) abort(403);
                    $incident = Incident::findOrFail((int)$request->input('incident_id'));
                    $approve = $request->boolean('approve', true);
                    if ($approve) {
                        $incident->update(['status' => 'New']);
                        return response()->json(['success' => true, 'message' => 'Incident approved for processing.']);
                    } else {
                        $incident->update(['status' => 'Archived']);
                        return response()->json(['success' => true, 'message' => 'Incident archived.']);
                    }
					
					// Notify reporter if email/phone provided
					if ($incident->reported_by_email || $incident->reported_by_phone) {
						$reporterMessage = "Your issue has been registered. Tracking Code: {$incidentCode}. We will start resolving it soon.";
						if ($incident->reported_by_email) {
							$this->notificationService->notify(
								User::where('email', $incident->reported_by_email)->first()?->id ?? 0,
								$reporterMessage,
								null,
								'Incident Registered - ' . $incidentCode,
								['incident_code' => $incidentCode, 'subject' => $incident->subject]
							);
						}
						if ($incident->reported_by_phone) {
							$this->notificationService->sendSMS($incident->reported_by_phone, $reporterMessage);
						}
					}
					
					return response()->json(['success' => true, 'message' => 'New incident created successfully.', 'id' => $incident->id, 'incident_code' => $incidentCode]);

				case 'get_incident_details':
					$incidentId = (int)$request->input('incident_id');
					$incident = Incident::with(['creator:id,name','assignee:id,name','assigner:id,name'])
						->findOrFail($incidentId);
					if (!$isManager && $incident->assigned_to !== $user->id) abort(403);
					$updates = IncidentUpdate::with('user:id,name')
						->where('incident_id', $incident->id)
						->orderByDesc('created_at')
						->get()
						->map(function($u){
							return [
								'id' => $u->id,
								'update_text' => $u->update_text,
								'is_internal_note' => $u->is_internal_note,
								'created_at' => $u->created_at->toDateTimeString(),
								'name' => $u->user->name,
							];
						});
					return response()->json(['success' => true, 'details' => $incident, 'updates' => $updates]);

				case 'assign_incident':
					if (!$isManager) abort(403);
					$incident = Incident::where('id', (int)$request->input('incident_id'))
						->where('status', 'New')->firstOrFail();
					$assigneeId = (int)$request->input('assignee_id');
					$incident->update(['assigned_to' => $assigneeId, 'assigned_by' => $user->id, 'status' => 'Assigned']);
					$assignee = User::find($assigneeId);
					IncidentUpdate::create([
						'incident_id' => $incident->id,
						'user_id' => $user->id,
						'update_text' => "Incident assigned to {$assignee->name} by {$user->name}",
						'is_internal_note' => true,
					]);
					
					// Notify assignee
					$this->notificationService->notify(
						$assigneeId,
						"You have been assigned a new incident: {$incident->incident_code} - " . e($incident->subject),
						route('modules.incidents', ['tab' => 'my_incidents']),
						'New Incident Assigned'
					);
					
					// Notify reporter that issue is being resolved
					if ($incident->reported_by_email || $incident->reported_by_phone) {
						$reporterMessage = "Your issue (Code: {$incident->incident_code}) has been assigned to {$assignee->name} and is being resolved.";
						if ($incident->reported_by_email) {
							$this->notificationService->notify(
								User::where('email', $incident->reported_by_email)->first()?->id ?? 0,
								$reporterMessage,
								null,
								'Incident Assigned - ' . $incident->incident_code
							);
						}
						if ($incident->reported_by_phone) {
							$this->notificationService->sendSMS($incident->reported_by_phone, $reporterMessage);
						}
					}
					
					return response()->json(['success' => true, 'message' => 'Incident assigned successfully.']);

				case 'submit_for_approval':
					$incidentId = (int)$request->input('incident_id');
					$resolutionText = (string)$request->input('resolution_text');
					IncidentUpdate::create([
						'incident_id' => $incidentId,
						'user_id' => $user->id,
						'update_text' => $resolutionText,
					]);
					$updated = Incident::where('id', $incidentId)
						->where('assigned_to', $user->id)
						->update(['status' => 'Pending Approval', 'resolution_details' => $resolutionText]);
					if (!$updated) abort(400, 'Could not submit for approval.');
					return response()->json(['success' => true, 'message' => 'Resolution submitted for approval.']);

				case 'approve_resolution':
					if (!$isManager) abort(403);
					$incident = Incident::where('id', (int)$request->input('incident_id'))
						->where('status', 'Pending Approval')
						->firstOrFail();
					$incident->update(['status' => 'Resolved', 'resolved_at' => now()]);
					IncidentUpdate::create([
						'incident_id' => $incident->id,
						'user_id' => $user->id,
						'update_text' => "Resolution Approved by {$user->name}. Incident is now resolved.",
						'is_internal_note' => true,
					]);
					
					// Notify reporter that issue is resolved
					if ($incident->reported_by_email || $incident->reported_by_phone) {
						$reporterMessage = "Your issue (Code: {$incident->incident_code}) has been resolved. Thank you for your patience.";
						if ($incident->reported_by_email) {
							$this->notificationService->notify(
								User::where('email', $incident->reported_by_email)->first()?->id ?? 0,
								$reporterMessage,
								null,
								'Incident Resolved - ' . $incident->incident_code
							);
						}
						if ($incident->reported_by_phone) {
							$this->notificationService->sendSMS($incident->reported_by_phone, $reporterMessage);
						}
					}
					
					return response()->json(['success' => true, 'message' => 'Resolution approved successfully.']);

				case 'add_update':
					$update = IncidentUpdate::create([
						'incident_id' => (int)$request->input('incident_id'),
						'user_id' => $user->id,
						'update_text' => (string)$request->input('update_text'),
						'is_internal_note' => $request->boolean('is_internal'),
					]);
					return response()->json(['success' => true, 'message' => 'Update added successfully.']);

				case 'change_status':
					$valid = ['New','Assigned','In Progress','Pending Approval','Resolved'];
					$new = (string)$request->input('new_status');
					if (!in_array($new, $valid, true)) abort(400, 'Invalid status.');
					if (!$isManager && $new !== 'In Progress') abort(403);
					$incidentId = (int)$request->input('incident_id');
					Incident::where('id', $incidentId)->update(['status' => $new]);
					IncidentUpdate::create([
						'incident_id' => $incidentId,
						'user_id' => $user->id,
						'update_text' => "Status changed to {$new} by {$user->name}",
						'is_internal_note' => true,
					]);
					return response()->json(['success' => true, 'message' => 'Status updated successfully.']);

				case 'bulk_assign':
					if (!$isManager) abort(403);
					$ids = array_map('intval', (array)$request->input('incident_ids', []));
					$assigneeId = (int)$request->input('assignee_id');
					if (empty($ids)) abort(400, 'No incidents selected.');
					Incident::whereIn('id', $ids)->where('status','New')
						->update(['assigned_to' => $assigneeId, 'assigned_by' => $user->id, 'status' => 'Assigned']);
					return response()->json(['success' => true, 'message' => 'Incidents assigned successfully.']);

				case 'update_incident':
					if (!$isManager) abort(403);
					$incidentId = (int)$request->input('incident_id');
					Incident::where('id', $incidentId)->update([
						'subject' => $request->string('subject'),
						'description' => $request->input('description'),
						'priority' => $request->string('priority'),
						'due_date' => $request->date('due_date'),
					]);
					return response()->json(['success' => true, 'message' => 'Incident updated successfully.']);
			}

			return response()->json(['success' => false, 'message' => 'Unknown action.'], 400);
		});
	}

	private static function cleanText(string $text): string
	{
		$text = preg_replace('/<style[^>]*>.*?<\\/style>/si', '', $text);
		$text = preg_replace('/<script[^>]*>.*?<\\/script>/si', '', $text);
		$text = preg_replace('/<br\\s*\\/?>/i', "\n", $text);
		$text = preg_replace('/<p\\s*\\/?>/i', "\n", $text);
		$text = strip_tags($text);
		$text = preg_replace('/\s+/', ' ', $text);
		return trim($text);
	}
}


