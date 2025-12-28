<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bx bx-shield text-primary"></i> Attendance Policies
            </h5>
            <button type="button" class="btn btn-primary" onclick="openPolicyModal()">
                <i class="bx bx-plus me-1"></i> Add Policy
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="policiesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Remote Allowed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="policiesList">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadPolicies() {
    const policies = settingsData.policies || [];
    displayPolicies(policies);
}

function displayPolicies(policies) {
    const tbody = document.getElementById('policiesList');
    if (policies.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No policies found</td></tr>';
        return;
    }
    
    tbody.innerHTML = policies.map(policy => `
        <tr>
            <td>${policy.name}</td>
            <td><code>${policy.code}</code></td>
            <td>${policy.location?.name || 'All'}</td>
            <td>${policy.department?.name || 'All'}</td>
            <td>${policy.allow_remote_attendance ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
            <td>${policy.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editPolicy(${policy.id})">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePolicy(${policy.id})">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openPolicyModal() {
    alert('Policy modal will be implemented');
}

function editPolicy(id) {
    alert('Edit policy: ' + id);
}

function deletePolicy(id) {
    Swal.fire({
        title: 'Delete Policy?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete policy
            Swal.fire('Deleted!', 'Policy deleted successfully', 'success');
            loadPolicies();
        }
    });
}
</script>









