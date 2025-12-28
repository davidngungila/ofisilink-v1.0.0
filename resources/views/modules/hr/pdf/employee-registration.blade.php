<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Registration - {{ $employee->name }}</title>
    <style>
        @page { 
            margin: 15mm 20mm 60mm 20mm; 
            size: A4 portrait;
        }
        body { 
            font-family: "DejaVu Sans", Arial, sans-serif; 
            font-size: 10pt; 
            line-height: 1.5; 
            color: #333; 
            background: #ffffff;
        }
        .header { 
            border-bottom: 4px solid #940000; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .header-content { 
            display: table; 
            width: 100%; 
        }
        .logo { 
            max-width: 120px; 
            max-height: 100px; 
            display: block;
        }
        .company-info { 
            text-align: center; 
            padding: 10px 0;
        }
        .company-name { 
            font-size: 20pt; 
            font-weight: bold; 
            color: #940000; 
            margin-bottom: 5px; 
        }
        .system-name {
            font-size: 12pt;
            color: #666;
            margin-bottom: 8px;
        }
        .document-title { 
            font-size: 18pt; 
            font-weight: bold; 
            text-align: center; 
            margin: 25px 0; 
            color: #940000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section { 
            margin-bottom: 25px; 
            page-break-inside: avoid; 
        }
        .section-title { 
            background: linear-gradient(to right, #940000, #b30000);
            color: white;
            padding: 10px 15px; 
            font-weight: bold; 
            font-size: 12pt;
            margin-bottom: 12px; 
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 9.5pt;
        }
        .info-table th { 
            background-color: #f5f5f5; 
            padding: 8px 10px; 
            text-align: left; 
            font-weight: bold; 
            width: 180px; 
            border: 1px solid #ddd; 
            color: #333;
            font-size: 9.5pt;
        }
        .info-table td { 
            padding: 8px 10px; 
            border: 1px solid #ddd; 
            background-color: #fff;
            color: #333;
            font-size: 9.5pt;
        }
        .info-table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 9pt; 
        }
        .data-table th { 
            background-color: #f5f5f5;
            color: #333; 
            padding: 10px 8px; 
            text-align: left; 
            font-weight: bold; 
            border: 1px solid #ddd;
            font-size: 9.5pt;
        }
        .data-table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            color: #333;
            font-size: 9pt;
        }
        .data-table tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .data-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table thead {
            background-color: #f5f5f5;
        }
        .footer { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 8pt; 
            color: #666; 
            padding: 10px; 
            border-top: 2px solid #940000; 
            background-color: #f9f9f9;
        }
        .badge { 
            display: inline-block; 
            padding: 3px 8px; 
            background-color: #940000; 
            color: white; 
            border-radius: 3px; 
            font-size: 8.5pt; 
            margin: 2px;
        }
        .text-center { 
            text-align: center; 
        }
        .mb-2 { 
            margin-bottom: 10px; 
        }
        .photo-container {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%);
            border: 3px solid #940000;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .employee-photo {
            max-width: 150px;
            max-height: 180px;
            width: auto;
            height: auto;
            border: 3px solid #940000;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(148, 0, 0, 0.2);
            display: block;
            margin: 0 auto;
        }
        .photo-placeholder { 
            width: 150px; 
            height: 180px; 
            border: 3px solid #940000; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            border-radius: 6px;
            color: #999;
            font-size: 10pt;
            margin: 0 auto;
        }
        .personal-info-header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        .photo-cell {
            display: table-cell;
            width: 200px;
            vertical-align: top;
            padding-right: 25px;
        }
        .info-cell {
            display: table-cell;
            vertical-align: top;
            width: auto;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .amount {
            font-weight: bold;
            color: #940000;
        }
        .primary-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    @include('components.pdf-header', [
        'documentTitle' => 'Employee Registration Form',
        'documentRef' => 'EMP-' . $employee->employee_id,
        'documentDate' => $generated_at
    ])

    <div class="document-title">EMPLOYEE REGISTRATION FORM</div>

    <!-- Personal Information with Photo -->
    <div class="section">
        <div class="section-title">1. Personal Information</div>
        <div class="personal-info-header">
            <div class="photo-cell">
                <div class="photo-container">
                    @php
                    use Illuminate\Support\Facades\Storage;
                    $photoPath = null;
                    $photoBase64 = null;
                    $photoExtension = 'png';
                    
                    // Try multiple paths to find the photo
                    if ($employee->photo && !empty(trim($employee->photo))) {
                        $filename = trim($employee->photo);
                        
                        // List of possible paths to check
                        $possiblePaths = [
                            storage_path('app/public/photos/' . $filename),
                            public_path('storage/photos/' . $filename),
                            storage_path('app/private/public/photos/' . $filename),
                            base_path('storage/app/public/photos/' . $filename),
                        ];
                        
                        // Try each path
                        foreach ($possiblePaths as $testPath) {
                            if (file_exists($testPath) && is_file($testPath)) {
                                $photoPath = $testPath;
                                break;
                            }
                        }
                        
                        // Also try with Storage facade as fallback
                        if (!$photoPath || !file_exists($photoPath)) {
                            try {
                                if (Storage::disk('public')->exists('photos/' . $filename)) {
                                    $photoPath = Storage::disk('public')->path('photos/' . $filename);
                                } elseif (Storage::exists('public/photos/' . $filename)) {
                                    $photoPath = Storage::path('public/photos/' . $filename);
                                }
                            } catch (\Exception $e) {
                                // Continue to next method
                            }
                        }
                        
                        // If we found a valid path, load the image
                        if ($photoPath && file_exists($photoPath) && is_readable($photoPath)) {
                            try {
                                $photoData = @file_get_contents($photoPath);
                                if ($photoData !== false && strlen($photoData) > 0) {
                                    $photoBase64 = base64_encode($photoData);
                                    
                                    // Detect file extension
                                    $photoExtension = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
                                    
                                    // If no extension, try to detect from MIME type
                                    if (empty($photoExtension) && function_exists('finfo_open')) {
                                        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                                        if ($finfo) {
                                            $mimeType = @finfo_file($finfo, $photoPath);
                                            @finfo_close($finfo);
                                            
                                            if ($mimeType === 'image/jpeg') $photoExtension = 'jpeg';
                                            elseif ($mimeType === 'image/png') $photoExtension = 'png';
                                            elseif ($mimeType === 'image/gif') $photoExtension = 'gif';
                                            elseif ($mimeType === 'image/webp') $photoExtension = 'webp';
                                        }
                                    }
                                    
                                    // Normalize extension
                                    if (empty($photoExtension)) {
                                        $photoExtension = 'jpeg'; // default
                                    } else {
                                        $photoExtension = $photoExtension === 'jpg' ? 'jpeg' : $photoExtension;
                                    }
                                }
                            } catch (\Exception $e) {
                                // Silently fail and show placeholder
                                $photoBase64 = null;
                            }
                        }
                    }
                    @endphp
                    @if($photoBase64)
                        <img src="data:image/{{ $photoExtension }};base64,{{ $photoBase64 }}" 
                             alt="Employee Photo" 
                             class="employee-photo"
                             style="max-width: 150px; max-height: 180px; width: auto; height: auto; object-fit: contain;">
                    @else
                        <div class="photo-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div style="font-size: 24pt; color: #ccc; margin-bottom: 5px;">👤</div>
                            <div style="font-size: 9pt; color: #999;">No Photo Available</div>
                        </div>
                    @endif
                    <div style="margin-top: 10px; font-size: 9pt; color: #333; text-align: center; padding: 8px; background-color: #f9f9f9; border-radius: 4px;">
                        <strong style="color: #940000;">Employee ID</strong><br>
                        <span style="font-family: monospace; font-size: 10pt;">{{ $employee->employee_id ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="info-cell">
                <table class="info-table">
                    <tr>
                        <th>Full Name:</th>
                        <td><strong>{{ $employee->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $employee->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $employee->phone ?? ($employee->mobile ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth:</th>
                        <td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td>{{ $employee->gender ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Marital Status:</th>
                        <td>{{ ucfirst($employee->marital_status ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Nationality:</th>
                        <td>{{ $employee->nationality ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td>{{ $employee->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($employee->is_active)
                                <span class="status-active">● Active</span>
                            @else
                                <span class="status-inactive">● Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Employment Details -->
    <div class="section">
        <div class="section-title">2. Employment Details</div>
        <table class="info-table">
            <tr>
                <th>Position:</th>
                <td><strong>{{ $employee->employee->position ?? 'N/A' }}</strong></td>
                <th>Department:</th>
                <td><strong>{{ $employee->primaryDepartment->name ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <th>Employment Type:</th>
                <td>
                    <span class="badge">{{ ucfirst($employee->employee->employment_type ?? 'N/A') }}</span>
                </td>
                <th>Hire Date:</th>
                <td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Salary:</th>
                <td class="amount">{{ $employee->employee->salary ? 'TZS ' . number_format($employee->employee->salary, 2) : 'N/A' }}</td>
                <th>Years of Service:</th>
                <td>
                    @if($employee->hire_date)
                        {{ \Carbon\Carbon::parse($employee->hire_date)->diffInYears(now()) }} years
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <th>Roles:</th>
                <td colspan="3">
                    @if($employee->roles && $employee->roles->count() > 0)
                        @foreach($employee->roles as $role)
                            <span class="badge">{{ $role->display_name ?? $role->name }}</span>
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Emergency Contact -->
    <div class="section">
        <div class="section-title">3. Emergency Contact</div>
        <table class="info-table">
            <tr>
                <th>Contact Name:</th>
                <td><strong>{{ $employee->employee->emergency_contact_name ?? 'N/A' }}</strong></td>
                <th>Phone:</th>
                <td>{{ $employee->employee->emergency_contact_phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Relationship:</th>
                <td>{{ $employee->employee->emergency_contact_relationship ?? 'N/A' }}</td>
                <th>Address:</th>
                <td>{{ $employee->employee->emergency_contact_address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Family Information -->
    @if($employee->family && $employee->family->count() > 0)
    <div class="section">
        <div class="section-title">4. Family Information</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Occupation</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Dependent</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->family as $member)
                <tr>
                    <td><strong>{{ $member->name }}</strong></td>
                    <td>{{ $member->relationship }}</td>
                    <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $member->gender ?? 'N/A' }}</td>
                    <td>{{ $member->occupation ?? 'N/A' }}</td>
                    <td>{{ $member->phone ?? 'N/A' }}</td>
                    <td>{{ $member->email ?? 'N/A' }}</td>
                    <td>
                        @if($member->is_dependent)
                            <span class="primary-badge">Yes</span>
                        @else
                            No
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Next of Kin -->
    @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
    <div class="section">
        <div class="section-title">5. Next of Kin</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>ID Number</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->nextOfKin as $kin)
                <tr>
                    <td><strong>{{ $kin->name }}</strong></td>
                    <td>{{ $kin->relationship }}</td>
                    <td>{{ $kin->phone }}</td>
                    <td>{{ $kin->email ?? 'N/A' }}</td>
                    <td>{{ $kin->address }}</td>
                    <td>{{ $kin->id_number ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Referees -->
    @if($employee->referees && $employee->referees->count() > 0)
    <div class="section">
        <div class="section-title">6. Referees</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Organization</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Relationship</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->referees as $referee)
                <tr>
                    <td><strong>{{ $referee->name }}</strong></td>
                    <td>{{ $referee->position ?? 'N/A' }}</td>
                    <td>{{ $referee->organization ?? 'N/A' }}</td>
                    <td>{{ $referee->phone }}</td>
                    <td>{{ $referee->email ?? 'N/A' }}</td>
                    <td>{{ $referee->relationship ?? 'N/A' }}</td>
                    <td>{{ $referee->address ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Education -->
    @if($employee->educations && $employee->educations->count() > 0)
    <div class="section">
        <div class="section-title">7. Education Background</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Institution</th>
                    <th>Qualification</th>
                    <th>Field of Study</th>
                    <th>Start Year</th>
                    <th>End Year</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->educations as $education)
                <tr>
                    <td><strong>{{ $education->institution_name }}</strong></td>
                    <td>{{ $education->qualification }}</td>
                    <td>{{ $education->field_of_study ?? 'N/A' }}</td>
                    <td>{{ $education->start_year ?? 'N/A' }}</td>
                    <td>{{ $education->end_year ?? 'N/A' }}</td>
                    <td>{{ $education->grade ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Banking Information -->
    @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
    <div class="section">
        <div class="section-title">8. Banking Information</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th>Branch Name</th>
                    <th>SWIFT Code</th>
                    <th>Primary</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->bankAccounts as $account)
                <tr>
                    <td><strong>{{ $account->bank_name }}</strong></td>
                    <td><code>{{ $account->account_number }}</code></td>
                    <td>{{ $account->account_name ?? 'N/A' }}</td>
                    <td>{{ $account->branch_name ?? 'N/A' }}</td>
                    <td>{{ $account->swift_code ?? 'N/A' }}</td>
                    <td>
                        @if($account->is_primary)
                            <span class="primary-badge">Primary</span>
                        @else
                            Secondary
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Deductions -->
    @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
    <div class="section">
        <div class="section-title">9. Salary Deductions</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Frequency</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->salaryDeductions as $deduction)
                <tr>
                    <td><strong>{{ $deduction->deduction_type }}</strong></td>
                    <td>{{ $deduction->description ?? 'N/A' }}</td>
                    <td class="amount">TZS {{ number_format($deduction->amount, 2) }}</td>
                    <td>{{ ucfirst($deduction->frequency) }}</td>
                    <td>{{ \Carbon\Carbon::parse($deduction->start_date)->format('d M Y') }}</td>
                    <td>{{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('d M Y') : 'Ongoing' }}</td>
                    <td>
                        @if($deduction->is_active)
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Statutory Information -->
    <div class="section">
        <div class="section-title">10. Statutory Information</div>
        <table class="info-table">
            <tr>
                <th>TIN Number:</th>
                <td><code>{{ $employee->employee->tin_number ?? 'N/A' }}</code></td>
                <th>NSSF Number:</th>
                <td><code>{{ $employee->employee->nssf_number ?? 'N/A' }}</code></td>
            </tr>
            <tr>
                <th>NHIF Number:</th>
                <td><code>{{ $employee->employee->nhif_number ?? 'N/A' }}</code></td>
                <th>HESLB Number:</th>
                <td><code>{{ $employee->employee->heslb_number ?? 'N/A' }}</code></td>
            </tr>
            <tr>
                <th>Has Student Loan:</th>
                <td colspan="3">
                    @if($employee->employee->has_student_loan)
                        <span class="badge">Yes</span>
                    @else
                        <span class="badge" style="background-color: #28a745;">No</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Documents -->
    @if($employee->documents && $employee->documents->count() > 0)
    <div class="section">
        <div class="section-title">11. Documents</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Document Type</th>
                    <th>Document Name</th>
                    <th>Document Number</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Issued By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->documents as $document)
                <tr>
                    <td><strong>{{ $document->document_type }}</strong></td>
                    <td>{{ $document->document_name }}</td>
                    <td><code>{{ $document->document_number ?? 'N/A' }}</code></td>
                    <td>{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d M Y') : 'N/A' }}</td>
                    <td>
                        @if($document->expiry_date)
                            @php
                                $expiryDate = \Carbon\Carbon::parse($document->expiry_date);
                                $isExpired = $expiryDate->isPast();
                                $isExpiringSoon = $expiryDate->diffInDays(now()) <= 30;
                            @endphp
                            <span style="color: {{ $isExpired ? '#dc3545' : ($isExpiringSoon ? '#ffc107' : '#28a745') }};">
                                {{ $expiryDate->format('d M Y') }}
                            </span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $document->issued_by ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @include('components.pdf-footer')
</body>
</html>
