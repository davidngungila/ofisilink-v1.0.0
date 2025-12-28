<script>
// Helper function to extract ID from various input types
function extractVoucherId(id) {
  // If it's already a number, return it
  if (typeof id === 'number' && !isNaN(id)) {
    return id;
  }
  
  let voucherId = id;
  
  // Handle different input types
  if (typeof id === 'object' && id !== null) {
    if (id.value !== undefined && id.value !== null) {
      voucherId = id.value;
    } else if (id.id !== undefined && id.id !== null) {
      voucherId = id.id;
    } else if (id.dataset && id.dataset.id) {
      voucherId = id.dataset.id;
    } else {
      console.error('Invalid id parameter (object):', id);
      return null;
    }
  }
  
  // Convert to number
  voucherId = parseInt(String(voucherId), 10);
  
  if (isNaN(voucherId) || voucherId <= 0) {
    console.error('Invalid voucher ID:', id, '-> parsed as:', voucherId);
    return null;
  }
  
  return voucherId;
}

// Accountant Verify
function accountantVerify(id) {
  console.log('accountantVerify called with:', id, 'type:', typeof id);
  
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    console.error('Failed to extract voucher ID from:', id);
    alert('Error: Invalid voucher ID. Please check the console for details.');
    return;
  }
  
  console.log('Using voucher ID:', voucherId);
  
  const form = document.getElementById('accountantVerifyForm');
  if (!form) {
    console.error('accountantVerifyForm not found');
    alert('Error: Verification form not found');
    return;
  }
  
  // Construct the URL directly to avoid route helper issues
  const actionUrl = `/petty-cash/${voucherId}/accountant-verify`;
  
  // Ensure action is set as a string before resetting
  form.setAttribute('action', actionUrl);
  form.action = actionUrl;
  console.log('Form action set to:', actionUrl);
  console.log('Form action attribute:', form.getAttribute('action'));
  console.log('Form action property:', form.action);
  
  // Reset form but preserve the action
  const currentAction = form.action;
  form.reset();
  // Restore action after reset (reset shouldn't affect it, but just in case)
  form.action = currentAction;
  form.setAttribute('action', currentAction);
  const actionField = form.querySelector('input[name="action"]');
  if (actionField) {
    actionField.value = 'approve';
  } else {
    // Add action field if it doesn't exist
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'approve';
    form.insertBefore(actionInput, form.firstChild);
  }
  
  const modalElement = document.getElementById('accountantVerifyModal');
  if (!modalElement) {
    console.error('accountantVerifyModal not found');
    alert('Error: Verification modal not found');
    return;
  }
  
  // Store voucher ID in a data attribute for safety
  form.setAttribute('data-voucher-id', voucherId);
  
  // Verify action is set correctly before showing modal
  const verifyAction = form.getAttribute('action');
  if (!verifyAction || verifyAction.includes('[object') || verifyAction.includes('HTMLInputElement')) {
    console.error('Action verification failed:', verifyAction);
    form.action = actionUrl;
    form.setAttribute('action', actionUrl);
  }
  
  const modal = new bootstrap.Modal(modalElement);
  
  // Add event listener to verify action when modal is shown
  modalElement.addEventListener('shown.bs.modal', function() {
    const currentAction = form.getAttribute('action');
    if (!currentAction || currentAction.includes('[object')) {
      const storedId = form.getAttribute('data-voucher-id');
      if (storedId) {
        form.action = `/petty-cash/${storedId}/accountant-verify`;
        form.setAttribute('action', `/petty-cash/${storedId}/accountant-verify`);
        console.log('Action restored from stored ID:', form.action);
      }
    }
  }, { once: true });
  
  modal.show();
}

// HOD Approve
function hodApprove(id) {
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    alert('Error: Invalid voucher ID');
    return;
  }
  
  const form = document.getElementById('hodApproveForm');
  if (form) {
    form.action = `/petty-cash/${voucherId}/hod-approve`;
  }
  const modal = new bootstrap.Modal(document.getElementById('hodApproveModal'));
  modal.show();
}

// CEO Approve
function ceoApprove(id) {
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    alert('Error: Invalid voucher ID');
    return;
  }
  
  const form = document.getElementById('ceoApproveForm');
  if (form) {
    form.action = `/petty-cash/${voucherId}/ceo-approve`;
  }
  const modal = new bootstrap.Modal(document.getElementById('ceoApproveModal'));
  modal.show();
}

// Open Payment
function openPayment(id) {
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    alert('Error: Invalid voucher ID');
    return;
  }
  
  const form = document.getElementById('paymentForm');
  if (form) {
    form.action = `/petty-cash/${voucherId}/mark-paid`;
  }
  const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
  modal.show();
}

