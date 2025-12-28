<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeOvertime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollOvertimeController extends Controller
{
    /**
     * Display a listing of overtime records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            abort(403, 'You do not have permission to manage overtime.');
        }

        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedMonth = $month;

        // Get all employees
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with(['primaryDepartment', 'employee'])
            ->orderBy('name')
            ->get();

        // Get overtime records for the selected month
        $overtimes = EmployeeOvertime::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment', 'creator'])
            ->get()
            ->keyBy('employee_id');

        // Calculate statistics
        $totalHours = $overtimes->sum('hours');
        $totalAmount = $overtimes->sum('amount');
        $employeeCount = $overtimes->count();
        $avgHours = $employeeCount > 0 ? $totalHours / $employeeCount : 0;

        // Get available months
        $availableMonths = EmployeeOvertime::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        return view('modules.hr.pages.manage-overtime', compact(
            'employees', 'overtimes', 'month', 'selectedMonth', 
            'totalHours', 'totalAmount', 'employeeCount', 'avgHours',
            'availableMonths'
        ));
    }

    /**
     * Store a newly created overtime record
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage overtime.'
            ], 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'hours' => 'required|numeric|min:0|max:744',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Get employee's basic salary to calculate hourly rate
            $employee = User::with('employee')->findOrFail($request->employee_id);
            $basicSalary = $employee->employee->salary ?? 0;
            
            if ($basicSalary <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee does not have a valid salary set.'
                ], 400);
            }

            // Calculate hourly rate (assuming 22 working days, 8 hours per day)
            $hourlyRate = $basicSalary / (22 * 8);
            
            // Calculate overtime amount (1.5x rate)
            $amount = $request->hours * $hourlyRate * 1.5;

            // Check if record already exists for this employee and month
            $existing = EmployeeOvertime::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update([
                    'hours' => $request->hours,
                    'hourly_rate' => $hourlyRate,
                    'amount' => $amount,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'updated_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Overtime record updated successfully.',
                    'overtime' => $existing->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            } else {
                // Create new record
                $overtime = EmployeeOvertime::create([
                    'employee_id' => $request->employee_id,
                    'month' => $request->month,
                    'hours' => $request->hours,
                    'hourly_rate' => $hourlyRate,
                    'amount' => $amount,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Overtime record created successfully.',
                    'overtime' => $overtime->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating overtime record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create overtime record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified overtime record
     */
    public function update(Request $request, EmployeeOvertime $overtime)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage overtime.'
            ], 403);
        }

        $request->validate([
            'hours' => 'required|numeric|min:0|max:744',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Get employee's basic salary to recalculate
            $employee = $overtime->employee;
            $basicSalary = $employee->employee->salary ?? 0;
            
            if ($basicSalary <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee does not have a valid salary set.'
                ], 400);
            }

            // Recalculate hourly rate and amount
            $hourlyRate = $basicSalary / (22 * 8);
            $amount = $request->hours * $hourlyRate * 1.5;

            $overtime->update([
                'hours' => $request->hours,
                'hourly_rate' => $hourlyRate,
                'amount' => $amount,
                'description' => $request->description,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Overtime record updated successfully.',
                'overtime' => $overtime->load(['employee.employee', 'employee.primaryDepartment'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating overtime record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update overtime record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified overtime record
     */
    public function destroy(EmployeeOvertime $overtime)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage overtime.'
            ], 403);
        }

        try {
            $overtime->update([
                'is_active' => false,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Overtime record deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deactivating overtime record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate overtime record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create overtime records from file upload
     */
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage overtime.'
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
                    
                    // Handle different column formats
                    // Format 1: employee_id, hours, description (old format)
                    // Format 2: employee_id, employee_code, employee_name, department, basic_salary, hours, description (new format)
                    if (count($row) < 2) {
                        $errors[] = "Row {$rowIndex}: Insufficient columns";
                        continue;
                    }
                    
                    // Determine which column has the hours value
                    // New format: employee_code, employee_id, employee_name, department, basic_salary, hours, description
                    // Old format: employee_id/employee_code, hours, description
                    $employeeIdentifier = '';
                    $hours = '';
                    $description = '';
                    
                    if (count($row) >= 7) {
                        // New format with all employee details (employee_code is first column)
                        $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                        $hours = trim($row[5] ?? '0'); // hours
                        $description = trim($row[6] ?? ''); // description
                    } else {
                        // Old format or minimal format
                        $employeeIdentifier = trim($row[0] ?? '');
                        $hours = trim($row[1] ?? '0');
                        $description = trim($row[2] ?? '');
                    }
                    
                    if (empty($employeeIdentifier)) {
                        $errors[] = "Row {$rowIndex}: Employee Code/ID is required";
                        continue;
                    }
                    
                    if (!is_numeric($hours) || $hours < 0 || $hours > 744) {
                        $errors[] = "Row {$rowIndex}: Valid hours (0-744) is required";
                        continue;
                    }
                    
                    // Find employee by employee_id (like EMP006) from users table first, then by system ID
                    $employee = User::where('employee_id', $employeeIdentifier)
                        ->orWhere('id', $employeeIdentifier)
                        ->whereHas('employee')
                        ->with('employee')
                        ->first();
                    
                    if (!$employee || !$employee->employee) {
                        $errors[] = "Row {$rowIndex}: Employee not found for identifier: {$employeeIdentifier}";
                        continue;
                    }
                    
                    $basicSalary = $employee->employee->salary ?? 0;
                    if ($basicSalary <= 0) {
                        $errors[] = "Row {$rowIndex}: Employee {$employee->name} does not have a valid salary";
                        continue;
                    }
                    
                    $hourlyRate = $basicSalary / (22 * 8);
                    $amount = $hours * $hourlyRate * 1.5;
                    
                    $existing = EmployeeOvertime::where('employee_id', $employee->id)
                        ->where('month', $month)
                        ->first();
                    
                    if ($existing) {
                        $existing->update([
                            'hours' => $hours,
                            'hourly_rate' => $hourlyRate,
                            'amount' => $amount,
                            'description' => $description ?: null,
                            'updated_by' => Auth::id(),
                            'is_active' => true,
                        ]);
                        $updated++;
                    } else {
                        EmployeeOvertime::create([
                            'employee_id' => $employee->id,
                            'month' => $month,
                            'hours' => $hours,
                            'hourly_rate' => $hourlyRate,
                            'amount' => $amount,
                            'description' => $description ?: null,
                            'created_by' => Auth::id(),
                            'is_active' => true,
                        ]);
                        $created++;
                    }
                }
                fclose($handle);
            } else {
                // Excel file - try Laravel Excel if available
                if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                    $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                    if (empty($data) || empty($data[0])) {
                        throw new \Exception('Excel file is empty or invalid');
                    }
                    
                    $rows = $data[0];
                    array_shift($rows); // Skip header
                    
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
                        
                        // Determine which column has the hours value
                        $employeeIdentifier = '';
                        $hours = '';
                        $description = '';
                        
                        if (count($row) >= 7) {
                            // New format with all employee details (employee_code is first column)
                            $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                            $hours = trim($row[5] ?? '0'); // hours
                            $description = trim($row[6] ?? ''); // description
                        } else {
                            // Old format or minimal format
                            $employeeIdentifier = trim($row[0] ?? '');
                            $hours = trim($row[1] ?? '0');
                            $description = trim($row[2] ?? '');
                        }
                        
                        if (empty($employeeIdentifier)) {
                            $errors[] = "Row {$rowIndex}: Employee Code/ID is required";
                            continue;
                        }
                        
                        if (!is_numeric($hours) || $hours < 0 || $hours > 744) {
                            $errors[] = "Row {$rowIndex}: Valid hours (0-744) is required";
                            continue;
                        }
                        
                        // Find employee by employee_id (like EMP006) from users table first, then by system ID
                        $employee = User::where('employee_id', $employeeIdentifier)
                            ->orWhere('id', $employeeIdentifier)
                            ->whereHas('employee')
                            ->with('employee')
                            ->first();
                        
                        if (!$employee || !$employee->employee) {
                            $errors[] = "Row {$rowIndex}: Employee not found: {$employeeIdentifier}";
                            continue;
                        }
                        
                        $basicSalary = $employee->employee->salary ?? 0;
                        if ($basicSalary <= 0) {
                            $errors[] = "Row {$rowIndex}: Employee {$employee->name} has no valid salary";
                            continue;
                        }
                        
                        $hourlyRate = $basicSalary / (22 * 8);
                        $amount = $hours * $hourlyRate * 1.5;
                        
                        $existing = EmployeeOvertime::where('employee_id', $employee->id)
                            ->where('month', $month)
                            ->first();
                        
                        if ($existing) {
                            $existing->update([
                                'hours' => $hours,
                                'hourly_rate' => $hourlyRate,
                                'amount' => $amount,
                                'description' => $description ?: null,
                                'updated_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $updated++;
                        } else {
                            EmployeeOvertime::create([
                                'employee_id' => $employee->id,
                                'month' => $month,
                                'hours' => $hours,
                                'hourly_rate' => $hourlyRate,
                                'amount' => $amount,
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
                        'message' => 'Excel support requires Laravel Excel package. Please use CSV format or install maatwebsite/excel.'
                    ], 400);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$created} new and {$updated} updated overtime records.",
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in bulk overtime creation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk overtime records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download overtime template with real employee data
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

        $filename = 'overtime_template_' . date('Y-m-d') . '.csv';
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
                'hours', 
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
                    '', // Hours - to be filled by user
                    ''  // Description - optional
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get overtime data for a specific month
     */
    public function getByMonth(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        $overtimes = EmployeeOvertime::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment'])
            ->get();

        return response()->json([
            'success' => true,
            'overtimes' => $overtimes
        ]);
    }
}
