<?php

namespace App\Console\Commands;

use App\Models\EmployeeSalaryDeduction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAllDeductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deductions:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all employee salary deductions from the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deductionsCount = EmployeeSalaryDeduction::count();

        if ($deductionsCount === 0) {
            $this->info('No deductions found in the database.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info("Found deductions data:");
            $this->line("  - Total Deductions: {$deductionsCount}");
            
            if (!$this->confirm("Are you sure you want to delete all {$deductionsCount} deduction(s)? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting all deductions...");

        try {
            DB::beginTransaction();

            $deleted = EmployeeSalaryDeduction::query()->delete();
            
            DB::commit();

            $this->info("Successfully deleted {$deleted} deduction(s).");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to delete deductions: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}






