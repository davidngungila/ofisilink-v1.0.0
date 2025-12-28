<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:clean {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all assets and asset categories from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Count all records
        $assetsCount = DB::table('assets')->count();
        $categoriesCount = DB::table('asset_categories')->count();
        $totalCount = $assetsCount + $categoriesCount;

        if ($totalCount === 0) {
            $this->info('No assets or asset categories found in the database.');
            return Command::SUCCESS;
        }

        // Display summary
        $this->info('=== Assets Cleanup ===');
        $this->line("  assets: {$assetsCount} records");
        $this->line("  asset_categories: {$categoriesCount} records");
        $this->newLine();
        $this->info("Total records to delete: {$totalCount}");

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all assets and asset categories? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Starting cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $deletedAssets = 0;
            $deletedCategories = 0;

            // Delete all assets first (including soft deleted)
            if ($assetsCount > 0) {
                $deletedAssets = DB::table('assets')->delete();
                $this->info("  ✓ assets: {$deletedAssets} records deleted");
            }

            // Delete all asset categories
            if ($categoriesCount > 0) {
                $deletedCategories = DB::table('asset_categories')->delete();
                $this->info("  ✓ asset_categories: {$deletedCategories} records deleted");
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $totalDeleted = $deletedAssets + $deletedCategories;
            $this->info("Total records deleted: {$totalDeleted}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to clean assets: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

