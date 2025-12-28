<style>
/* Ensure SweetAlert2 appears above all modals - HIGHEST PRIORITY */
.swal2-container {
  z-index: 999999 !important;
}

.swal2-container.swal2-backdrop-show {
  z-index: 999999 !important;
}

.swal2-popup {
  z-index: 1000000 !important;
}

/* Ensure SweetAlert backdrop is above modal backdrops */
body.swal2-shown .swal2-container {
  z-index: 999999 !important;
}

body.swal2-shown .swal2-backdrop-show {
  z-index: 999998 !important;
}

/* When modal is open and SweetAlert is shown, ensure SweetAlert is on top */
.modal.show ~ .swal2-container,
.swal2-container {
  z-index: 999999 !important;
}

/* Override any modal z-index when SweetAlert is shown */
body.swal2-shown .modal {
  z-index: 100000 !important;
}

body.swal2-shown .modal-backdrop {
  z-index: 999998 !important;
}

/* Ensure SweetAlert appears above verify receipt modal specifically */
body.swal2-shown #verifyReceiptModal {
  z-index: 100000 !important;
}

body.swal2-shown #verifyReceiptModal + .modal-backdrop {
  z-index: 999998 !important;
}

/* Force SweetAlert to be on top when opened from within a modal */
.swal2-container.swal2-shown {
  z-index: 999999 !important;
}

.swal2-popup.swal2-show {
  z-index: 1000000 !important;
}
</style>

<script>
// Toast notification
function showToast(message, type = 'success') {
  const bgClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
  const toast = $('<div class="alert ' + bgClass + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002; min-width: 300px;"><strong>' + message + '</strong><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
  $('body').append(toast);
  setTimeout(() => toast.fadeOut(() => toast.remove()), 4000);
}

// New Imprest Form Submission - Make it available globally
window.submitNewImprest = function() {
  const form = document.getElementById('newImprestForm');
  if (!form) {
    console.error('Form not found');
    return;
  }
  
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  
  // Disable submit button
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
  }

  fetch('{{ route("imprest.store") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token'),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(err => { throw err; });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Imprest request created successfully!', 'success');
      const modalEl = document.getElementById('newImprestModal');
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast(data.message || 'Error creating imprest request', 'error');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Submit Request';
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    let errorMsg = 'Error creating imprest request. Please try again.';
    
    if (error.message) {
      errorMsg = error.message;
    } else if (error.error) {
      errorMsg = error.error;
    } else if (typeof error === 'object' && error.errors) {
      // Handle Laravel validation errors
      const errors = Object.values(error.errors).flat();
      errorMsg = errors.join(', ');
    }
    
    showToast(errorMsg, 'error');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Submit Request';
    }
  });
};

// Bind form submission with jQuery when document is ready
$(document).ready(function() {
  // Use event delegation for dynamically loaded modals
  $(document).on('submit', '#newImprestForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (typeof window.submitNewImprest === 'function') {
      window.submitNewImprest();
    } else {
      console.error('submitNewImprest function not available');
      showToast('Form submission error. Please refresh the page.', 'error');
    }
    return false;
  });
  
  // Check if we need to redirect to an imprest from notification
  @if(session('openImprestId'))
    setTimeout(function() {
      window.location.href = '{{ route("imprest.show", ["id" => session("openImprestId")]) }}';
    }, 100); // Small delay to ensure page is fully loaded
  @endif
});

// Assign Staff - Redirect to dedicated page
function openAssignStaff(id) {
  window.location.href = '{{ route("imprest.assign-staff.page", ":id") }}'.replace(':id', id);
}

// Assign Staff form submission is now handled on the dedicated page

// HOD Approve
function hodApprove(id) {
  Swal.fire({
    title: 'Approve Imprest Request?',
    text: 'This will forward the request to CEO for final approval.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel',
    didOpen: () => {
      // Force highest z-index when SweetAlert opens
      const swalContainer = document.querySelector('.swal2-container');
      if (swalContainer) {
        swalContainer.style.zIndex = '999999';
      }
      const swalPopup = document.querySelector('.swal2-popup');
      if (swalPopup) {
        swalPopup.style.zIndex = '1000000';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '{{ route("imprest.hod-approve", ":id") }}'.replace(':id', id),
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
          if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(res.message || 'Error approving', 'error');
          }
        },
        error: function() {
          showToast('Error approving request', 'error');
        }
      });
    }
  });
}

// CEO Approve
function ceoApprove(id) {
  Swal.fire({
    title: 'Give Final Approval?',
    text: 'This will approve the request for staff assignment and payment.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel',
    didOpen: () => {
      // Force highest z-index when SweetAlert opens
      const swalContainer = document.querySelector('.swal2-container');
      if (swalContainer) {
        swalContainer.style.zIndex = '999999';
      }
      const swalPopup = document.querySelector('.swal2-popup');
      if (swalPopup) {
        swalPopup.style.zIndex = '1000000';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '{{ route("imprest.ceo-approve", ":id") }}'.replace(':id', id),
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
          if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(res.message || 'Error approving', 'error');
          }
        },
        error: function() {
          showToast('Error approving request', 'error');
        }
      });
    }
  });
}

