<?php

namespace Database\Seeders;

use App\Models\RackCategory;
use Illuminate\Database\Seeder;

class RackCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Human Resources', 'description' => 'Employee files, contracts, HR documents', 'prefix' => 'HR'],
            ['name' => 'Finance', 'description' => 'Financial records, invoices, receipts', 'prefix' => 'FIN'],
            ['name' => 'Legal', 'description' => 'Legal documents, contracts, agreements', 'prefix' => 'LEG'],
            ['name' => 'Administration', 'description' => 'General administrative documents', 'prefix' => 'ADM'],
            ['name' => 'Operations', 'description' => 'Operational documents and reports', 'prefix' => 'OPS'],
            ['name' => 'Sales & Marketing', 'description' => 'Sales records, marketing materials', 'prefix' => 'S&M'],
            ['name' => 'IT & Technical', 'description' => 'Technical documentation, manuals', 'prefix' => 'IT'],
            ['name' => 'General', 'description' => 'General purpose documents', 'prefix' => 'GEN'],
        ];

        foreach ($categories as $category) {
            RackCategory::create($category);
        }
    }
}








