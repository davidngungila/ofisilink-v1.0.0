




<!-- Enhanced Submit Receipt Modal -->
<div class="modal fade" id="submitReceiptModal" tabindex="-1" aria-labelledby="submitReceiptModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white" id="submitReceiptModalLabel">
          <i class="bx bx-upload me-2"></i> Submit Receipt
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="submitReceiptForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="assignment_id" id="receiptAssignmentId">
        <div class="modal-body p-4" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden;">
          <!-- Assignment Info Card -->
          <div class="card bg-light mb-4" id="assignmentInfoCard">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <small class="text-muted d-block">Assigned Amount</small>
                  <h5 class="mb-0 text-primary" id="assignedAmountDisplay">TZS 0.00</h5>
                </div>
                <div class="col-md-6">
                  <small class="text-muted d-block">Request Number</small>
                  <h6 class="mb-0" id="requestNoDisplay">-</h6>
                </div>
              </div>
            </div>
          </div>

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
                placeholder="0.00"
                required
              >
            </div>
            <small class="text-muted mt-1 d-block">
              <i class="bx bx-info-circle"></i> Enter the total amount shown on your receipt
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
            <div class="file-upload-area border-2 border-dashed rounded p-4 text-center" id="fileUploadArea">
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
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-lg text-white" id="submitReceiptBtn">
            <i class="bx bx-upload me-1"></i> Submit Receipt
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#submitReceiptModal {
    z-index: 99999 !important;
}

#submitReceiptModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#submitReceiptModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#submitReceiptModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#submitReceiptModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#submitReceiptModal .modal-header,
#submitReceiptModal .modal-footer {
    flex-shrink: 0;
}

#submitReceiptModal .file-upload-area {
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

#submitReceiptModal .file-upload-area:hover {
    background: #e9ecef;
    border-color: #0d6efd !important;
}

#submitReceiptModal .file-upload-area.dragover {
    background: #e7f3ff;
    border-color: #0d6efd !important;
}

#submitReceiptModal .form-control:focus,
#submitReceiptModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#submitReceiptModal .input-group-lg .form-control {
    font-size: 1.1rem;
}

@media (max-width: 991.98px) {
    #submitReceiptModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #submitReceiptModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<script>
function previewReceiptFile(input) {
  const file = input.files[0];
  if (!file) return;

  const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
  const fileName = file.name;
  const fileType = file.type;

  // Show preview
  document.getElementById('fileUploadPlaceholder').classList.add('d-none');
  document.getElementById('filePreview').classList.remove('d-none');
  document.getElementById('fileName').textContent = fileName;
  document.getElementById('fileSize').textContent = fileSize + ' MB';

  // Show image preview if it's an image
  if (fileType.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('previewImage').src = e.target.result;
      document.getElementById('imagePreview').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
  } else {
    document.getElementById('imagePreview').classList.add('d-none');
  }

  // Validate file size
  if (file.size > 2 * 1024 * 1024) {
    document.getElementById('validationMessages').classList.remove('d-none');
    document.getElementById('validationText').textContent = 'File size exceeds 2MB. Please choose a smaller file.';
    input.value = '';
    clearFileInput();
    return;
  }

  document.getElementById('validationMessages').classList.add('d-none');
}

function clearFileInput() {
  document.getElementById('receiptFile').value = '';
  document.getElementById('fileUploadPlaceholder').classList.remove('d-none');
  document.getElementById('filePreview').classList.add('d-none');
  document.getElementById('imagePreview').classList.add('d-none');
  document.getElementById('validationMessages').classList.add('d-none');
}

// Drag and drop functionality
const fileUploadArea = document.getElementById('fileUploadArea');
if (fileUploadArea) {
  fileUploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
  });

  fileUploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
  });

  fileUploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
      document.getElementById('receiptFile').files = e.dataTransfer.files;
      previewReceiptFile(document.getElementById('receiptFile'));
    }
  });

  fileUploadArea.addEventListener('click', function() {
    document.getElementById('receiptFile').click();
  });
}

// Assignment loading is handled in scripts.blade.php
</script>