// Payment - Redirect to dedicated page
function openPayment(id) {
  window.location.href = '{{ route("imprest.payment.page", ":id") }}'.replace(':id', id);
}

// Payment form submission is now handled on the dedicated page

// View My Receipts - Redirect to dedicated page
function viewMyReceipts(assignmentId) {
  window.location.href = '{{ route("imprest.my-receipts.page", ":assignmentId") }}'.replace(':assignmentId', assignmentId);
}

// Submit Receipt - Redirect to dedicated page
function openSubmitReceipt(assignmentId) {
  window.location.href = '{{ route("imprest.submit-receipt.page", ":assignmentId") }}'.replace(':assignmentId', assignmentId);
}

// Submit Receipt form submission is now handled on the dedicated page

// Verify Receipt - Redirect to show page
function viewReceiptsForVerification(imprestId) {
  window.location.href = '{{ route("imprest.show", ":id") }}'.replace(':id', imprestId);
}

// Open Verify Receipt Modal
function openVerifyReceipt(receiptId) {
  // Load receipt details
  fetch(`{{ route("imprest.receipts.details", ":id") }}`.replace(':id', receiptId))
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const receipt = data.receipt;
        
        // Set receipt ID
        $('#verifyReceiptId').val(receipt.id);
        
        // Build receipt details HTML
        let html = `
          <div class="receipt-verification-details">
            <div class="card mb-3">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fa fa-file-invoice"></i> Receipt Information</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Request Number:</label>
                    <p class="mb-0"><strong>${receipt.imprest_request.request_no}</strong></p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Purpose:</label>
                    <p class="mb-0">${receipt.imprest_request.purpose || 'N/A'}</p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Staff Member:</label>
                    <p class="mb-0"><strong>${receipt.assignment.staff_name}</strong></p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Assigned Amount:</label>
                    <p class="mb-0"><strong>TZS ${parseFloat(receipt.assignment.assigned_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card mb-3">
              <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fa fa-receipt"></i> Receipt Details</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Receipt Amount:</label>
                    <p class="mb-0"><strong class="text-primary">TZS ${parseFloat(receipt.receipt_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Description:</label>
                    <p class="mb-0">${receipt.receipt_description || 'N/A'}</p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Submitted By:</label>
                    <p class="mb-0">${receipt.submitted_by}</p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="text-muted small">Submitted Date:</label>
                    <p class="mb-0">${receipt.submitted_at || 'N/A'}</p>
                  </div>
                  <div class="col-12 mb-2">
                    <label class="text-muted small">Receipt File:</label>
                    <p class="mb-0">
                      <a href="/storage/${receipt.receipt_file_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-download"></i> View Receipt File
                      </a>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label for="verification_notes" class="form-label"><strong>Verification Notes (Optional):</strong></label>
              <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3" placeholder="Add any notes about this verification...">${receipt.verification_notes || ''}</textarea>
              <small class="form-text text-muted">You can add notes explaining your verification decision (approve/reject).</small>
            </div>
          </div>
        `;
        
        // Update modal content
        $('#verifyReceiptContent').html(html);
        
        // Show modal and ensure it's on top - HIGHEST PRIORITY
        var modalElement = document.getElementById('verifyReceiptModal');
        if (modalElement) {
            // Move modal to end of body to ensure it's on top
            $('body').append($(modalElement));
            
            // Hide any other open modals temporarily
            $('.modal.show').not(modalElement).each(function() {
                $(this).css('z-index', 9999);
            });
            
            // Remove any existing backdrops first
            $('.modal-backdrop').remove();
            
            // Set z-index immediately before showing
            $(modalElement).css({
                'z-index': '100000',
                'position': 'relative'
            });
            
            var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
            
            // Force modal to front with multiple checks
            setTimeout(function() {
                // Ensure modal is at the very front
                $(modalElement).css({
                    'z-index': '100000',
                    'display': 'block',
                    'position': 'fixed'
                }).addClass('show');
                
                // Create backdrop with proper z-index
                if ($('.modal-backdrop').length === 0) {
                    $('body').append('<div class="modal-backdrop fade show" style="z-index: 99999;"></div>');
                } else {
                    $('.modal-backdrop').last().css('z-index', 99999);
                }
                
                // Ensure body class is set
                $('body').addClass('modal-open');
                
                // Force modal dialog to front
                $(modalElement).find('.modal-dialog').css({
                    'z-index': '100001',
                    'position': 'relative'
                });
                
                // Final check - ensure modal is visible and on top
                $(modalElement).css('z-index', '100000');
            }, 10);
            
            // Additional check after animation
            setTimeout(function() {
                $(modalElement).css('z-index', '100000');
                $('.modal-backdrop').last().css('z-index', 99999);
            }, 300);
        }
      } else {
        showToast(data.message || 'Error loading receipt details', 'error');
      }
    })
    .catch(err => {
      console.error('Error loading receipt details:', err);
      showToast('Error loading receipt details. Please try again.', 'error');
    });
}

