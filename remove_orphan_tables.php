<?php

/**
 * Script to identify and remove database tables that don't have corresponding migration files
 */

// Get all migration files
$migrationsPath = __DIR__ . '/database/migrations';
$migrationFiles = glob($migrationsPath . '/*.php');

// Tables that exist in migrations (from Schema::create)
$migratedTables = [];

// Laravel default tables (should be kept)
$defaultTables = [
    'users',
    'password_reset_tokens',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'migrations',
    'sessions',
];

// Parse each migration file
foreach ($migrationFiles as $file) {
    $content = file_get_contents($file);
    
    // Find all Schema::create() calls (including those with Schema::hasTable checks)
    preg_match_all("/Schema::create\(['\"]([^'\"]+)['\"]/", $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $table) {
            $migratedTables[] = $table;
        }
    }
    
    // Also check for Schema::dropIfExists to find table names
    preg_match_all("/Schema::dropIfExists\(['\"]([^'\"]+)['\"]/", $content, $dropMatches);
    if (!empty($dropMatches[1])) {
        foreach ($dropMatches[1] as $table) {
            $migratedTables[] = $table;
        }
    }
}

// Remove duplicates
$migratedTables = array_unique($migratedTables);

// All tables from the user's list (extracted from the message)
$allTables = [
    'accounting_audit_logs',
    'accounts_adminbiodata',
    'accounts_usernotification',
    'acc_acccombination',
    'acc_accgroups',
    'acc_accholiday',
    'acc_accprivilege',
    'acc_accterminal',
    'acc_acctimezone',
    'activity_assignments',
    'activity_logs',
    'activity_reports',
    'application_documents',
    'application_evaluations',
    'application_history',
    'assessments',
    'assessment_activities',
    'assessment_progress_reports',
    'assets',
    'asset_assignments',
    'asset_categories',
    'asset_issues',
    'asset_maintenance',
    'attendances',
    'attendance_devices',
    'attendance_locations',
    'attendance_policies',
    'attparam',
    'att_attcalclog',
    'att_attcode',
    'att_attemployee',
    'att_attgroup',
    'att_attpolicy',
    'att_attreportsetting',
    'att_attrule',
    'att_attschedule',
    'att_attshift',
    'att_breaktime',
    'att_calculatelastdate',
    'att_calculatetask',
    'att_changeschedule',
    'att_custom_report',
    'att_departmentpolicy',
    'att_departmentschedule',
    'att_deptattrule',
    'att_grouppolicy',
    'att_groupschedule',
    'att_holiday',
    'att_leave',
    'att_leavegroup',
    'att_leavegroupdetail',
    'att_leaveyearbalance',
    'att_manuallog',
    'att_overtime',
    'att_overtimepolicy',
    'att_paycode',
    'att_payloadattcode',
    'att_payloadbase',
    'att_payloadbreak',
    'att_payloadeffectpunch',
    'att_payloadexception',
    'att_payloadmulpunchset',
    'att_payloadovertime',
    'att_payloadparing',
    'att_payloadpaycode',
    'att_payloadpunch',
    'att_payloadtimecard',
    'att_reportparam',
    'att_reporttemplate',
    'att_shiftdetail',
    'att_temporaryschedule',
    'att_tempschedule',
    'att_timeinterval',
    'att_timeinterval_break_time',
    'att_training',
    'att_webpunch',
    'authtoken_token',
    'auth_group',
    'auth_group_permissions',
    'auth_permission',
    'auth_user',
    'auth_user_auth_area',
    'auth_user_auth_dept',
    'auth_user_groups',
    'auth_user_profile',
    'auth_user_user_permissions',
    'bank_accounts',
    'base_adminlog',
    'base_apiendpoint',
    'base_apipermission',
    'base_attparamdepts',
    'base_autoattexporttask',
    'base_autoexporttask',
    'base_autoimporttask',
    'base_bookmark',
    'base_dbbackuplog',
    'base_emailtemplate',
    'base_eventalertsetting',
    'base_fixedexporttask',
    'base_linenotifyforemployee',
    'base_linenotifysetting',
    'base_messengersentlog',
    'base_securitypolicy',
    'base_sendemail',
    'base_sftpsetting',
    'base_sysparam',
    'base_sysparamdept',
    'base_systemlog',
    'base_systemsetting',
    'base_whatsapplog',
    'base_zoomsetting',
    'bills',
    'bill_items',
    'bill_payments',
    'budgets',
    'budget_items',
    'cache',
    'cache_locks',
    'cash_boxes',
    'chart_of_accounts',
    'credit_memos',
    'customers',
    'database_backups',
    'departments',
    'django_admin_log',
    'django_celery_beat_clockedschedule',
    'django_celery_beat_crontabschedule',
    'django_celery_beat_intervalschedule',
    'django_celery_beat_periodictask',
    'django_celery_beat_periodictasks',
    'django_celery_beat_solarschedule',
    'django_content_type',
    'django_migrations',
    'django_session',
    'education',
    'emergency_contacts',
    'employees',
    'employee_allowances',
    'employee_bonuses',
    'employee_documents',
    'employee_educations',
    'employee_family',
    'employee_next_of_kin',
    'employee_overtimes',
    'employee_performance',
    'employee_referees',
    'employee_salary_deductions',
    'employee_skills',
    'employee_training',
    'employee_work_history',
    'ep_epsetup',
    'ep_eptransaction',
    'failed_jobs',
    'family_members',
    'files',
    'file_access_requests',
    'file_activities',
    'file_folders',
    'file_user_assignments',
    'fixed_assets',
    'fixed_asset_categories',
    'fixed_asset_depreciations',
    'fixed_asset_disposals',
    'fixed_asset_maintenances',
    'general_ledger',
    'gl_accounts',
    'iclock_biodata',
    'iclock_biophoto',
    'iclock_devicemoduleconfig',
    'iclock_errorcommandlog',
    'iclock_privatemessage',
    'iclock_publicmessage',
    'iclock_shortmessage',
    'iclock_terminal',
    'iclock_terminalcommand',
    'iclock_terminalcommandlog',
    'iclock_terminalemployee',
    'iclock_terminallog',
    'iclock_terminalparameter',
    'iclock_terminaluploadlog',
    'iclock_terminalworkcode',
    'iclock_transaction',
    'iclock_transactionproofcmd',
    'imprest_approval_history',
    'imprest_assignments',
    'imprest_receipts',
    'imprest_requests',
    'incidents',
    'incident_email_config',
    'incident_inbox',
    'incident_updates',
    'interview_schedules',
    'invoices',
    'invoice_items',
    'invoice_payments',
    'jobs',
    'job_applications',
    'job_batches',
    'journal_entries',
    'journal_entry_lines',
    'leave_balances',
    'leave_dependents',
    'leave_documents',
    'leave_recommendations',
    'leave_requests',
    'leave_types',
    'main_responsibilities',
    'main_tasks',
    'meeting_meetingentity',
    'meeting_meetingentity_attender',
    'meeting_meetingmanuallog',
    'meeting_meetingpayloadbase',
    'meeting_meetingroom',
    'meeting_meetingroomdevice',
    'meeting_meetingtransaction',
    'migrations',
    'mobile_announcement',
    'mobile_appactionlog',
    'mobile_applist',
    'mobile_appnotification',
    'mobile_gpsfordepartment',
    'mobile_gpsfordepartment_location',
    'mobile_gpsforemployee',
    'mobile_gpsforemployee_location',
    'mobile_gpslocation',
    'mobile_mobileapirequestlog',
    'next_of_kin',
    'notifications',
    'notification_providers',
    'organization_settings',
    'otp_codes',
    'password_reset_tokens',
    'payrolls',
    'payroll_deductionformula',
    'payroll_empexpenseexemption',
    'payroll_emploan',
    'payroll_emppayrollprofile',
    'payroll_exceptionformula',
    'payroll_extradeduction',
    'payroll_extraincrease',
    'payroll_increasementformula',
    'payroll_items',
    'payroll_leaveformula',
    'payroll_overtimeformula',
    'payroll_payrollpayload',
    'payroll_payrollpayloadexpenseexemption',
    'payroll_payrollpayloadpaycode',
    'payroll_reimbursement',
    'payroll_salaryadvance',
    'payroll_salarystructure',
    'petty_cash_vouchers',
    'petty_cash_voucher_lines',
    'permissions',
    'permission_requests',
    'positions',
    'rack_activities',
    'rack_categories',
    'rack_file_requests',
    'rack_files',
    'rack_folders',
    'recruitment_jobs',
    'roles',
    'role_permissions',
    'sessions',
    'sick_sheets',
    'system_settings',
    'task_categories',
    'tax_settings',
    'user_departments',
    'user_roles',
    'users',
    'vendors',
    'work_schedules',
];

