<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gl_accounts')) {
            $exists = DB::table('gl_accounts')->count();
            if ($exists == 0) {
                DB::table('gl_accounts')->insert([
                    ['code'=>'1000','name'=>'Petty Cash','category'=>'Assets','is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'5000','name'=>'Office Expenses','category'=>'Expense','is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'6000','name'=>'Payroll','category'=>'Expense','is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
                ]);
            }
        }
        if (Schema::hasTable('cash_boxes')) {
            $exists = DB::table('cash_boxes')->count();
            if ($exists == 0) {
                DB::table('cash_boxes')->insert([
                    ['name'=>'Main Cash Box','currency'=>'TZS','current_balance'=>0,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
                ]);
            }
        }
    }

    public function down(): void
    {
        // no-op (do not remove seed data on rollback)
    }
};