function verifyReceiptAction(action) {
  const receiptId = $('#verifyReceiptId').val();
  const notes = $('#verification_notes').val() || '';
  
  if (!receiptId) {
    showToast('Receipt ID is missing. Please try again.', 'error');
    return;
  }
  
  // Show confirmation with highest z-index to appear above all modals
  const actionText = action === 'approve' ? 'approve' : 'reject';
  const confirmMessage = action === 'approve' 
    ? 'Are you sure you want to approve this receipt?'
    : 'Are you sure you want to reject this receipt?';
  
  // Ensure SweetAlert appears above all modals
  Swal.fire({
    title: 'Confirm Verification',
    text: confirmMessage,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: `Yes, ${actionText}`,
    cancelButtonText: 'Cancel',
    confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
    customClass: {
      container: 'swal2-container-high-z',
      popup: 'swal2-popup-high-z'
    },
    didOpen: () => {
      // Force highest z-index when SweetAlert opens
      const swalContainer = document.querySelector('.swal2-container');
      if (swalContainer) {
        swalContainer.style.zIndex = '999999';
      }
      const swalPopup = document.querySelector('.swal2-popup');
      if (swalPopup) {
        swalPopup.style.zIndex = '1000000';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '{{ route("imprest.receipts.verify", ":id") }}'.replace(':id', receiptId),
        method: 'POST',
        data: {action: action, verification_notes: notes},
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
          if (res.success) {
            showToast(res.message, 'success');
            $('#verifyReceiptModal').modal('hide');
            if (res.all_verified) {
              showToast('All receipts verified! Imprest request completed.', 'success');
            }
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(res.message || 'Error verifying receipt', 'error');
          }
        },
        error: function(xhr) {
          let errorMsg = 'Error verifying receipt';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          }
          showToast(errorMsg, 'error');
        }
      });
    }
  });
}

// View Details - Redirect to dedicated page
function viewDetails(id) {
  window.location.href = '{{ route("imprest.show", ":id") }}'.replace(':id', id);
}

// View Receipts for Assignment (Accountant/Admin view) - Redirect to dedicated page
function viewReceipts(assignmentId) {
  window.location.href = '{{ route("imprest.view-receipts.page", ":assignmentId") }}'.replace(':assignmentId', assignmentId);
}

// View Receipt Details for Staff
function viewReceiptDetails(assignmentId) {
  // Load assignment details with receipts
  fetch(`{{ url('imprest/assignment') }}/${assignmentId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const assignment = data.assignment;
        let html = `
          <div class="card mb-3">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">Assignment Details</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Request Number:</strong> ${assignment.imprest_request?.request_no || '-'}</p>
                  <p><strong>Amount Assigned:</strong> TZS ${parseFloat(assignment.assigned_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Assigned Date:</strong> ${assignment.assigned_at ? new Date(assignment.assigned_at).toLocaleDateString() : '-'}</p>
                  <p><strong>Receipt Submitted:</strong> ${assignment.receipt_submitted ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>'}</p>
                </div>
              </div>
            </div>
          </div>
        `;
        
        if (assignment.receipts && assignment.receipts.length > 0) {
          html += `
            <div class="card">
              <div class="card-header bg-info text-white">
                <h5 class="mb-0">Submitted Receipts (${assignment.receipts.length})</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
          `;
          
          assignment.receipts.forEach(receipt => {
            const statusBadge = receipt.is_verified 
              ? '<span class="badge bg-success">Verified</span>' 
              : '<span class="badge bg-warning">Pending Verification</span>';
            const submittedDate = receipt.submitted_at 
              ? new Date(receipt.submitted_at).toLocaleDateString() 
              : '-';
            
            html += `
              <tr>
                <td><strong>TZS ${parseFloat(receipt.receipt_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                <td>${receipt.receipt_description || '-'}</td>
                <td>${submittedDate}</td>
                <td>${statusBadge}</td>
                <td>
                  ${receipt.receipt_file_path ? `<a href="/storage/${receipt.receipt_file_path}" target="_blank" class="btn btn-sm btn-info"><i class="bx bx-download"></i> View Receipt</a>` : '-'}
                </td>
              </tr>
            `;
          });
          
          html += `
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          `;
        } else {
          html += `
            <div class="alert alert-info">
              <i class="bx bx-info-circle"></i> No receipts have been submitted yet for this assignment.
            </div>
          `;
        }
        
        $('#viewDetailsContent').html(html);
        
        // Show modal and ensure it's on top
        var modalElement = document.getElementById('viewDetailsModal');
        if (modalElement) {
          var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
          modal.show();
          
          // Ensure z-index is set correctly
          setTimeout(function() {
            $(modalElement).css('z-index', 99999);
            $('.modal-backdrop').last().css('z-index', 99998);
          }, 10);
        }
      } else {
        showToast('Error loading receipt details', 'error');
      }
    })
    .catch(err => {
      console.error('Error loading receipt details:', err);
      showToast('Error loading receipt details', 'error');
    });
}
</script>

