@extends('layouts.app')

@section('title', 'Edit Fixed Asset')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Edit Fixed Asset</h4>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Fixed Asset: {{ $asset->asset_code }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('modules.accounting.fixed-assets.update', $asset->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $asset->category_id == $category->id ? 'selected' : '' }}>
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
                                       value="{{ old('asset_code', $asset->asset_code) }}" required>
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
                                       value="{{ old('name', $asset->name) }}" required>
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
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $asset->serial_number) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer', $asset->manufacturer) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control" value="{{ old('model', $asset->model) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $asset->location) }}">
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
                                    <option value="{{ $dept->id }}" {{ $asset->department_id == $dept->id ? 'selected' : '' }}>
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
                                    <option value="{{ $user->id }}" {{ $asset->assigned_to == $user->id ? 'selected' : '' }}>
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
                                       value="{{ old('purchase_date', $asset->purchase_date->format('Y-m-d')) }}" required>
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
                                       value="{{ old('purchase_cost', $asset->purchase_cost) }}" required>
                                @error('purchase_cost')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Additional Costs</label>
                                <input type="number" name="additional_costs" step="0.01" min="0" 
                                       class="form-control" value="{{ old('additional_costs', $asset->additional_costs) }}">
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
                                    <option value="{{ $vendor->id }}" {{ $asset->vendor_id == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $asset->invoice_number) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>PO Number</label>
                                <input type="text" name="purchase_order_number" class="form-control" value="{{ old('purchase_order_number', $asset->purchase_order_number) }}">
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
                                       value="{{ old('warranty_period', $asset->warranty_period) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" class="form-control" 
                                       value="{{ old('warranty_expiry', $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $asset->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $asset->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Asset
                        </button>
                        <a href="{{ route('modules.accounting.fixed-assets.show', $asset->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




