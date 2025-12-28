<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KeepOnlyEmcaGeneralDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'departments:keep-only-emca-general {--force : Force operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all departments except EMCA GENERAL DP (code: DP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Department Cleanup - Keep Only EMCA GENERAL DP ===');
        $this->newLine();

        // Get or create EMCA GENERAL DP department
        $emcaDept = Department::updateOrCreate(
            ['code' => 'DP'],
            [
                'name' => 'EMCA GENERAL DP',
                'description' => 'GENERAL DEPARTMENT',
                'is_active' => true,
            ]
        );

        $this->info('Target Department:');
        $this->line("  Code: {$emcaDept->code}");
        $this->line("  Name: {$emcaDept->name}");
        $this->line("  Description: {$emcaDept->description}");
        $this->line("  ID: {$emcaDept->id}");
        $this->newLine();

        // Get all other departments
        $otherDepartments = Department::where('code', '!=', 'DP')->get();

        $this->warn('Departments to DELETE:');
        if ($otherDepartments->count() > 0) {
            foreach ($otherDepartments as $dept) {
                $userCount = User::where('primary_department_id', $dept->id)->count();
                $this->line("  - {$dept->code}: {$dept->name} (ID: {$dept->id}) - {$userCount} users");
            }
        } else {
            $this->line('  (none)');
        }

        $this->newLine();
        $this->error("Total departments to delete: {$otherDepartments->count()}");

        if ($otherDepartments->count() === 0) {
            $this->info('No other departments found. Only EMCA GENERAL DP exists.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Are you sure you want to delete these departments and update all users to EMCA GENERAL DP? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Starting cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Update all users to use EMCA GENERAL DP
            $usersUpdated = User::where(function($query) use ($emcaDept) {
                $query->where('primary_department_id', '!=', $emcaDept->id)
                      ->orWhereNull('primary_department_id');
            })->update(['primary_department_id' => $emcaDept->id]);

            $this->info("  ✓ Updated {$usersUpdated} users to EMCA GENERAL DP");

            // Get all users
            $allUsers = User::all();
            
            // Delete all user_departments entries for other departments
            $pivotDeleted = DB::table('user_departments')
                ->where('department_id', '!=', $emcaDept->id)
                ->delete();

            $this->info("  ✓ Deleted {$pivotDeleted} old user-department relationships");

            // Add all users to EMCA GENERAL DP if they don't already have it
            $pivotAdded = 0;
            foreach ($allUsers as $user) {
                $exists = DB::table('user_departments')
                    ->where('user_id', $user->id)
                    ->where('department_id', $emcaDept->id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('user_departments')->insert([
                        'user_id' => $user->id,
                        'department_id' => $emcaDept->id,
                        'is_primary' => true,
                        'is_active' => true,
                        'joined_at' => $user->hire_date ?? now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pivotAdded++;
                }
            }

            $this->info("  ✓ Added {$pivotAdded} users to EMCA GENERAL DP");

            // Delete other departments
            $deletedCount = 0;
            foreach ($otherDepartments as $dept) {
                $dept->delete();
                $deletedCount++;
                $this->info("  ✓ Deleted: {$dept->code} - {$dept->name}");
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $this->info("Total departments deleted: {$deletedCount}");
            $this->info("Remaining departments: " . Department::count());
            $this->info("Users in EMCA GENERAL DP: " . User::where('primary_department_id', $emcaDept->id)->count());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to cleanup departments: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}

