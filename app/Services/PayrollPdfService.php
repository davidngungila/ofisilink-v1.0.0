<?php

namespace App\Services;

use App\Models\PayrollItem;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayrollPdfService
{
    /**
     * Generate payslip PDF for individual employee - matching the provided format
     */
    public function generatePayslip(PayrollItem $payrollItem)
    {
        $payroll = $payrollItem->payroll;
        $employee = $payrollItem->employee;
        $department = $employee->primaryDepartment ?? null;
        
        // Get employee record separately if needed
        $employeeRecord = \App\Models\Employee::where('user_id', $payrollItem->employee_id)->first();

        // Get company settings
        $companySettings = $this->getCompanySettings();
        
        // Get bank details - ensure all values are strings
        $bankDetailsRaw = $this->getBankDetails($payrollItem->employee_id);
        $bankDetails = [
            'bank_name' => is_array($bankDetailsRaw['bank_name'] ?? null) ? 'Not Provided' : (string)($bankDetailsRaw['bank_name'] ?? 'Not Provided'),
            'account_name' => is_array($bankDetailsRaw['account_name'] ?? null) ? 'Not Provided' : (string)($bankDetailsRaw['account_name'] ?? 'Not Provided'),
            'account_number' => is_array($bankDetailsRaw['account_number'] ?? null) ? '' : (string)($bankDetailsRaw['account_number'] ?? ''),
            'branch_name' => is_array($bankDetailsRaw['branch_name'] ?? null) ? '' : (string)($bankDetailsRaw['branch_name'] ?? ''),
            'swift_code' => is_array($bankDetailsRaw['swift_code'] ?? null) ? '' : (string)($bankDetailsRaw['swift_code'] ?? ''),
        ];
        
        // Get fixed deductions
        $fixedDeductions = $this->getFixedDeductions($payrollItem->employee_id);

        // Calculate totals
        $basic = (float)($payrollItem->basic_salary ?? 0);
        $overtime = (float)($payrollItem->overtime_amount ?? 0);
        $bonus = (float)($payrollItem->bonus_amount ?? 0);
        $allowance = (float)($payrollItem->allowance_amount ?? 0);
        $grossSalary = $basic + $overtime + $bonus + $allowance;

        $nssf = (float)($payrollItem->nssf_amount ?? 0);
        $paye = (float)($payrollItem->paye_amount ?? 0);
        $nhif = (float)($payrollItem->nhif_amount ?? 0);
        $heslb = (float)($payrollItem->heslb_amount ?? 0);
        $wcf = (float)($payrollItem->wcf_amount ?? 0);
        $otherDeductions = (float)($payrollItem->deduction_amount ?? 0);
        $fixedDeductionsTotal = array_sum(array_column($fixedDeductions, 'amount'));
        
        $totalDeductions = $nssf + $paye + $nhif + $heslb + $wcf + $otherDeductions + $fixedDeductionsTotal;
        $netSalary = (float)($payrollItem->net_salary ?? 0);
        
        // Recalculate net if needed
        $calculatedNet = $grossSalary - $totalDeductions;
        if (abs($calculatedNet - $netSalary) > 1) {
            $netSalary = $calculatedNet;
        }

        // Get logo
        $logoPath = $companySettings['logo_path'] ?? null;
        $logoSrc = $this->getLogoBase64($logoPath);
        $mainColor = '#940000'; // Red color as in the example

        // Get employee ID - ensure it's a string
        // employee_id might be a relationship or array, so we need to get the actual value safely
        $empNo = '';
        
        // First try to get from Employee model (most reliable) - use employee_number
        if ($employeeRecord) {
            $empNo = (string)($employeeRecord->employee_number ?? $employeeRecord->id ?? '');
        }
        
        // If still empty, try from User model's employee relationship
        if (empty($empNo) && $employee && $employee->employee) {
            $empNo = (string)($employee->employee->employee_number ?? $employee->employee->id ?? '');
        }
        
        // If still empty, try User model's employee_id attribute (but be careful - might be relationship or array)
        if (empty($empNo) && $employee) {
            // Use getAttributeValue to get raw value, not relationship
            $userEmpId = $employee->getAttributeValue('employee_id');
            if ($userEmpId !== null && !is_array($userEmpId) && !is_object($userEmpId)) {
                $empNo = (string)$userEmpId;
            }
        }
        
        // Final fallback to User ID
        if (empty($empNo) && $employee) {
            $empNo = (string)($employee->id ?? '');
        }
        
        // Ensure it's not empty and is a valid string
        if (empty($empNo) || is_array($empNo) || is_object($empNo)) {
            $empNo = 'N/A';
        } else {
            $empNo = (string)$empNo;
        }
        
        // Safely get employee name - ensure all parts are strings
        $employeeName = '';
        if ($employee) {
            if (isset($employee->name) && !is_array($employee->name) && !is_object($employee->name)) {
                $employeeName = (string)$employee->name;
            } else {
                // Build name from first_name and last_name
                $firstName = isset($employee->first_name) && !is_array($employee->first_name) && !is_object($employee->first_name) 
                    ? (string)$employee->first_name : '';
                $lastName = isset($employee->last_name) && !is_array($employee->last_name) && !is_object($employee->last_name) 
                    ? (string)$employee->last_name : '';
                $employeeName = trim($firstName . ' ' . $lastName);
            }
        }
        if (empty($employeeName)) {
            $employeeName = 'N/A';
        }
        
        // Safely get other employee fields
        $firstName = isset($employee->first_name) && !is_array($employee->first_name) && !is_object($employee->first_name) 
            ? (string)$employee->first_name : '';
        $lastName = isset($employee->last_name) && !is_array($employee->last_name) && !is_object($employee->last_name) 
            ? (string)$employee->last_name : '';
        
        // Safely get department name
        $deptName = 'N/A';
        if ($department && isset($department->name) && !is_array($department->name) && !is_object($department->name)) {
            $deptName = (string)$department->name;
        }
        
        // Safely get position and employment type
        $position = 'N/A';
        if ($employeeRecord && isset($employeeRecord->position) && !is_array($employeeRecord->position) && !is_object($employeeRecord->position)) {
            $position = (string)$employeeRecord->position;
        }
        
        $employmentType = 'N/A';
        if ($employeeRecord && isset($employeeRecord->employment_type) && !is_array($employeeRecord->employment_type) && !is_object($employeeRecord->employment_type)) {
            $employmentType = (string)$employeeRecord->employment_type;
        }
        
        // Safely get pay period
        $payPeriodStr = '';
        if ($payroll && isset($payroll->pay_period)) {
            if (is_array($payroll->pay_period)) {
                \Log::warning('Pay period is an array', ['pay_period' => $payroll->pay_period]);
                $payPeriodStr = date('Y-m');
            } elseif (is_object($payroll->pay_period)) {
                $payPeriodStr = (string)$payroll->pay_period;
            } else {
                $payPeriodStr = (string)$payroll->pay_period;
            }
        }
        
        // Ensure pay period is valid format and is a string (not array)
        if (is_array($payPeriodStr)) {
            \Log::warning('Pay period is still an array after processing', ['pay_period' => $payPeriodStr]);
            $payPeriodStr = date('Y-m');
        } elseif (empty($payPeriodStr) || !is_string($payPeriodStr) || !preg_match('/^\d{4}-\d{2}$/', $payPeriodStr)) {
            $payPeriodStr = date('Y-m');
        }
        
        // Prepare payslip data - ensure all values are strings/numbers, not arrays
        $payslipData = [
            'full_name' => $employeeName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'emp_no' => is_array($empNo) ? 'N/A' : (string)$empNo,
            'department_name' => $deptName,
            'position' => $position,
            'employment_type' => $employmentType,
            'pay_period' => is_array($payPeriodStr) ? date('Y-m') : (string)$payPeriodStr,
            'pay_period_name' => $payPeriodStr ? (is_array($payPeriodStr) ? date('F Y') : date('F Y', strtotime($payPeriodStr . '-01'))) : '',
            'pay_date' => $this->safeFormatDate($payroll->pay_date ?? null),
            'basic_salary' => (float)$basic,
            'overtime_amount' => (float)$overtime,
            'bonus_amount' => (float)$bonus,
            'allowance_amount' => (float)$allowance,
            'nssf_amount' => (float)$nssf,
            'paye_amount' => (float)$paye,
            'nhif_amount' => (float)$nhif,
            'heslb_amount' => (float)$heslb,
            'wcf_amount' => (float)$wcf,
            'deduction_amount' => (float)$otherDeductions,
            'net_salary' => (float)$netSalary,
        ];

        // Final sanitization - ensure all data is properly formatted
        $sanitizedPayslipData = [];
        foreach ($payslipData as $key => $value) {
            if (is_array($value)) {
                // If it's an array, convert to string representation or use default
                \Log::warning("Payslip data array detected for key: {$key}", [
                    'value' => $value,
                    'key' => $key,
                    'value_type' => gettype($value),
                    'value_count' => is_array($value) ? count($value) : 'N/A'
                ]);
                // Set appropriate default based on key type
                if (strpos($key, '_amount') !== false || strpos($key, 'salary') !== false || $key === 'net_salary') {
                    $sanitizedPayslipData[$key] = 0.0;
                } else {
                    $sanitizedPayslipData[$key] = 'N/A';
                }
            } elseif (is_object($value) && !($value instanceof \DateTime) && !($value instanceof \Carbon\Carbon)) {
                // If it's an object (except DateTime/Carbon), convert to string
                try {
                    $sanitizedPayslipData[$key] = (string)$value;
                } catch (\Throwable $objError) {
                    \Log::warning("Could not convert object to string for key: {$key}", [
                        'class' => get_class($value),
                        'error' => $objError->getMessage()
                    ]);
                    $sanitizedPayslipData[$key] = 'N/A';
                }
            } else {
                $sanitizedPayslipData[$key] = $value;
            }
        }
        
        // Sanitize fixed deductions
        $sanitizedFixedDeductions = [];
        foreach ($fixedDeductions as $deduction) {
            $sanitized = [];
            foreach ($deduction as $key => $value) {
                if (is_array($value)) {
                    $sanitized[$key] = $key === 'amount' ? 0 : '';
                    \Log::warning("Fixed deduction array detected for key: {$key}", ['value' => $value]);
                } elseif (is_object($value)) {
                    $sanitized[$key] = (string)$value;
                } else {
                    $sanitized[$key] = $value;
                }
            }
            $sanitizedFixedDeductions[] = $sanitized;
        }
        
        // Ensure all data passed to view is safe
        $safeLogoSrc = is_array($logoSrc) ? null : ($logoSrc ?? null);
        $safeMainColor = is_array($mainColor) ? '#940000' : (string)($mainColor ?? '#940000');
        $safeGenerationDate = now()->setTimezone('Africa/Dar_es_Salaam')->format('F j, Y \a\t g:i A');
        if (is_array($safeGenerationDate)) {
            $safeGenerationDate = date('F j, Y \a\t g:i A');
        }
        
        $data = [
            'payslip' => $sanitizedPayslipData,
            'company_settings' => $companySettings,
            'bank_details' => $bankDetails,
            'fixed_deductions' => $sanitizedFixedDeductions,
            'logo_src' => $safeLogoSrc,
            'main_color' => $safeMainColor,
            'generation_date' => $safeGenerationDate,
        ];

        try {
            // Additional validation before rendering
            $this->validatePayslipData($sanitizedPayslipData, $sanitizedFixedDeductions, $bankDetails, $companySettings);
            
            // Final check of all data before passing to view - ensure no arrays where strings expected
            $this->ensureAllDataIsSafe($data);
            
            // Deep validation - check every single value recursively
            $this->deepValidateData($data, 'root');
            
            // Comprehensive data validation - check each payslip field individually
            foreach ($data['payslip'] ?? [] as $key => $value) {
                if (is_array($value)) {
                    \Log::error("CRITICAL: Payslip field '{$key}' is still an array after sanitization!", [
                        'key' => $key,
                        'value' => $value,
                        'value_type' => gettype($value)
                    ]);
                    // Force it to a safe value
                    if (strpos($key, '_amount') !== false || strpos($key, 'salary') !== false) {
                        $data['payslip'][$key] = 0.0;
                    } else {
                        $data['payslip'][$key] = 'N/A';
                    }
                }
            }
            
            // Log data structure for debugging
            \Log::info('Rendering payslip PDF', [
                'payslip_keys' => array_keys($data['payslip'] ?? []),
                'has_fixed_deductions' => !empty($data['fixed_deductions']),
                'fixed_deductions_count' => count($data['fixed_deductions'] ?? []),
                'payslip_sample' => array_slice($data['payslip'] ?? [], 0, 3, true)
            ]);
            
            // Use output buffering to catch any errors during rendering
            ob_start();
            $html = null;
            try {
                $html = view('modules.hr.pdf.payslip', $data)->render();
            } catch (\Throwable $renderError) {
                $output = ob_get_clean();
                \Log::error('View rendering threw exception', [
                    'error' => $renderError->getMessage(),
                    'output_buffer' => substr($output, 0, 500)
                ]);
                throw $renderError;
            }
            $output = ob_get_clean();
            
            // If there's output buffer content, check for arrays
            if (!empty($output)) {
                if (stripos($output, 'Array') !== false) {
                    \Log::error('Output buffer contains array data!', [
                        'output' => substr($output, 0, 1000),
                        'output_length' => strlen($output)
                    ]);
                    // Clean the output
                    $output = preg_replace('/Array\s*\([^)]*\)/i', '[Invalid Data]', $output);
                    $output = preg_replace('/Array\s*\(/i', '[Invalid Data]', $output);
                }
                $html = $output . ($html ?? '');
            }
            
            // Validate HTML output is a string
            if (!is_string($html)) {
                \Log::error('View rendered non-string HTML', [
                    'type' => gettype($html)
                ]);
                throw new \Exception('View rendering returned invalid HTML');
            }
            
            // Final check - ensure HTML doesn't contain array outputs
            if (stripos($html, 'Array') !== false && preg_match('/Array\s*\(/i', $html)) {
                // Try to find and log where the array is
                $matches = [];
                // Use numeric value 256 for PREG_OFFSET_CAPTURE (return byte offsets)
                preg_match_all('/Array\s*\([^)]*\)/i', $html, $matches, 256);
                \Log::error('HTML still contains array outputs after cleaning', [
                    'matches_count' => count($matches[0] ?? []),
                    'first_match' => $matches[0][0] ?? null
                ]);
            }
        } catch (\Throwable $e) {
            // Safely get error message to avoid array-to-string conversion
            $errorMsg = 'An error occurred while rendering the payslip template';
            
            try {
                $originalMessage = $e->getMessage();
                // Use safeToString to ensure it's always a string
                $errorMsg = $this->safeToString($originalMessage, 'An error occurred while rendering the payslip template');
            } catch (\Throwable $t) {
                \Log::error('Failed to extract exception message in view rendering', [
                    'original_exception' => get_class($e)
                ]);
            }
            
            try {
                // Get full trace to identify the exact location
                $trace = '';
                try {
                    $trace = $e->getTraceAsString();
                } catch (\Throwable $traceError) {
                    $trace = 'Could not get trace';
                }
                
                \Log::error('Error rendering payslip view', [
                    'error' => $errorMsg,
                    'exception_class' => get_class($e),
                    'payslip_data_keys' => array_keys($sanitizedPayslipData),
                    'file' => method_exists($e, 'getFile') ? $e->getFile() : 'unknown',
                    'line' => method_exists($e, 'getLine') ? $e->getLine() : 0,
                    'trace' => $trace,
                    'payslip_sample' => array_slice($sanitizedPayslipData, 0, 5, true) // First 5 items for debugging
                ]);
            } catch (\Throwable $t) {
                \Log::error('Error rendering payslip view - logging failed', [
                    'original_error' => $errorMsg,
                    'logging_error' => $t->getMessage()
                ]);
            }
            
            // Throw a new exception with safe message
            // Use safeToString to absolutely guarantee it's a string
            $safeErrorMsg = $this->safeToString($errorMsg, 'An error occurred while rendering the payslip template');
            $finalMessage = 'Failed to render payslip template: ' . $safeErrorMsg;
            throw new \Exception($finalMessage);
        }
        
        try {
            // Validate HTML is a string before passing to PDF library
            if (!is_string($html)) {
                \Log::error('HTML is not a string', [
                    'type' => gettype($html),
                    'html_length' => is_string($html) ? strlen($html) : 'N/A'
                ]);
                throw new \Exception('Invalid HTML content for PDF generation');
            }
            
            // Check HTML for potential issues - look for "Array" text which might indicate array output
            if (stripos($html, 'Array') !== false && preg_match('/Array\s*\(/i', $html)) {
                // Find the exact location of the array output
                $arrayPos = stripos($html, 'Array');
                $contextStart = max(0, $arrayPos - 200);
                $contextEnd = min(strlen($html), $arrayPos + 200);
                $context = substr($html, $contextStart, $contextEnd - $contextStart);
                
                \Log::warning('HTML contains array output - attempting to clean', [
                    'array_position' => $arrayPos,
                    'html_length' => strlen($html),
                    'context_before' => substr($html, max(0, $arrayPos - 100), 100),
                    'context_after' => substr($html, $arrayPos, 100),
                    'full_context' => $context
                ]);
                
                // Try multiple cleaning strategies
                // IMPORTANT: Don't replace "Array" in PHP code blocks (like is_array, array(), etc.)
                $cleanedHtml = $html;
                
                // Strategy 1: Only replace "Array" that appears to be output (not in script tags or PHP code)
                // Split HTML into parts - preserve script tags, clean the rest
                // Use numeric value 1 for PREG_SPLIT_DELIMITER_CAPTURE (capture delimiters in result)
                $parts = preg_split('/(<script[^>]*>.*?<\/script>)/is', $cleanedHtml, -1, 1);
                $cleanedParts = [];
                
                foreach ($parts as $index => $part) {
                    // If it's a script tag, don't modify it
                    if (preg_match('/<script[^>]*>.*?<\/script>/is', $part)) {
                        $cleanedParts[] = $part;
                    } else {
                        // Clean this part - remove Array() patterns that are clearly output
                        $cleaned = $part;
                        // Only replace standalone "Array" or "Array()" that's not part of code
                        $cleaned = preg_replace('/\bArray\s*\(\s*\)/i', '[Data]', $cleaned);
                        $cleaned = preg_replace('/(?<![a-zA-Z_$])\bArray\b(?![a-zA-Z_$])/i', '[Data]', $cleaned);
                        $cleanedParts[] = $cleaned;
                    }
                }
                
                $cleanedHtml = implode('', $cleanedParts);
                
                if ($cleanedHtml !== $html) {
                    \Log::info('HTML cleaned successfully - removed array outputs', [
                        'original_length' => strlen($html),
                        'cleaned_length' => strlen($cleanedHtml),
                        'changes_made' => true
                    ]);
                    $html = $cleanedHtml;
                } else {
                    // If cleaning didn't work, log but continue anyway
                    \Log::error('Could not clean HTML array outputs, but continuing anyway', [
                        'html_preview' => substr($html, 0, 1000)
                    ]);
                }
            }
            
            // Log HTML length for debugging
            \Log::info('Generating PDF from HTML', [
                'html_length' => strlen($html),
                'html_preview' => substr($html, 0, 200)
            ]);
            
            $pdf = Pdf::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isPhpEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);

            // Safely convert to strings, ensuring no arrays - use safeToString for absolute safety
            $empId = $this->safeToString($sanitizedPayslipData['emp_no'] ?? '', 'N/A');
            if (empty($empId) || $empId === '') {
                $empId = 'N/A';
            }
            
            $payPeriodValue = $payPeriodStr ?? date('Y-m');
            $payPeriodValue = $this->safeToString($payPeriodValue, date('Y-m'));
            $payPeriod = str_replace('-', '_', $payPeriodValue);
            
            // Final safety check before concatenation
            $empId = $this->safeToString($empId, 'N/A');
            $payPeriod = $this->safeToString($payPeriod, date('Y_m'));
            
            $filename = "Salary_Slip_{$empId}_{$payPeriod}.pdf";

            return $pdf->stream($filename);
        } catch (\Throwable $e) {
            // Log the original error with full details for debugging
            try {
                $errorMsg = $this->safeToString($e->getMessage(), 'Unknown error');
                $errorFile = method_exists($e, 'getFile') ? $e->getFile() : 'unknown';
                $errorLine = method_exists($e, 'getLine') ? $e->getLine() : 0;
                
                \Log::error('PDF Generation Error', [
                    'exception_class' => get_class($e),
                    'message' => $errorMsg,
                    'file' => $errorFile,
                    'line' => $errorLine,
                ]);
            } catch (\Throwable $logError) {
                // If logging fails, use error_log as fallback
                error_log('PDF Generation Error: ' . get_class($e));
            }
            
            // Create a safe error message for the user
            $userMessage = 'Failed to generate PDF';
            try {
                $originalMsg = $e->getMessage();
                $safeMsg = $this->safeToString($originalMsg, '');
                if (!empty($safeMsg) && $safeMsg !== 'Array to string conversion') {
                    $userMessage = 'Failed to generate PDF: ' . $safeMsg;
                } elseif ($safeMsg === 'Array to string conversion') {
                    $userMessage = 'Failed to generate PDF: Invalid data format detected. Please check the payroll data.';
                }
            } catch (\Throwable $msgError) {
                // If message extraction fails, use default
            }
            
            // Ensure the message is definitely a string
            $userMessage = $this->safeToString($userMessage, 'Failed to generate PDF');
            
            // Throw a new exception with the safe message
            throw new \Exception($userMessage);
        }
    }
    
    /**
     * Get company settings from database
     */
    private function getCompanySettings()
    {
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        
        // Get organization info from SystemSetting if available
        $organizationSettings = \App\Models\SystemSetting::whereIn('key', [
            'company_name', 'trading_name', 'address', 'phone', 'email', 'website', 'tax_id'
        ])->get()->keyBy('key');
        
        // Helper function to safely get setting value
        $getSettingValue = function($key, $default = '') use ($organizationSettings) {
            if (isset($organizationSettings[$key]) && is_object($organizationSettings[$key])) {
                $value = $organizationSettings[$key]->value ?? $default;
                // Ensure it's not an array
                return is_array($value) ? $default : (string)$value;
            }
            return $default;
        };
        
        // Safely get values, ensuring they're strings, not arrays
        $companyName = $orgSettings->company_name ?? $getSettingValue('company_name', config('app.name', 'Company Name'));
        $tradingName = $getSettingValue('trading_name', $orgSettings->company_name ?? '');
        $address = $orgSettings->company_address ?? $getSettingValue('address', '');
        $phone = $orgSettings->company_phone ?? $getSettingValue('phone', '');
        $email = $orgSettings->company_email ?? $getSettingValue('email', config('mail.from.address', ''));
        $website = $orgSettings->company_website ?? $getSettingValue('website', '');
        $taxId = $orgSettings->company_tax_id ?? $getSettingValue('tax_id', '');
        
        return [
            'company_name' => is_array($companyName) ? config('app.name', 'Company Name') : (string)$companyName,
            'trading_name' => is_array($tradingName) ? '' : (string)$tradingName,
            'address' => is_array($address) ? '' : (string)$address,
            'city' => is_array($orgSettings->company_city ?? null) ? '' : (string)($orgSettings->company_city ?? ''),
            'state' => is_array($orgSettings->company_state ?? null) ? '' : (string)($orgSettings->company_state ?? ''),
            'country' => is_array($orgSettings->company_country ?? null) ? 'Tanzania' : (string)($orgSettings->company_country ?? 'Tanzania'),
            'postal_code' => is_array($orgSettings->company_postal_code ?? null) ? '' : (string)($orgSettings->company_postal_code ?? ''),
            'phone' => is_array($phone) ? '' : (string)$phone,
            'email' => is_array($email) ? config('mail.from.address', '') : (string)$email,
            'website' => is_array($website) ? '' : (string)$website,
            'tax_id' => is_array($taxId) ? '' : (string)$taxId,
            'logo_path' => is_array($orgSettings->company_logo ?? null) ? null : ($orgSettings->company_logo ?? null),
        ];
    }
    
    /**
     * Get bank details for employee
     */
    private function getBankDetails($employeeId)
    {
        try {
            // Try new bank_accounts table first
            if (DB::getSchemaBuilder()->hasTable('bank_accounts')) {
                $bank = DB::table('bank_accounts')
                    ->where('user_id', $employeeId)
                    ->where('is_primary', true)
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($bank) {
                    return [
                        'bank_name' => is_array($bank->bank_name ?? null) ? 'Not Provided' : (string)($bank->bank_name ?? 'Not Provided'),
                        'account_name' => is_array($bank->account_name ?? null) ? 'Not Provided' : (string)($bank->account_name ?? 'Not Provided'),
                        'account_number' => is_array($bank->account_number ?? null) ? '' : (string)($bank->account_number ?? ''),
                        'branch_name' => is_array($bank->branch_name ?? null) ? '' : (string)($bank->branch_name ?? ''),
                        'swift_code' => is_array($bank->swift_code ?? null) ? '' : (string)($bank->swift_code ?? ''),
                    ];
                }
                
                // If no primary, get any active account
                $bank = DB::table('bank_accounts')
                    ->where('user_id', $employeeId)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($bank) {
                    return [
                        'bank_name' => is_array($bank->bank_name ?? null) ? 'Not Provided' : (string)($bank->bank_name ?? 'Not Provided'),
                        'account_name' => is_array($bank->account_name ?? null) ? 'Not Provided' : (string)($bank->account_name ?? 'Not Provided'),
                        'account_number' => is_array($bank->account_number ?? null) ? '' : (string)($bank->account_number ?? ''),
                        'branch_name' => is_array($bank->branch_name ?? null) ? '' : (string)($bank->branch_name ?? ''),
                        'swift_code' => is_array($bank->swift_code ?? null) ? '' : (string)($bank->swift_code ?? ''),
                    ];
                }
            }
            
            // Fallback to old employee_bank_details table if exists
            if (DB::getSchemaBuilder()->hasTable('employee_bank_details')) {
                $bank = DB::table('employee_bank_details')
                    ->where('employee_id', $employeeId)
                    ->where(function($query) {
                        $query->where('is_primary', 1)
                              ->orWhere('status', 'active');
                    })
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($bank) {
                    return [
                        'bank_name' => is_array($bank->bank_name ?? null) ? 'Not Provided' : (string)($bank->bank_name ?? 'Not Provided'),
                        'account_name' => is_array($bank->account_name ?? null) ? 'Not Provided' : (string)($bank->account_name ?? 'Not Provided'),
                        'account_number' => is_array($bank->account_number ?? null) ? '' : (string)($bank->account_number ?? ''),
                        'branch_name' => '',
                        'swift_code' => '',
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Bank details table not found: ' . $e->getMessage());
        }
        
        return [
            'bank_name' => 'Not Provided',
            'account_name' => 'Not Provided',
            'account_number' => '',
            'branch_name' => '',
            'swift_code' => '',
        ];
    }
    
    /**
     * Get fixed deductions for employee
     */
    private function getFixedDeductions($employeeId)
    {
        $deductions = [];
        
        try {
            if (DB::getSchemaBuilder()->hasTable('employee_salary_deductions')) {
                $fixedDeductions = DB::table('employee_salary_deductions')
                    ->where('employee_id', $employeeId)
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                    })
                    ->get();
                
                foreach ($fixedDeductions as $deduction) {
                    // Only include monthly deductions or applicable one-time deductions
                    $shouldInclude = false;
                    if ($deduction->frequency === 'monthly') {
                        $shouldInclude = true;
                    } elseif ($deduction->frequency === 'one-time' && 
                             $deduction->start_date <= now() && 
                             (!$deduction->end_date || $deduction->end_date >= now())) {
                        $shouldInclude = true;
                    }
                    
                    if ($shouldInclude) {
                        // Safely extract deduction data - ensure all values are proper types
                        $deductionType = 'Other';
                        if (isset($deduction->deduction_type) && !is_array($deduction->deduction_type) && !is_object($deduction->deduction_type)) {
                            $deductionType = (string)$deduction->deduction_type;
                        }
                        
                        $deductionAmount = 0;
                        if (isset($deduction->amount)) {
                            if (is_numeric($deduction->amount)) {
                                $deductionAmount = (float)$deduction->amount;
                            } elseif (!is_array($deduction->amount) && !is_object($deduction->amount)) {
                                $deductionAmount = (float)((string)$deduction->amount);
                            }
                        }
                        
                        $deductionDesc = '';
                        if (isset($deduction->description) && !is_array($deduction->description) && !is_object($deduction->description)) {
                            $deductionDesc = (string)$deduction->description;
                        }
                        
                        $deductionFreq = 'monthly';
                        if (isset($deduction->frequency) && !is_array($deduction->frequency) && !is_object($deduction->frequency)) {
                            $deductionFreq = (string)$deduction->frequency;
                        }
                        
                        $deductions[] = [
                            'deduction_type' => $deductionType,
                            'amount' => $deductionAmount,
                            'description' => $deductionDesc,
                            'frequency' => $deductionFreq,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Fixed deductions table not found: ' . $e->getMessage());
        }
        
        return $deductions;
    }
    
    /**
     * Get logo as base64
     */
    private function getLogoBase64($logoPath)
    {
        $possiblePaths = [];
        
        // First, try to get logo from organization settings
        if ($logoPath) {
            // Path 1: storage/app/public/{logo_path} (most common - logo stored as 'settings/filename.png')
            $possiblePaths[] = storage_path('app/public/' . $logoPath);
            
            // Path 2: public/storage/{logo_path} (symlinked)
            $possiblePaths[] = public_path('storage/' . $logoPath);
            
            // Path 3: Check using Storage facade
            try {
                if (Storage::disk('public')->exists($logoPath)) {
                    $storagePath = Storage::disk('public')->path($logoPath);
                    if ($storagePath && file_exists($storagePath)) {
                        $possiblePaths[] = $storagePath;
                    }
                }
            } catch (\Exception $e) {
                // Continue with other paths
            }
            
            // Path 4: Try with Storage adapter
            try {
                if (Storage::disk('public')->exists($logoPath)) {
                    $realPath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . $logoPath;
                    if (file_exists($realPath)) {
                        $possiblePaths[] = $realPath;
                    }
                }
            } catch (\Exception $e) {
                // Continue with other paths
            }
        }
        
        // Fallback: Check for default logo in assets folder
        $defaultLogoPaths = [
            public_path('assets/img/office_link_logo.png'),
            public_path('assets/img/logo.png'),
            public_path('assets/img/company-logo.png'),
            public_path('assets/images/default_logo.png'),
            public_path('images/logo.png'),
        ];
        
        $possiblePaths = array_merge($possiblePaths, $defaultLogoPaths);
        
        // Remove duplicates and null values
        $possiblePaths = array_filter(array_unique($possiblePaths));
        
        // Find the first existing logo file
        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path) && is_file($path) && is_readable($path)) {
                // Verify it's actually an image file
                $imageInfo = @getimagesize($path);
                if ($imageInfo !== false) {
                    try {
                        $logoData = file_get_contents($path);
                        if ($logoData !== false) {
                            $logoBase64 = base64_encode($logoData);
                            $logoExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            
                            // Map extensions to MIME types
                            $mimeTypes = [
                                'jpg' => 'jpeg',
                                'jpeg' => 'jpeg',
                                'png' => 'png',
                                'gif' => 'gif',
                                'webp' => 'webp',
                                'svg' => 'svg+xml'
                            ];
                            
                            $mimeType = $mimeTypes[$logoExtension] ?? 'png';
                            return 'data:image/' . $mimeType . ';base64,' . $logoBase64;
                        }
                    } catch (\Exception $e) {
                        // Continue to next path
                        continue;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Convert number to words
     */
    public static function convertNumberToWords($number)
    {
        $number = (int)$number;
        if ($number === 0) return 'Zero';
        
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $words = '';
        
        if ($number >= 1000000) { 
            $words .= self::convertNumberToWords((int)($number / 1000000)) . ' Million '; 
            $number %= 1000000; 
        }
        if ($number >= 1000) { 
            $words .= self::convertNumberToWords((int)($number / 1000)) . ' Thousand '; 
            $number %= 1000; 
        }
        if ($number >= 100) { 
            $words .= $ones[(int)($number / 100)] . ' Hundred '; 
            $number %= 100; 
        }
        if ($number >= 20) { 
            $words .= $tens[(int)($number / 10)] . ' '; 
            $number %= 10; 
        } elseif ($number >= 10) { 
            $words .= $teens[$number - 10] . ' '; 
            $number = 0; 
        }
        if ($number > 0) { 
            $words .= $ones[$number] . ' '; 
        }
        
        return trim($words);
    }

    /**
     * Generate comprehensive payroll report PDF
     */
    public function generatePayrollReport(Payroll $payroll)
    {
        $items = $payroll->items()->with('employee.primaryDepartment')->get();

        // Calculate totals
        $totals = [
            'basic_salary' => $items->sum('basic_salary'),
            'overtime_amount' => $items->sum('overtime_amount'),
            'bonus_amount' => $items->sum('bonus_amount'),
            'allowance_amount' => $items->sum('allowance_amount'),
            'gross_salary' => 0,
            'nssf_amount' => $items->sum('nssf_amount'),
            'nhif_amount' => $items->sum('nhif_amount'),
            'heslb_amount' => $items->sum('heslb_amount'),
            'paye_amount' => $items->sum('paye_amount'),
            'deduction_amount' => $items->sum('deduction_amount'),
            'other_deductions' => $items->sum('other_deductions'),
            'total_deductions' => 0,
            'net_salary' => $items->sum('net_salary'),
            'employer_nssf' => $items->sum('employer_nssf'),
            'employer_wcf' => $items->sum('employer_wcf'),
            'employer_sdl' => $items->sum('employer_sdl'),
            'total_employer_cost' => $items->sum('total_employer_cost'),
        ];

        $totals['gross_salary'] = $totals['basic_salary'] 
            + $totals['overtime_amount'] 
            + $totals['bonus_amount'] 
            + $totals['allowance_amount'];

        $totals['total_deductions'] = $totals['nssf_amount'] 
            + $totals['nhif_amount'] 
            + $totals['heslb_amount'] 
            + $totals['paye_amount'] 
            + $totals['deduction_amount'] 
            + $totals['other_deductions'];

        // Department breakdown
        $departmentBreakdown = [];
        foreach ($items as $item) {
            $deptName = $item->employee->primaryDepartment->name ?? 'Unassigned';
            if (!isset($departmentBreakdown[$deptName])) {
                $departmentBreakdown[$deptName] = [
                    'count' => 0,
                    'total_gross' => 0,
                    'total_net' => 0,
                    'total_deductions' => 0,
                ];
            }
            $deptGross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
            $departmentBreakdown[$deptName]['count']++;
            $departmentBreakdown[$deptName]['total_gross'] += $deptGross;
            $departmentBreakdown[$deptName]['total_net'] += $item->net_salary;
            $departmentBreakdown[$deptName]['total_deductions'] += ($item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->paye_amount + $item->deduction_amount);
        }

        $data = [
            'payroll' => $payroll,
            'items' => $items,
            'totals' => $totals,
            'departmentBreakdown' => $departmentBreakdown,
            'companyName' => config('app.name', 'Company Name'),
            'companyAddress' => config('app.address', ''),
            'generatedAt' => now()->format('F j, Y H:i'),
            'processor' => $payroll->processor ?? null,
            'reviewer' => $payroll->reviewer ?? null,
            'approver' => $payroll->approver ?? null,
            'payer' => $payroll->payer ?? null,
        ];

        $html = view('modules.hr.pdf.payroll-report', $data)->render();
        
        $pdf = Pdf::loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);

        // Safely get pay_period for filename
        $reportPayPeriod = $this->safeToString($payroll->pay_period ?? date('Y-m'), date('Y-m'));
        $filename = 'Payroll_Report_' . $reportPayPeriod . '_' . date('Y-m-d') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Format currency for display
     */
    private function formatCurrency($amount)
    {
        return 'TZS ' . number_format($amount, 0, '.', ',');
    }
    
    /**
     * Validate payslip data to ensure no arrays are passed where strings are expected
     * Logs warnings but doesn't throw exceptions - sanitization should have handled most cases
     */
    private function validatePayslipData($payslipData, $fixedDeductions, $bankDetails, $companySettings)
    {
        // Validate payslip data - log warnings for arrays
        foreach ($payslipData as $key => $value) {
            if (is_array($value)) {
                \Log::warning('Array found in payslip data (should be sanitized)', [
                    'key' => $key,
                    'value_type' => gettype($value),
                    'value_count' => count($value)
                ]);
            }
        }
        
        // Validate fixed deductions
        foreach ($fixedDeductions as $index => $deduction) {
            if (!is_array($deduction)) {
                \Log::warning('Fixed deduction is not an array', [
                    'index' => $index,
                    'type' => gettype($deduction)
                ]);
                continue;
            }
            foreach ($deduction as $key => $value) {
                if (is_array($value)) {
                    \Log::warning('Array found in fixed deduction field', [
                        'index' => $index,
                        'key' => $key,
                        'value_type' => gettype($value)
                    ]);
                }
            }
        }
        
        // Validate bank details
        foreach ($bankDetails as $key => $value) {
            if (is_array($value)) {
                \Log::warning('Array found in bank details', [
                    'key' => $key,
                    'value_type' => gettype($value)
                ]);
            }
        }
        
        // Validate company settings
        foreach ($companySettings as $key => $value) {
            if (is_array($value) && $key !== 'logo_path') {
                \Log::warning('Array found in company settings', [
                    'key' => $key,
                    'value_type' => gettype($value)
                ]);
            }
        }
    }
    
    /**
     * Ensure all data in the array is safe (no arrays where strings expected)
     * This recursively checks and sanitizes nested arrays
     */
    private function ensureAllDataIsSafe(&$data)
    {
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                // For nested arrays, recursively check them
                // But if this is a data structure that should be an array (like fixed_deductions), keep it
                if ($key === 'fixed_deductions' || $key === 'payslip' || $key === 'company_settings' || $key === 'bank_details') {
                    // These are expected to be arrays, just sanitize their contents
                    $this->ensureAllDataIsSafe($value);
                } else {
                    // Unexpected array, convert to empty string
                    \Log::warning("Unexpected array found in data at key: {$key}", ['value' => $value]);
                    $value = '';
                }
            } elseif (is_object($value) && !($value instanceof \DateTime) && !($value instanceof \Carbon\Carbon)) {
                // Convert objects to strings (except DateTime objects)
                try {
                    if (method_exists($value, '__toString')) {
                        $value = (string)$value;
                    } else {
                        $value = '';
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Could not convert object to string for key: {$key}", [
                        'class' => get_class($value)
                    ]);
                    $value = '';
                }
            }
        }
        
        // Special handling for fixed_deductions - ensure all amounts are numeric
        if (isset($data['fixed_deductions']) && is_array($data['fixed_deductions'])) {
            foreach ($data['fixed_deductions'] as &$deduction) {
                if (isset($deduction['amount']) && is_array($deduction['amount'])) {
                    \Log::warning('Fixed deduction amount is an array', ['deduction' => $deduction]);
                    $deduction['amount'] = 0;
                } elseif (isset($deduction['amount']) && !is_numeric($deduction['amount'])) {
                    $deduction['amount'] = is_numeric($deduction['amount']) ? (float)$deduction['amount'] : 0;
                }
            }
        }
        
        // Special handling for payslip data - ensure all string fields are strings
        if (isset($data['payslip']) && is_array($data['payslip'])) {
            foreach ($data['payslip'] as $payslipKey => &$payslipValue) {
                if (is_array($payslipValue)) {
                    \Log::warning("Payslip field '{$payslipKey}' is an array", ['value' => $payslipValue]);
                    // Set appropriate defaults based on the key
                    if (in_array($payslipKey, ['basic_salary', 'overtime_amount', 'bonus_amount', 'allowance_amount', 'nssf_amount', 'paye_amount', 'nhif_amount', 'heslb_amount', 'wcf_amount', 'deduction_amount', 'net_salary'])) {
                        $payslipValue = 0;
                    } else {
                        $payslipValue = 'N/A';
                    }
                }
            }
        }
    }
    
    /**
     * Deep validation - recursively check all data for arrays where strings expected
     */
    private function deepValidateData($data, $path = 'root')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path . '.' . $key;
                
                if (is_array($value)) {
                    // Check if this is an expected array structure
                    if (in_array($key, ['fixed_deductions', 'payslip', 'company_settings', 'bank_details'])) {
                        // Expected arrays - validate their contents
                        $this->deepValidateData($value, $currentPath);
                    } else {
                        // Unexpected array - log and convert
                        \Log::error("Unexpected array found at path: {$currentPath}", [
                            'key' => $key,
                            'value' => $value,
                            'path' => $currentPath
                        ]);
                    }
                } elseif (is_object($value) && !($value instanceof \DateTime) && !($value instanceof \Carbon\Carbon)) {
                    // Objects should be converted
                    \Log::warning("Object found at path: {$currentPath}", [
                        'class' => get_class($value),
                        'path' => $currentPath
                    ]);
                } else {
                    // Recursively check nested structures
                    if (is_array($value) || (is_object($value) && !($value instanceof \DateTime) && !($value instanceof \Carbon\Carbon))) {
                        $this->deepValidateData($value, $currentPath);
                    }
                }
            }
        }
    }
    
    /**
     * Safely convert any value to a string, handling arrays and objects
     */
    private function safeToString($value, $default = '')
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            \Log::warning('Attempted to convert array to string', ['value' => $value]);
            return $default;
        }
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                try {
                    return (string)$value;
                } catch (\Throwable $e) {
                    return $default;
                }
            }
            return $default;
        }
        if (is_null($value)) {
            return $default;
        }
        try {
            return (string)$value;
        } catch (\Throwable $e) {
            return $default;
        }
    }
    
    /**
     * Safely format a date value
     */
    private function safeFormatDate($date, $format = 'Y-m-d')
    {
        if (empty($date)) {
            return '';
        }
        
        // If it's already a string, try to parse it
        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Exception $e) {
                return '';
            }
        }
        
        // If it's an array, return empty
        if (is_array($date)) {
            \Log::warning('Date value is an array', ['date' => $date]);
            return '';
        }
        
        // If it's a Carbon instance or DateTime
        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
            try {
                return $date->format($format);
            } catch (\Exception $e) {
                return '';
            }
        }
        
        // If it's an object with a format method
        if (is_object($date) && method_exists($date, 'format')) {
            try {
                return $date->format($format);
            } catch (\Exception $e) {
                return '';
            }
        }
        
        // If it's an object, try to convert to string and parse
        if (is_object($date)) {
            try {
                $dateString = (string)$date;
                if (!empty($dateString)) {
                    return \Carbon\Carbon::parse($dateString)->format($format);
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        return '';
    }
}

