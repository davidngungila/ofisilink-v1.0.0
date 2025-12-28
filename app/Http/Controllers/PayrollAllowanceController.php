<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAllowance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollAllowanceController extends Controller
{
    /**
     * Display a listing of allowance records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            abort(403, 'You do not have permission to manage allowances.');
        }

        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedMonth = $month;

        // Get all employees
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with(['primaryDepartment', 'employee'])
            ->orderBy('name')
            ->get();

        // Get allowance records for the selected month
        $allowances = EmployeeAllowance::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment', 'creator'])
            ->get()
            ->keyBy('employee_id');

        // Calculate statistics
        $totalAmount = $allowances->sum('amount');
        $employeeCount = $allowances->count();
        $avgAmount = $employeeCount > 0 ? $totalAmount / $employeeCount : 0;

        // Get available months
        $availableMonths = EmployeeAllowance::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        return view('modules.hr.pages.manage-allowance', compact(
            'employees', 'allowances', 'month', 'selectedMonth', 
            'totalAmount', 'employeeCount', 'avgAmount',
            'availableMonths'
        ));
    }

    /**
     * Store a newly created allowance record
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage allowances.'
            ], 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount' => 'required|numeric|min:0',
            'allowance_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Check if record already exists for this employee and month
            $existing = EmployeeAllowance::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update([
                    'amount' => $request->amount,
                    'allowance_type' => $request->allowance_type,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'updated_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Allowance record updated successfully.',
                    'allowance' => $existing->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            } else {
                // Create new record
                $allowance = EmployeeAllowance::create([
                    'employee_id' => $request->employee_id,
                    'month' => $request->month,
                    'amount' => $request->amount,
                    'allowance_type' => $request->allowance_type,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Allowance record created successfully.',
                    'allowance' => $allowance->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating allowance record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create allowance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified allowance record
     */
    public function update(Request $request, EmployeeAllowance $allowance)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage allowances.'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'allowance_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $allowance->update([
                'amount' => $request->amount,
                'allowance_type' => $request->allowance_type,
                'description' => $request->description,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Allowance record updated successfully.',
                'allowance' => $allowance->load(['employee.employee', 'employee.primaryDepartment'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating allowance record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update allowance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified allowance record
     */
    public function destroy(EmployeeAllowance $allowance)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage allowances.'
            ], 403);
        }

        try {
            $allowance->update([
                'is_active' => false,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Allowance record deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deactivating allowance record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate allowance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download allowance template with real employee data
     */
    public function downloadTemplate()
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            abort(403, 'You do not have permission to download templates.');
        }

        // Get all active employees with their details
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with(['primaryDepartment', 'employee'])
            ->orderBy('name')
            ->get();

        $filename = 'allowance_template_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            
            // Header row with all details (employee_code is the primary identifier like EMP006)
            fputcsv($file, [
                'employee_code', 
                'employee_id', 
                'employee_name', 
                'department', 
                'basic_salary', 
                'amount', 
                'allowance_type', 
                'description'
            ]);
            
            // Add all employees with their real data
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->employee_id ?? '', // Primary identifier (e.g., EMP006) - from users table
                    $employee->id, // System ID (for reference)
                    $employee->name ?? '',
                    $employee->primaryDepartment->name ?? '',
                    $employee->employee->salary ?? 0,
                    '', // Amount - to be filled by user
                    '', // Allowance Type - optional (Transport, Housing, Medical, Meal, Communication, Other)
                    ''  // Description - optional
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk create allowance records from file upload
     */
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage allowances.'
            ], 403);
        }

        $request->validate([
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $extension = strtolower($value->getClientOriginalExtension());
                        $allowed = ['xlsx', 'xls', 'csv'];
                        if (!in_array($extension, $allowed)) {
                            $fail('The file must be a file of type: xlsx, xls, csv.');
                        }
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $month = $request->month;
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            if (strtolower($extension) === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if ($handle === false) {
                    throw new \Exception('Failed to open CSV file');
                }
                
                $header = fgetcsv($handle);
                if (!$header) {
                    throw new \Exception('CSV file is empty or invalid');
                }
                
                $rowIndex = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $rowIndex++;
                    // Skip if row is empty
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    if (count($row) < 2) {
                        $errors[] = "Row {$rowIndex}: Insufficient columns";
                        continue;
                    }
                    
                    // Determine which column has the amount value
                    // New format: employee_code, employee_id, employee_name, department, basic_salary, amount, allowance_type, description
                    $employeeIdentifier = '';
                    $amount = '';
                    $allowanceType = '';
                    $description = '';
                    
                    if (count($row) >= 8) {
                        // New format with all employee details (employee_code is first column)
                        $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                        $amount = trim($row[5] ?? '0'); // amount
                        $allowanceType = trim($row[6] ?? ''); // allowance_type
                        $description = trim($row[7] ?? ''); // description
                    } else {
                        // Old format or minimal format
                        $employeeIdentifier = trim($row[0] ?? '');
                        $amount = trim($row[1] ?? '0');
                        $allowanceType = trim($row[2] ?? '');
                        $description = trim($row[3] ?? '');
                    }
                    
                    if (empty($employeeIdentifier)) {
                        $errors[] = "Row {$rowIndex}: Employee Code/ID is required";
                        continue;
                    }
                    
                    if (!is_numeric($amount) || $amount < 0) {
                        $errors[] = "Row {$rowIndex}: Valid amount is required";
                        continue;
                    }
                    
                    // Find employee by employee_id (like EMP006) from users table first, then by system ID
                    $employee = User::where('employee_id', $employeeIdentifier)
                        ->orWhere('id', $employeeIdentifier)
                        ->whereHas('employee')
                        ->first();
                    
                    if (!$employee) {
                        $errors[] = "Row {$rowIndex}: Employee not found: {$employeeIdentifier}";
                        continue;
                    }
                    
                    $existing = EmployeeAllowance::where('employee_id', $employee->id)
                        ->where('month', $month)
                        ->first();
                    
                    if ($existing) {
                        $existing->update([
                            'amount' => $amount,
                            'allowance_type' => $allowanceType ?: null,
                            'description' => $description ?: null,
                            'updated_by' => Auth::id(),
                            'is_active' => true,
                        ]);
                        $updated++;
                    } else {
                        EmployeeAllowance::create([
                            'employee_id' => $employee->id,
                            'month' => $month,
                            'amount' => $amount,
                            'allowance_type' => $allowanceType ?: null,
                            'description' => $description ?: null,
                            'created_by' => Auth::id(),
                            'is_active' => true,
                        ]);
                        $created++;
                    }
                }
                fclose($handle);
            } else {
                if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                    $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                    if (empty($data) || empty($data[0])) {
                        throw new \Exception('Excel file is empty or invalid');
                    }
                    
                    $rows = $data[0];
                    array_shift($rows);
                    
                    $rowIndex = 1;
                    foreach ($rows as $row) {
                        $rowIndex++;
                        // Skip if row is empty
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        
                        if (count($row) < 2) {
                            $errors[] = "Row {$rowIndex}: Insufficient columns";
                            continue;
                        }
                        
                        // Determine which column has the amount value
                        $employeeIdentifier = '';
                        $amount = '';
                        $allowanceType = '';
                        $description = '';
                        
                        if (count($row) >= 8) {
                            // New format with all employee details (employee_code is first column)
                            $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                            $amount = trim($row[5] ?? '0'); // amount
                            $allowanceType = trim($row[6] ?? ''); // allowance_type
                            $description = trim($row[7] ?? ''); // description
                        } else {
                            // Old format or minimal format
                            $employeeIdentifier = trim($row[0] ?? '');
                            $amount = trim($row[1] ?? '0');
                            $allowanceType = trim($row[2] ?? '');
                            $description = trim($row[3] ?? '');
                        }
                        
                        if (empty($employeeIdentifier) || !is_numeric($amount) || $amount < 0) {
                            $errors[] = "Row {$rowIndex}: Invalid data";
                            continue;
                        }
                        
                        // Find employee by employee_id (like EMP006) from users table first, then by system ID
                        $employee = User::where('employee_id', $employeeIdentifier)
                            ->orWhere('id', $employeeIdentifier)
                            ->whereHas('employee')
                            ->first();
                        
                        if (!$employee) {
                            $errors[] = "Row {$rowIndex}: Employee not found: {$employeeIdentifier}";
                            continue;
                        }
                        
                        $existing = EmployeeAllowance::where('employee_id', $employee->id)
                            ->where('month', $month)
                            ->first();
                        
                        if ($existing) {
                            $existing->update([
                                'amount' => $amount,
                                'allowance_type' => $allowanceType ?: null,
                                'description' => $description ?: null,
                                'updated_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $updated++;
                        } else {
                            EmployeeAllowance::create([
                                'employee_id' => $employee->id,
                                'month' => $month,
                                'amount' => $amount,
                                'allowance_type' => $allowanceType ?: null,
                                'description' => $description ?: null,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $created++;
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Excel support requires Laravel Excel package. Please use CSV format.'
                    ], 400);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$created} new and {$updated} updated allowance records.",
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in bulk allowance creation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk allowance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get allowance data for a specific month
     */
    public function getByMonth(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        $allowances = EmployeeAllowance::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment'])
            ->get();

        return response()->json([
            'success' => true,
            'allowances' => $allowances
        ]);
    }
}
