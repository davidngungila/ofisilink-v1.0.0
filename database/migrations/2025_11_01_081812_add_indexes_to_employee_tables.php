<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes to employees table for faster lookups
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'user_id')) {
            Schema::table('employees', function (Blueprint $table) {
                // Index for user_id lookups (most common query)
                try {
                    if (!$this->indexExists('employees', 'employees_user_id_index')) {
                        $table->index('user_id', 'employees_user_id_index');
                    }
                } catch (\Exception $e) {
                    // Index might already exist or table structure different
                }
            });
        }
        
        // Add indexes to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    // Index for department filtering
                    if (Schema::hasColumn('users', 'primary_department_id') && !$this->indexExists('users', 'users_primary_department_id_index')) {
                        $table->index('primary_department_id', 'users_primary_department_id_index');
                    }
                    
                    // Index for employee_id lookups
                    if (Schema::hasColumn('users', 'employee_id') && !$this->indexExists('users', 'users_employee_id_index')) {
                        $table->index('employee_id', 'users_employee_id_index');
                    }
                    
                    // Index for active status filtering
                    if (!$this->indexExists('users', 'users_is_active_index')) {
                        $table->index('is_active', 'users_is_active_index');
                    }
                } catch (\Exception $e) {
                    // Indexes might already exist
                }
            });
        }
        
        // Add indexes to related tables
        $relatedTables = [
            'bank_accounts' => ['user_id' => 'bank_accounts_user_id_index'],
            'employee_educations' => ['user_id' => 'employee_educations_user_id_index'],
            'employee_family' => ['user_id' => 'employee_family_user_id_index'],
            'employee_next_of_kin' => ['user_id' => 'employee_next_of_kin_user_id_index'],
            'employee_referees' => ['user_id' => 'employee_referees_user_id_index'],
        ];
        
        foreach ($relatedTables as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    foreach ($columns as $column => $indexName) {
                        try {
                            if (Schema::hasColumn($tableName, $column) && !$this->indexExists($tableName, $indexName)) {
                                $table->index($column, $indexName);
                            }
                        } catch (\Exception $e) {
                            // Index might already exist
                        }
                    }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'user_id')) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['user_id', 'employment_status']);
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'primary_department_id')) {
                $table->dropIndex(['primary_department_id']);
            }
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropIndex(['employee_id']);
            }
            $table->dropIndex(['is_active']);
        });
    }
    
    private function getIndexes($tableName)
    {
        try {
            $connection = Schema::getConnection();
            $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
            $tableIndexes = $doctrineSchemaManager->listTableIndexes($tableName);
            return array_map(function($index) {
                return $index->getName();
            }, $tableIndexes);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function indexExists($tableName, $indexName)
    {
        try {
            $indexes = $this->getIndexes($tableName);
            return in_array($indexName, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