// Find tables that don't have migrations
$orphanTables = [];
foreach ($allTables as $table) {
    // Skip Laravel default tables
    if (in_array($table, $defaultTables)) {
        continue;
    }
    
    // Check if table has a migration
    if (!in_array($table, $migratedTables)) {
        $orphanTables[] = $table;
    }
}

// Generate SQL to drop orphan tables
$sql = "-- Drop tables that don't have corresponding migration files\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($orphanTables as $table) {
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
}

$sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

// Save to file
file_put_contents(__DIR__ . '/drop_orphan_tables.sql', $sql);

// Also create a PHP array for reference
$phpArray = "<?php\n\n";
$phpArray .= "// Tables to be dropped (no migrations found)\n";
$phpArray .= "return [\n";
foreach ($orphanTables as $table) {
    $phpArray .= "    '{$table}',\n";
}
$phpArray .= "];\n";

file_put_contents(__DIR__ . '/orphan_tables.php', $phpArray);

// Output results
echo "Migration Analysis Complete!\n\n";
echo "Total tables in database: " . count($allTables) . "\n";
echo "Tables with migrations: " . count($migratedTables) . "\n";
echo "Orphan tables (no migrations): " . count($orphanTables) . "\n\n";
echo "Files generated:\n";
echo "  - drop_orphan_tables.sql (SQL to drop orphan tables)\n";
echo "  - orphan_tables.php (PHP array of orphan tables)\n\n";
echo "Orphan tables:\n";
foreach ($orphanTables as $table) {
    echo "  - {$table}\n";
}

