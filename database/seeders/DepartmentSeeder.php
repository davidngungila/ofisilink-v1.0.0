<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Administration',
                'description' => 'General administration and management',
                'code' => 'ADMIN',
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Human resources and personnel management',
                'code' => 'HR',
            ],
            [
                'name' => 'Finance',
                'description' => 'Financial management and accounting',
                'code' => 'FIN',
            ],
            [
                'name' => 'Information Technology',
                'description' => 'IT services and technical support',
                'code' => 'IT',
            ],
            [
                'name' => 'Operations',
                'description' => 'Daily operations and logistics',
                'code' => 'OPS',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing and business development',
                'code' => 'MKT',
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Customer support and relations',
                'code' => 'CS',
            ],
            [
                'name' => 'Legal',
                'description' => 'Legal affairs and compliance',
                'code' => 'LEGAL',
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales and revenue generation',
                'code' => 'SALES',
            ],
            [
                'name' => 'Procurement',
                'description' => 'Purchasing and supply chain management',
                'code' => 'PROC',
            ],
            [
                'name' => 'Quality Assurance',
                'description' => 'Quality control and assurance',
                'code' => 'QA',
            ],
            [
                'name' => 'Research and Development',
                'description' => 'Research, innovation and product development',
                'code' => 'R&D',
            ],
            [
                'name' => 'Production',
                'description' => 'Production and manufacturing',
                'code' => 'PROD',
            ],
            [
                'name' => 'Warehouse',
                'description' => 'Warehouse and inventory management',
                'code' => 'WH',
            ],
            [
                'name' => 'Logistics',
                'description' => 'Logistics and distribution',
                'code' => 'LOG',
            ],
            [
                'name' => 'Security',
                'description' => 'Security and safety management',
                'code' => 'SEC',
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Facilities and equipment maintenance',
                'code' => 'MAINT',
            ],
            [
                'name' => 'Public Relations',
                'description' => 'Public relations and communications',
                'code' => 'PR',
            ],
            [
                'name' => 'Training',
                'description' => 'Training and development',
                'code' => 'TRAIN',
            ],
            [
                'name' => 'Compliance',
                'description' => 'Regulatory compliance and risk management',
                'code' => 'COMP',
            ],
            [
                'name' => 'Internal Audit',
                'description' => 'Internal auditing and controls',
                'code' => 'AUDIT',
            ],
            [
                'name' => 'Project Management',
                'description' => 'Project planning and execution',
                'code' => 'PM',
            ],
            [
                'name' => 'Business Development',
                'description' => 'Business growth and strategic partnerships',
                'code' => 'BD',
            ],
            [
                'name' => 'Facilities Management',
                'description' => 'Facilities and property management',
                'code' => 'FM',
            ],
            [
                'name' => 'Health and Safety',
                'description' => 'Occupational health and safety',
                'code' => 'HSE',
            ],
            [
                'name' => 'Data Management',
                'description' => 'Data management and analytics',
                'code' => 'DATA',
            ],
            [
                'name' => 'Customer Relations',
                'description' => 'Customer relationship management',
                'code' => 'CR',
            ],
            [
                'name' => 'Strategic Planning',
                'description' => 'Strategic planning and corporate development',
                'code' => 'SP',
            ],
            [
                'name' => 'Administrative Support',
                'description' => 'Administrative and clerical support',
                'code' => 'ADMIN-SUP',
            ],
            [
                'name' => 'Executive Office',
                'description' => 'Executive leadership and corporate governance',
                'code' => 'EXEC',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }
}