// Approve Retirement
function approveRetirement(id) {
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    alert('Error: Invalid voucher ID');
    return;
  }
  
  const form = document.getElementById('retirementApproveForm');
  if (form) {
    form.action = `/petty-cash/${voucherId}/approve-retirement`;
  }
  const modal = new bootstrap.Modal(document.getElementById('retirementApproveModal'));
  modal.show();
}

// View Details
function viewDetails(id) {
  const voucherId = extractVoucherId(id);
  if (!voucherId) {
    alert('Error: Invalid voucher ID');
    return;
  }
  
  const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
  const content = document.getElementById('viewDetailsContent');
  
  // Reset content
  content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>';
  
  modal.show();
  
  // Fetch details - use the details-ajax route
  fetch(`/petty-cash/${voucherId}/details-ajax`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`Network error: ${response.statusText}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success && data.html) {
      content.innerHTML = data.html;
    } else {
      content.innerHTML = `<div class="alert alert-danger text-center">${data.message || 'Error loading details. Please try again.'}</div>`;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    content.innerHTML = '<div class="alert alert-danger text-center">Error loading details. Please try again.</div>';
  });
}

// Form submissions
document.addEventListener('DOMContentLoaded', function() {
  // Accountant Verify Form
  const accountantVerifyForm = document.getElementById('accountantVerifyForm');
  if (accountantVerifyForm) {
    accountantVerifyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form action and ensure it's a valid string
      // Try multiple ways to get the action to be safe
      let formAction = this.getAttribute('action') || this.action || '';
      
      // If action is still invalid, try to get from data attribute
      if (!formAction || typeof formAction !== 'string' || formAction === '' || formAction.includes('[object')) {
        const storedId = this.getAttribute('data-voucher-id');
        if (storedId) {
          formAction = `/petty-cash/${storedId}/accountant-verify`;
          this.action = formAction;
          this.setAttribute('action', formAction);
          console.log('Restored action from data-voucher-id:', formAction);
        } else {
          console.error('Invalid form action and no stored ID:', formAction, this);
          alert('Error: Form action is not set correctly. Please close the modal and try again.');
          return;
        }
      }
      
      // Final validation - ensure action is a valid URL string
      if (formAction.includes('[object') || formAction.includes('HTMLInputElement') || !formAction.startsWith('/')) {
        console.error('Form action contains invalid reference:', formAction);
        alert('Error: Invalid form configuration. Please refresh the page and try again.');
        return;
      }
      
      // Ensure it's a string (not an object)
      formAction = String(formAction);
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';
      
      console.log('Submitting form to:', formAction);
      
      fetch(formAction, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(async response => {
        let data;
        try {
          data = await response.json();
        } catch (e) {
          // If response is not JSON, it might be HTML (redirect)
          throw { 
            response, 
            data: { 
              message: 'Server returned an unexpected response. Please try again.',
              success: false 
            } 
          };
        }
        
        if (!response.ok) {
          throw { response, data };
        }
        return data;
      })
      .then(data => {
        if (data.success) {
          if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.success('Success', data.message || 'Voucher verified successfully!', { duration: 5000 });
          } else {
            alert(data.message || 'Voucher verified successfully!');
          }
          setTimeout(() => location.reload(), 1500);
        } else {
          throw { data };
        }
      })
      .catch(error => {
        console.error('Error:', error);
        console.error('Error response:', error.response);
        console.error('Error data:', error.data);
        
        let errorMsg = 'Error verifying voucher';
        
        if (error.data) {
          if (error.data.message) {
            errorMsg = error.data.message;
          } else if (error.data.errors) {
            const errors = Object.values(error.data.errors).flat();
            errorMsg = errors.join(', ');
          } else if (typeof error.data === 'string') {
            errorMsg = error.data;
          }
        } else if (error.response) {
          if (error.response.status === 422) {
            errorMsg = 'Validation error: Please check all required fields are filled.';
          } else if (error.response.status === 403) {
            errorMsg = 'You do not have permission to perform this action.';
          } else if (error.response.status === 404) {
            errorMsg = 'Voucher not found. It may have been deleted.';
          } else {
            errorMsg = `Server error (${error.response.status}). Please try again.`;
          }
        } else if (error.message) {
          errorMsg = error.message;
        }
        
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

  // HOD Approve Form
  const hodApproveForm = document.getElementById('hodApproveForm');
  if (hodApproveForm) {
    hodApproveForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || 'Error approving voucher');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    });
  }

  // CEO Approve Form
  const ceoApproveForm = document.getElementById('ceoApproveForm');
  if (ceoApproveForm) {
    ceoApproveForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || 'Error approving voucher');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    });
  }

  // Payment Form
  const paymentForm = document.getElementById('paymentForm');
  if (paymentForm) {
    paymentForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || 'Error processing payment');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    });
  }

  // Retirement Approve Form
  const retirementApproveForm = document.getElementById('retirementApproveForm');
  if (retirementApproveForm) {
    retirementApproveForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || 'Error approving retirement');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    });
  }
});
</script>

