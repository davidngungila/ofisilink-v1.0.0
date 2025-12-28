<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\GeneralLedger;

class CleanPayrollData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:clean-all {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all payroll and deduction data from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  WARNING: This will delete ALL payroll and deduction data. This action cannot be undone. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            if (!$this->confirm('Are you absolutely certain? Type "yes" to confirm:', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting payroll data cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Count records before deletion
            $payrollCount = Payroll::count();
            $payrollItemCount = PayrollItem::count();
            $deductionCount = DB::table('employee_salary_deductions')->count();
            $glPayrollCount = GeneralLedger::where('source', 'Payroll')
                ->orWhere('reference_type', 'Payroll')
                ->orWhere('description', 'like', '%payroll%')
                ->orWhere('description', 'like', '%salary%')
                ->count();

            $this->info("Found:");
            $this->line("  - Payrolls: {$payrollCount}");
            $this->line("  - Payroll Items: {$payrollItemCount}");
            $this->line("  - Employee Salary Deductions: {$deductionCount}");
            $this->line("  - General Ledger Entries (Payroll-related): {$glPayrollCount}");
            $this->newLine();

            // Delete General Ledger entries related to payroll first (to avoid foreign key issues)
            $this->info('Deleting General Ledger entries related to payroll...');
            $deletedGL = GeneralLedger::where('source', 'Payroll')
                ->orWhere('reference_type', 'Payroll')
                ->orWhere('description', 'like', '%payroll%')
                ->orWhere('description', 'like', '%salary%')
                ->delete();
            $this->line("  ✓ Deleted {$deletedGL} General Ledger entries");

            // Delete Payroll Items (will cascade from payrolls, but doing explicitly for clarity)
            $this->info('Deleting Payroll Items...');
            $deletedItems = PayrollItem::query()->delete();
            $this->line("  ✓ Deleted {$deletedItems} Payroll Items");

            // Delete Payrolls
            $this->info('Deleting Payrolls...');
            $deletedPayrolls = Payroll::query()->delete();
            $this->line("  ✓ Deleted {$deletedPayrolls} Payrolls");

            // Delete Employee Salary Deductions
            $this->info('Deleting Employee Salary Deductions...');
            $deletedDeductions = DB::table('employee_salary_deductions')->delete();
            $this->line("  ✓ Deleted {$deletedDeductions} Employee Salary Deductions");

            DB::commit();

            $this->newLine();
            $this->info('✅ Payroll data cleanup completed successfully!');
            $this->line("Summary:");
            $this->line("  - Deleted {$deletedPayrolls} Payrolls");
            $this->line("  - Deleted {$deletedItems} Payroll Items");
            $this->line("  - Deleted {$deletedDeductions} Employee Salary Deductions");
            $this->line("  - Deleted {$deletedGL} General Ledger entries");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}







