<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveNonEmcaUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:remove-non-emca {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all users that are not from EmCa (keep only @emca.tech emails and admin@ofisi.com)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all users
        $allUsers = User::all();
        
        // Filter EmCa users and admin
        $emcaUsers = $allUsers->filter(function ($user) {
            return str_ends_with($user->email, '@emca.tech') || $user->email === 'admin@ofisi.com';
        });
        
        $nonEmcaUsers = $allUsers->filter(function ($user) {
            return !str_ends_with($user->email, '@emca.tech') && $user->email !== 'admin@ofisi.com';
        });

        $this->info('=== Remove Non-EmCa Users ===');
        $this->newLine();
        
        $this->info('Users to KEEP:');
        $this->line('  - EmCa users (@emca.tech): ' . $emcaUsers->where('email', 'like', '%@emca.tech')->count());
        $this->line('  - Admin (admin@ofisi.com): ' . ($emcaUsers->where('email', 'admin@ofisi.com')->count() > 0 ? '1' : '0'));
        $this->newLine();
        
        $this->warn('Users to DELETE:');
        if ($nonEmcaUsers->count() > 0) {
            foreach ($nonEmcaUsers as $user) {
                $this->line("  - {$user->name} ({$user->email}) - EMP: {$user->employee_id}");
            }
        } else {
            $this->line('  (none)');
        }
        
        $this->newLine();
        $this->error("Total users to delete: {$nonEmcaUsers->count()}");

        if ($nonEmcaUsers->count() === 0) {
            $this->info('No non-EmCa users found. All users are from EmCa or are the admin.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Are you sure you want to delete these users? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Deleting users...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            foreach ($nonEmcaUsers as $user) {
                $user->delete();
                $deletedCount++;
                $this->info("  ✓ Deleted: {$user->name} ({$user->email})");
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $this->info("Total users deleted: {$deletedCount}");
            $this->info("Remaining users: " . User::count());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete users: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}





