@extends('layouts.app')

@section('title', 'Submit Receipt for Imprest Assignment')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Submit Receipt for Imprest Assignment</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}">{{ $assignment->imprestRequest->request_no }}</a></li>
            <li class="breadcrumb-item active">Submit Receipt</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .file-upload-area:hover {
        border-color: #0d6efd;
        background: #e7f3ff;
    }
    
    .file-upload-area.dragover {
        border-color: #0d6efd;
        background: #e7f3ff;
    }
    
    .file-preview {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        background: #f8f9fa;
    }
    
    .info-card {
        border-left: 4px solid #0d6efd;
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Details
        </a>
    </div>

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h2 class="fw-bold mb-2 text-white">
                <i class="bx bx-upload me-2"></i>Submit Receipt for Imprest Assignment
            </h2>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Request Number:</strong> {{ $assignment->imprestRequest->request_no }}</p>
                    <p class="mb-1"><strong>Purpose:</strong> {{ $assignment->imprestRequest->purpose }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Assigned Amount:</strong> <strong>TZS {{ number_format($assignment->assigned_amount, 2) }}</strong></p>
                    <p class="mb-1"><strong>Staff Member:</strong> {{ $assignment->staff->name ?? 'Unknown' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Info -->
    <div class="card border-0 shadow-sm mb-4 info-card">
        <div class="card-body">
            <h5 class="mb-3"><i class="bx bx-info-circle me-2"></i>Assignment Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Assigned Amount:</strong> <span class="text-primary">TZS {{ number_format($assignment->assigned_amount, 2) }}</span></p>
                    <p class="mb-2"><strong>Assigned Date:</strong> {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Request Number:</strong> {{ $assignment->imprestRequest->request_no }}</p>
                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success">{{ ucwords(str_replace('_', ' ', $assignment->imprestRequest->status)) }}</span></p>
                </div>
            </div>
            @if($assignment->receipts->count() > 0)
            <div class="alert alert-info mt-3 mb-0">
                <i class="bx bx-info-circle me-2"></i>You have already submitted <strong>{{ $assignment->receipts->count() }}</strong> receipt(s) for this assignment. You can submit additional receipts if needed.
            </div>
            @endif
        </div>
    </div>

    <!-- Submit Receipt Form -->
    <form id="submitReceiptForm" method="POST" action="{{ route('imprest.submit-receipt') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-receipt me-2"></i>Receipt Details</h5>
            </div>
            <div class="card-body">
                <!-- Receipt Amount -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Receipt Amount <span class="text-danger">*</span>
                        <small class="text-muted">(TZS)</small>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="bx bx-money text-primary"></i>
                        </span>
                        <input 
                            type="number" 
                            class="form-control" 
                            name="receipt_amount" 
                            id="receiptAmount"
                            step="0.01" 
                            min="0" 
                            max="{{ $assignment->assigned_amount }}"
                            placeholder="0.00"
                            required
                        >
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="bx bx-info-circle"></i> Enter the total amount shown on your receipt (Maximum: TZS {{ number_format($assignment->assigned_amount, 2) }})
                    </small>
                </div>

                <!-- Receipt Description -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Receipt Description <span class="text-danger">*</span>
                    </label>
                    <textarea 
                        class="form-control" 
                        name="receipt_description" 
                        id="receiptDescription"
                        rows="4" 
                        placeholder="Brief description of what the receipt is for (e.g., Office supplies, Travel expenses, etc.)"
                        required
                    ></textarea>
                    <small class="text-muted mt-1 d-block">
                        <i class="bx bx-info-circle"></i> Provide a clear description of the expense
                    </small>
                </div>

                <!-- Receipt File Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Receipt File <span class="text-danger">*</span>
                    </label>
                    <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('receiptFile').click()">
                        <input 
                            type="file" 
                            class="d-none" 
                            name="receipt_file" 
                            id="receiptFile"
                            accept=".pdf,.jpg,.jpeg,.png"
                            required
                            onchange="previewReceiptFile(this)"
                        >
                        <div id="fileUploadPlaceholder">
                            <i class="bx bx-cloud-upload" style="font-size: 3rem; color: #6c757d;"></i>
                            <p class="mb-2 mt-2">
                                <strong>Click to upload</strong> or drag and drop
                            </p>
                            <p class="text-muted small mb-0">
                                PDF, JPG, PNG (Max 2MB)
                            </p>
                        </div>
                        <div id="filePreview" class="d-none mt-3">
                            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-file" style="font-size: 2rem; color: #0d6efd;" class="me-3"></i>
                                    <div>
                                        <strong id="fileName"></strong>
                                        <small class="text-muted d-block" id="fileSize"></small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFileInput()">
                                    <i class="bx bx-x me-1"></i>Remove
                                </button>
                            </div>
                            <div id="imagePreview" class="mt-3 text-center d-none">
                                <img id="previewImage" src="" alt="Receipt Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="bx bx-info-circle"></i> Upload a clear, readable image or PDF of your receipt
                    </small>
                </div>

                <!-- Validation Messages -->
                <div id="validationMessages" class="alert alert-warning d-none">
                    <i class="bx bx-error-circle"></i>
                    <span id="validationText"></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}" class="btn btn-secondary btn-lg">
                <i class="bx bx-x me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="bx bx-upload me-1"></i>Submit Receipt
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// File preview functionality
function previewReceiptFile(input) {
    const file = input.files[0];
    if (!file) return;
    
    // Check file size (2MB limit)
    if (file.size > 2 * 1024 * 1024) {
        showValidationMessage('File size exceeds 2MB limit. Please choose a smaller file.', true);
        input.value = '';
        clearFileInput();
        return;
    }
    
    // Hide validation message
    showValidationMessage('', false);
    
    // Show file preview
    document.getElementById('fileUploadPlaceholder').classList.add('d-none');
    document.getElementById('filePreview').classList.remove('d-none');
    
    // Set file name and size
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
    
    // Preview image if it's an image file
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.classList.add('d-none');
    }
}

function clearFileInput() {
    const fileInput = document.getElementById('receiptFile');
    const filePreview = document.getElementById('filePreview');
    const filePlaceholder = document.getElementById('fileUploadPlaceholder');
    const imagePreview = document.getElementById('imagePreview');
    
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.classList.add('d-none');
    if (filePlaceholder) filePlaceholder.classList.remove('d-none');
    if (imagePreview) imagePreview.classList.add('d-none');
    
    showValidationMessage('', false);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function showValidationMessage(message, show) {
    const validationDiv = document.getElementById('validationMessages');
    const validationText = document.getElementById('validationText');
    
    if (validationDiv && validationText) {
        if (show && message) {
            validationText.textContent = message;
            validationDiv.classList.remove('d-none');
        } else {
            validationDiv.classList.add('d-none');
        }
    }
}

// Drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('receiptFile');
    
    if (fileUploadArea && fileInput) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        fileUploadArea.addEventListener('drop', handleDrop, false);
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        fileUploadArea.classList.add('dragover');
    }
    
    function unhighlight(e) {
        fileUploadArea.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            previewReceiptFile(fileInput);
        }
    }
    
    // Form submission
    const form = document.getElementById('submitReceiptForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Validate receipt amount
            const receiptAmount = parseFloat(document.getElementById('receiptAmount').value);
            const maxAmount = parseFloat(document.getElementById('receiptAmount').getAttribute('max'));
            if (receiptAmount > maxAmount) {
                showValidationMessage('Receipt amount cannot exceed assigned amount (TZS ' + maxAmount.toLocaleString() + ')', true);
                return;
            }
            
            // Validate file
            const receiptFile = document.getElementById('receiptFile').files[0];
            if (!receiptFile) {
                showValidationMessage('Please select a receipt file to upload', true);
                return;
            }
            
            // Check file size (2MB limit)
            if (receiptFile.size > 2 * 1024 * 1024) {
                showValidationMessage('File size exceeds 2MB limit. Please choose a smaller file.', true);
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
            
            const formData = new FormData(form);
            
            fetch('{{ route("imprest.submit-receipt") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const result = await response.json();
                
                if (response.ok && result.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', result.message || 'Receipt submitted successfully!', { duration: 5000 });
                    } else {
                        alert('Receipt submitted successfully!');
                    }
                    
                    // Redirect to imprest details page
                    setTimeout(() => {
                        window.location.href = '{{ route("imprest.show", $assignment->imprestRequest->id) }}';
                    }, 1000);
                } else {
                    let errorMsg = result.message || 'Failed to submit receipt.';
                    
                    if (result.errors) {
                        const errorMessages = [];
                        Object.keys(result.errors).forEach(key => {
                            if (Array.isArray(result.errors[key])) {
                                result.errors[key].forEach(err => {
                                    errorMessages.push(`${key}: ${err}`);
                                });
                            } else {
                                errorMessages.push(`${key}: ${result.errors[key]}`);
                            }
                        });
                        if (errorMessages.length > 0) {
                            errorMsg = errorMessages.join('\n');
                        }
                    }
                    
                    showValidationMessage(errorMsg, true);
                    
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                showValidationMessage(errorMsg, true);
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>
@endpush







