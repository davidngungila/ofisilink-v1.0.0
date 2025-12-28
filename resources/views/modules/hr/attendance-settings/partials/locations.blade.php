<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bx bx-map text-primary"></i> Attendance Locations
            </h5>
            <button type="button" class="btn btn-primary" onclick="openLocationModal()">
                <i class="bx bx-plus me-1"></i> Add Location
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="locationsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Address</th>
                                <th>GPS Radius</th>
                                <th>Methods</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="locationsList">
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
function loadLocations() {
    fetch('{{ route("modules.hr.attendance.settings") }}/locations', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            displayLocations(data.locations);
        }
    });
}

function displayLocations(locations) {
    const tbody = document.getElementById('locationsList');
    if (locations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No locations found</td></tr>';
        return;
    }
    
    tbody.innerHTML = locations.map(loc => `
        <tr>
            <td>${loc.name}</td>
            <td><code>${loc.code}</code></td>
            <td>${loc.address || 'N/A'}</td>
            <td>${loc.radius_meters || 100}m</td>
            <td>${(loc.allowed_methods || []).join(', ') || 'All'}</td>
            <td>${loc.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editLocation(${loc.id})">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteLocation(${loc.id})">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openLocationModal() {
    // Open location modal
    alert('Location modal will be implemented');
}

function editLocation(id) {
    // Edit location
    alert('Edit location: ' + id);
}

function deleteLocation(id) {
    Swal.fire({
        title: 'Delete Location?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route("modules.hr.attendance.settings") }}/locations/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadLocations();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}
</script>









