<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PettyCashVoucher;
use App\Models\PettyCashVoucherLine;
use App\Models\ImprestRequest;
use App\Models\ImprestAssignment;
use App\Models\ImprestReceipt;
use Illuminate\Support\Facades\DB;

class CleanPettyCashAndImprestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning petty cash and imprest requests...');
        
        // Get counts before deletion
        $pettyCashCount = PettyCashVoucher::count();
        $pettyCashLinesCount = PettyCashVoucherLine::count();
        $imprestCount = ImprestRequest::count();
        $imprestAssignmentsCount = ImprestAssignment::count();
        $imprestReceiptsCount = ImprestReceipt::count();
        
        $this->command->info("Found:");
        $this->command->info("  - Petty Cash Vouchers: {$pettyCashCount}");
        $this->command->info("  - Petty Cash Voucher Lines: {$pettyCashLinesCount}");
        $this->command->info("  - Imprest Requests: {$imprestCount}");
        $this->command->info("  - Imprest Assignments: {$imprestAssignmentsCount}");
        $this->command->info("  - Imprest Receipts: {$imprestReceiptsCount}");
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // Delete in order to respect foreign key constraints
        // 1. Delete Imprest Receipts first (depends on ImprestAssignment)
        $this->command->info('Deleting Imprest Receipts...');
        ImprestReceipt::query()->delete();
        
        // 2. Delete Imprest Assignments (depends on ImprestRequest)
        $this->command->info('Deleting Imprest Assignments...');
        ImprestAssignment::query()->delete();
        
        // 3. Delete Imprest Requests
        $this->command->info('Deleting Imprest Requests...');
        ImprestRequest::query()->delete();
        
        // 4. Delete Petty Cash Voucher Lines (depends on PettyCashVoucher)
        $this->command->info('Deleting Petty Cash Voucher Lines...');
        PettyCashVoucherLine::query()->delete();
        
        // 5. Delete Petty Cash Vouchers
        $this->command->info('Deleting Petty Cash Vouchers...');
        PettyCashVoucher::query()->delete();
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all data!");
        $this->command->info("==========================================");
        $this->command->info("Deleted:");
        $this->command->info("  - {$pettyCashCount} Petty Cash Vouchers");
        $this->command->info("  - {$pettyCashLinesCount} Petty Cash Voucher Lines");
        $this->command->info("  - {$imprestCount} Imprest Requests");
        $this->command->info("  - {$imprestAssignmentsCount} Imprest Assignments");
        $this->command->info("  - {$imprestReceiptsCount} Imprest Receipts");
        $this->command->info("\n");
    }
}

