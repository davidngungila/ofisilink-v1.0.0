@extends('layouts.app')

@section('title', 'Add Fixed Asset')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Add Fixed Asset</h4>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">New Fixed Asset</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('modules.accounting.fixed-assets.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Asset Code <span class="text-danger">*</span></label>
                                <input type="text" name="asset_code" class="form-control @error('asset_code') is-invalid @enderror" 
                                       value="{{ old('asset_code') }}" required>
                                @error('asset_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Serial Number</label>
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Assigned To</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-primary">Purchase Information</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       value="{{ old('purchase_date') }}" required>
                                @error('purchase_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Purchase Cost <span class="text-danger">*</span></label>
                                <input type="number" name="purchase_cost" step="0.01" min="0" 
                                       class="form-control @error('purchase_cost') is-invalid @enderror" 
                                       value="{{ old('purchase_cost') }}" required>
                                @error('purchase_cost')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Additional Costs</label>
                                <input type="number" name="additional_costs" step="0.01" min="0" 
                                       class="form-control" value="{{ old('additional_costs', 0) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>PO Number</label>
                                <input type="text" name="purchase_order_number" class="form-control" value="{{ old('purchase_order_number') }}">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-primary">Depreciation Information</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Depreciation Method <span class="text-danger">*</span></label>
                                <select name="depreciation_method" class="form-control @error('depreciation_method') is-invalid @enderror" required>
                                    <option value="Straight Line" {{ old('depreciation_method') == 'Straight Line' ? 'selected' : '' }}>Straight Line</option>
                                    <option value="Declining Balance" {{ old('depreciation_method') == 'Declining Balance' ? 'selected' : '' }}>Declining Balance</option>
                                    <option value="Sum of Years Digits" {{ old('depreciation_method') == 'Sum of Years Digits' ? 'selected' : '' }}>Sum of Years Digits</option>
                                    <option value="Units of Production" {{ old('depreciation_method') == 'Units of Production' ? 'selected' : '' }}>Units of Production</option>
                                </select>
                                @error('depreciation_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Useful Life (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="useful_life_years" min="1" 
                                       class="form-control @error('useful_life_years') is-invalid @enderror" 
                                       value="{{ old('useful_life_years', 5) }}" required>
                                @error('useful_life_years')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Depreciation Rate (%)</label>
                                <input type="number" name="depreciation_rate" step="0.01" min="0" max="100" 
                                       class="form-control" value="{{ old('depreciation_rate') }}">
                                <small class="form-text text-muted">Leave empty for auto-calculation (Straight Line)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Salvage Value</label>
                                <input type="number" name="salvage_value" step="0.01" min="0" 
                                       class="form-control" value="{{ old('salvage_value', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Depreciation Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="depreciation_start_date" 
                                       class="form-control @error('depreciation_start_date') is-invalid @enderror" 
                                       value="{{ old('depreciation_start_date') }}" required>
                                @error('depreciation_start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Useful Life (Units)</label>
                                <input type="number" name="useful_life_units" min="0" 
                                       class="form-control" value="{{ old('useful_life_units') }}">
                                <small class="form-text text-muted">For Units of Production method</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-primary">Accounting Integration</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Asset Account</label>
                                <select name="asset_account_id" class="form-control">
                                    <option value="">Select Account</option>
                                    @foreach($assetAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('asset_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Depreciation Expense Account</label>
                                <select name="depreciation_expense_account_id" class="form-control">
                                    <option value="">Select Account</option>
                                    @foreach($expenseAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('depreciation_expense_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Accumulated Depreciation Account</label>
                                <select name="accumulated_depreciation_account_id" class="form-control">
                                    <option value="">Select Account</option>
                                    @foreach($assetAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('accumulated_depreciation_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="create_journal_entry" class="form-check-input" value="1" {{ old('create_journal_entry') ? 'checked' : '' }}>
                                    <label class="form-check-label">Create Journal Entry for Asset Purchase</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-primary">Additional Information</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Warranty Period</label>
                                <input type="text" name="warranty_period" class="form-control" 
                                       placeholder="e.g., 1 year, 24 months" value="{{ old('warranty_period') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Asset
                        </button>
                        <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




