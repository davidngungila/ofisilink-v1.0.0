<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBonus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollBonusController extends Controller
{
    /**
     * Display a listing of bonus records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            abort(403, 'You do not have permission to manage bonuses.');
        }

        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedMonth = $month;

        // Get all employees
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with(['primaryDepartment', 'employee'])
            ->orderBy('name')
            ->get();

        // Get bonus records for the selected month
        $bonuses = EmployeeBonus::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment', 'creator'])
            ->get()
            ->keyBy('employee_id');

        // Calculate statistics
        $totalAmount = $bonuses->sum('amount');
        $employeeCount = $bonuses->count();
        $avgAmount = $employeeCount > 0 ? $totalAmount / $employeeCount : 0;

        // Get available months
        $availableMonths = EmployeeBonus::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        return view('modules.hr.pages.manage-bonus', compact(
            'employees', 'bonuses', 'month', 'selectedMonth', 
            'totalAmount', 'employeeCount', 'avgAmount',
            'availableMonths'
        ));
    }

    /**
     * Store a newly created bonus record
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage bonuses.'
            ], 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount' => 'required|numeric|min:0',
            'bonus_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Check if record already exists for this employee and month
            $existing = EmployeeBonus::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update([
                    'amount' => $request->amount,
                    'bonus_type' => $request->bonus_type,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'updated_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bonus record updated successfully.',
                    'bonus' => $existing->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            } else {
                // Create new record
                $bonus = EmployeeBonus::create([
                    'employee_id' => $request->employee_id,
                    'month' => $request->month,
                    'amount' => $request->amount,
                    'bonus_type' => $request->bonus_type,
                    'description' => $request->description,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bonus record created successfully.',
                    'bonus' => $bonus->load(['employee.employee', 'employee.primaryDepartment'])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating bonus record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bonus record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified bonus record
     */
    public function update(Request $request, EmployeeBonus $bonus)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage bonuses.'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'bonus_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $bonus->update([
                'amount' => $request->amount,
                'bonus_type' => $request->bonus_type,
                'description' => $request->description,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bonus record updated successfully.',
                'bonus' => $bonus->load(['employee.employee', 'employee.primaryDepartment'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating bonus record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bonus record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified bonus record
     */
    public function destroy(EmployeeBonus $bonus)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage bonuses.'
            ], 403);
        }

        try {
            $bonus->update([
                'is_active' => false,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bonus record deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deactivating bonus record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate bonus record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download bonus template with real employee data
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

        $filename = 'bonus_template_' . date('Y-m-d') . '.csv';
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
                'bonus_type', 
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
                    '', // Bonus Type - optional (Performance, Annual, Special, Project, Other)
                    ''  // Description - optional
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk create bonus records from file upload
     */
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $can_manage = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_manage) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage bonuses.'
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
                    // New format: employee_code, employee_id, employee_name, department, basic_salary, amount, bonus_type, description
                    $employeeIdentifier = '';
                    $amount = '';
                    $bonusType = '';
                    $description = '';
                    
                    if (count($row) >= 8) {
                        // New format with all employee details (employee_code is first column)
                        $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                        $amount = trim($row[5] ?? '0'); // amount
                        $bonusType = trim($row[6] ?? ''); // bonus_type
                        $description = trim($row[7] ?? ''); // description
                    } else {
                        // Old format or minimal format
                        $employeeIdentifier = trim($row[0] ?? '');
                        $amount = trim($row[1] ?? '0');
                        $bonusType = trim($row[2] ?? '');
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
                    
                    $existing = EmployeeBonus::where('employee_id', $employee->id)
                        ->where('month', $month)
                        ->first();
                    
                    if ($existing) {
                        $existing->update([
                            'amount' => $amount,
                            'bonus_type' => $bonusType ?: null,
                            'description' => $description ?: null,
                            'updated_by' => Auth::id(),
                            'is_active' => true,
                        ]);
                        $updated++;
                    } else {
                        EmployeeBonus::create([
                            'employee_id' => $employee->id,
                            'month' => $month,
                            'amount' => $amount,
                            'bonus_type' => $bonusType ?: null,
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
                        $bonusType = '';
                        $description = '';
                        
                        if (count($row) >= 8) {
                            // New format with all employee details (employee_code is first column)
                            $employeeIdentifier = trim($row[0] ?? ''); // employee_code (e.g., EMP006)
                            $amount = trim($row[5] ?? '0'); // amount
                            $bonusType = trim($row[6] ?? ''); // bonus_type
                            $description = trim($row[7] ?? ''); // description
                        } else {
                            // Old format or minimal format
                            $employeeIdentifier = trim($row[0] ?? '');
                            $amount = trim($row[1] ?? '0');
                            $bonusType = trim($row[2] ?? '');
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
                        
                        $existing = EmployeeBonus::where('employee_id', $employee->id)
                            ->where('month', $month)
                            ->first();
                        
                        if ($existing) {
                            $existing->update([
                                'amount' => $amount,
                                'bonus_type' => $bonusType ?: null,
                                'description' => $description ?: null,
                                'updated_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $updated++;
                        } else {
                            EmployeeBonus::create([
                                'employee_id' => $employee->id,
                                'month' => $month,
                                'amount' => $amount,
                                'bonus_type' => $bonusType ?: null,
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
                'message' => "Successfully processed {$created} new and {$updated} updated bonus records.",
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in bulk bonus creation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk bonus records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bonus data for a specific month
     */
    public function getByMonth(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        $bonuses = EmployeeBonus::where('month', $month)
            ->where('is_active', true)
            ->with(['employee.employee', 'employee.primaryDepartment'])
            ->get();

        return response()->json([
            'success' => true,
            'bonuses' => $bonuses
        ]);
    }
}
