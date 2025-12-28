<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assessment;
use App\Models\AssessmentProgressReport;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendAssessmentReminders extends Command
{
    protected $signature = 'assessments:send-reminders {--dry-run : Do not send, only log}';

    protected $description = 'Send SMS reminders to staff to submit assessment progress reports based on frequency';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $now = Carbon::now();
        $dryRun = (bool) $this->option('dry-run');

        $assessments = Assessment::with(['employee', 'activities'])
            ->where('status', 'approved')
            ->get();

        $remindersSent = 0;

        foreach ($assessments as $assessment) {
            $employee = $assessment->employee;
            if (!$employee) { continue; }

            foreach ($assessment->activities as $activity) {
                [$start, $end, $label] = $this->getPeriodBounds($now, $activity->reporting_frequency);

                // Any report within the current period (any status) counts as submitted
                $hasReport = AssessmentProgressReport::where('activity_id', $activity->id)
                    ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()])
                    ->exists();

                if ($hasReport) { continue; }

                $message = "Reminder: Please submit your {$activity->reporting_frequency} progress report for '{$activity->activity_name}' ({$label}).";

                if ($dryRun) {
                    $this->info("[DRY RUN] Would remind {$employee->name}: {$message}");
                } else {
                    try {
                        $this->notificationService->notify($employee->id, $message, route('modules.hr.assessments'), 'Assessment Reminder');
                        $remindersSent++;
                    } catch (\Exception $e) {
                        $this->error('Failed to send reminder: ' . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Reminders processed: {$remindersSent}");
        return Command::SUCCESS;
    }

    private function getPeriodBounds(Carbon $now, string $frequency): array
    {
        if ($frequency === 'daily') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            $label = $now->toDateString();
            return [$start, $end, $label];
        }

        if ($frequency === 'weekly') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $label = 'Week of ' . $start->toDateString();
            return [$start, $end, $label];
        }

        // monthly
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $label = $now->format('F Y');
        return [$start, $end, $label];
    }
}








