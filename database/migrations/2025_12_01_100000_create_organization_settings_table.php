<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            
            // Company Information
            $table->string('company_name')->default('OfisiLink');
            $table->string('company_registration_number')->nullable();
            $table->string('company_tax_id')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_country')->default('Tanzania');
            $table->string('company_postal_code')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_favicon')->nullable();
            
            // Financial Settings
            $table->string('currency', 3)->default('TZS');
            $table->string('currency_symbol', 10)->default('TSh');
            $table->string('currency_position')->default('prefix'); // prefix, suffix
            $table->integer('decimal_places')->default(2);
            $table->string('number_format')->default('1,234.56'); // 1,234.56 or 1.234,56
            
            // Financial Year Settings (Critical for accounting)
            $table->string('financial_year_start_month')->default('07'); // July
            $table->string('financial_year_start_day')->default('01');
            $table->integer('current_financial_year')->default(date('Y'));
            $table->date('financial_year_start_date')->nullable();
            $table->date('financial_year_end_date')->nullable();
            $table->boolean('financial_year_locked')->default(false); // Prevent changes during active year
            $table->json('financial_year_history')->nullable(); // Track historical FY changes
            
            // Date & Time Settings
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('date_format')->default('Y-m-d'); // Y-m-d, d/m/Y, m/d/Y
            $table->string('time_format')->default('H:i:s'); // 24h or 12h
            $table->string('week_start_day')->default('monday'); // monday, sunday
            $table->string('first_day_of_month')->default('01'); // For reporting periods
            
            // Regional Settings
            $table->string('locale')->default('en');
            $table->string('country_code', 2)->default('TZ');
            $table->string('language')->default('en');
            
            // System Settings
            $table->integer('max_file_size')->default(10); // MB
            $table->string('allowed_file_types')->default('pdf,jpg,jpeg,png,doc,docx,xls,xlsx');
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('sms_notifications_enabled')->default(true);
            $table->boolean('push_notifications_enabled')->default(true);
            
            // Business Hours
            $table->time('business_hours_start')->default('08:00:00');
            $table->time('business_hours_end')->default('17:00:00');
            $table->json('business_days')->nullable();
            
            // Payroll Settings
            $table->integer('payroll_period_days')->default(30); // Monthly = 30
            $table->integer('payroll_processing_day')->default(25); // Day of month
            $table->string('payroll_currency')->default('TZS');
            
            // Leave Settings
            $table->integer('default_annual_leave_days')->default(21);
            $table->integer('default_sick_leave_days')->default(30);
            $table->integer('max_consecutive_leave_days')->default(14);
            
            // Tax Settings
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->decimal('income_tax_rate', 5, 2)->nullable();
            $table->boolean('tax_inclusive_pricing')->default(false);
            
            // Advanced Settings
            $table->json('custom_fields')->nullable();
            $table->json('integration_settings')->nullable(); // API keys, third-party integrations
            $table->text('internal_notes')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            
            // Audit
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Ensure single row (singleton pattern)
            $table->unique('id');
        });
        
        // Insert default settings
        DB::table('organization_settings')->insert([
            'company_name' => config('app.name', 'OfisiLink'),
            'company_email' => config('mail.from.address', ''),
            'currency' => 'TZS',
            'currency_symbol' => 'TSh',
            'timezone' => config('app.timezone', 'Africa/Dar_es_Salaam'),
            'current_financial_year' => date('Y'),
            'financial_year_start_month' => '07',
            'financial_year_start_day' => '01',
            'business_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};

