@extends('layouts.app')

@section('title', 'Attendance Policies')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-shield"></i> Attendance Policies
                </h4>
                <p class="text-muted">Set attendance policies, rules, and approval workflows</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <button type="button" class="btn btn-primary" onclick="openPolicyModal()">
                    <i class="bx bx-plus me-1"></i> Add Policy
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .border-left-warning { border-left: 4px solid #ffc107; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Policies</h6>
                            <h3 class="mb-0" id="statTotalPolicies">{{ $stats['total_policies'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-shield fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Policies</h6>
                            <h3 class="mb-0 text-success" id="statActivePolicies">{{ $stats['active_policies'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Policies Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Policies List
                    </h6>
                </div>
                <div class="card-body">
                    @include('modules.hr.attendance-settings.partials.policies')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Policy Modal -->
@include('modules.hr.attendance-settings.modals.policy-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const policiesData = @json($policies ?? []);

document.addEventListener('DOMContentLoaded', function() {
    loadPolicies();
});

function loadPolicies() {
    const tbody = document.getElementById('policiesList');
    if (!tbody) return;

    if (!policiesData || policiesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No policies found</p></td></tr>';
        return;
    }
    
    tbody.innerHTML = policiesData.map(policy => `
        <tr>
            <td><strong>${policy.name || 'N/A'}</strong></td>
            <td><code>${policy.code || 'N/A'}</code></td>
            <td>${policy.location?.name || 'All'}</td>
            <td>${policy.department?.name || 'All'}</td>
            <td>${policy.allow_remote_attendance ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
            <td>${policy.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editPolicy(${policy.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePolicy(${policy.id}, '${policy.name || ''}')" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openPolicyModal(policyId = null) {
    // Open policy modal logic
    console.log('Open policy modal', policyId);
}

function editPolicy(id) {
    openPolicyModal(id);
}

function deletePolicy(id, name) {
    Swal.fire({
        title: 'Delete Policy',
        html: 'Are you sure you want to delete <strong>' + name + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/attendance-settings/policies/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadPolicies();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete policy', 'error');
            });
        }
    });
}
</script>
@endpush









