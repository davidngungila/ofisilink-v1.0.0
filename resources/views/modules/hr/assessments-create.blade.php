@extends('layouts.app')

@section('title', 'Create New Assessment - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bx bx-plus-circle me-2"></i>Create New Assessment
                            </h4>
                            <p class="mb-0 text-muted">Define your main responsibility and activities</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Assessment Information</h5>
                </div>
                <div class="card-body">
                    <form id="create-assessment-form">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Main Responsibility Title <span class="text-danger">*</span></label>
                            <input type="text" name="main_responsibility" class="form-control" required placeholder="e.g., Customer Service Management">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide a detailed description of this responsibility..."></textarea>
                        </div>
                        
                        <input type="hidden" name="contribution_percentage" value="100">

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Sub Responsibilities / Activities</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="add-activity">
                                <i class="bx bx-plus"></i> Add Activity
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="activities-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%">Activity Name <span class="text-danger">*</span></th>
                                        <th style="width:45%">Description</th>
                                        <th style="width:20%">Frequency <span class="text-danger">*</span></th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i> Contribution percentage will be automatically distributed equally among all activities.
                        </small>
                        
                        <div class="alert alert-danger d-none mt-3" id="create-error"></div>
                        <div class="alert alert-success d-none mt-3" id="create-success"></div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bx bx-check me-2"></i>Submit for HOD Approval
                            </button>
                            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const tableBody = document.querySelector('#activities-table tbody');
    const addBtn = document.getElementById('add-activity');
    const form = document.getElementById('create-assessment-form');
    const errBox = document.getElementById('create-error');
    const okBox = document.getElementById('create-success');

    function addRow(){
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="activities[][activity_name]" class="form-control form-control-sm" required placeholder="Activity name"></td>
            <td><input type="text" name="activities[][description]" class="form-control form-control-sm" placeholder="Description"></td>
            <td>
                <select name="activities[][reporting_frequency]" class="form-select form-select-sm" required>
                    <option value="">-- Select --</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                </select>
            </td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
        `;
        tableBody.appendChild(tr);
        tr.querySelector('.remove-row').addEventListener('click', function(){ tr.remove(); });
    }

    addBtn.addEventListener('click', addRow);

    form.addEventListener('submit', function(e){
        e.preventDefault();
        errBox.classList.add('d-none'); errBox.textContent='';
        okBox.classList.add('d-none'); okBox.textContent='';

        const url = "{{ route('assessments.store') }}";

        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const activities = [];
        rows.forEach(function(tr){
            const name = tr.querySelector('input[name="activities[][activity_name]"]').value.trim();
            const desc = tr.querySelector('input[name="activities[][description]"]').value.trim();
            const freq = tr.querySelector('select[name="activities[][reporting_frequency]"]').value;
            if (name !== '') {
                activities.push({
                    activity_name: name,
                    description: desc || null,
                    reporting_frequency: freq
                });
            }
        });

        if (activities.length === 0) {
            errBox.textContent = 'Add at least one activity and fill its name.';
            errBox.classList.remove('d-none');
            return;
        }

        const payload = {
            main_responsibility: form.querySelector('input[name="main_responsibility"]').value,
            description: form.querySelector('textarea[name="description"]').value,
            contribution_percentage: form.querySelector('input[name="contribution_percentage"]').value,
            activities: activities
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

        fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 
                'Accept': 'application/json', 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(payload)
        }).then(async (res)=>{
            const data = await res.json().catch(()=>({success:false,message:'Unexpected response'}));
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to submit');
            }
            okBox.textContent = data.message || 'Submitted successfully';
            okBox.classList.remove('d-none');
            setTimeout(()=>{ window.location.href = "{{ route('modules.hr.assessments') }}"; }, 1500);
        }).catch((e)=>{
            errBox.textContent = e.message || 'Failed to submit';
            errBox.classList.remove('d-none');
        }).finally(()=>{
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush






