/**
 * Advanced Toast Notification Usage Examples
 * 
 * This file demonstrates how to use the Advanced Toast Notification system
 * throughout the application. Replace SweetAlert calls with these functions.
 */

// ============================================
// BASIC USAGE
// ============================================

// Success notification
window.toastSuccess('Success!', 'Operation completed successfully');

// Error notification
window.toastError('Error!', 'Something went wrong');

// Warning notification
window.toastWarning('Warning', 'Please check your input');

// Info notification
window.toastInfo('Information', 'Here is some information');

// Primary notification (custom color)
window.toastPrimary('Notification', 'Important message');

// ============================================
// ADVANCED USAGE
// ============================================

// With custom duration
window.toastSuccess('Success!', 'Saved successfully', { duration: 3000 });

// Persistent (won't auto-dismiss)
window.toastError('Critical Error', 'Please contact support', { persistent: true });

// With sound
window.toastSuccess('Success!', 'Email sent', { sound: true });

// With HTML content
window.toastInfo('Details', '', {
    html: '<p>User <strong>John Doe</strong> has been updated.</p><p>Changes saved successfully.</p>',
    duration: 5000
});

// With action buttons
window.toastSuccess('File Uploaded', 'Your file has been uploaded successfully', {
    actions: [
        {
            label: 'View',
            name: 'view',
            class: 'primary',
            callback: function() {
                window.location.href = '/files';
            },
            dismiss: true
        },
        {
            label: 'Dismiss',
            name: 'dismiss',
            class: 'secondary',
            callback: function() {
                // Do nothing, just dismiss
            },
            dismiss: true
        }
    ],
    duration: 8000
});

// With custom icon
window.toastInfo('Custom Icon', 'Message with custom icon', {
    icon: '<i class="bx bx-custom-icon"></i>'
});

// With onClose callback
window.toastSuccess('Saved', 'Your changes have been saved', {
    duration: 3000,
    onClose: function() {
        console.log('Toast dismissed');
    }
});

// ============================================
// AJAX RESPONSE HANDLING
// ============================================

// Example: Handle AJAX success/error
$.ajax({
    url: '/api/update',
    method: 'POST',
    data: formData,
    success: function(response) {
        // Automatically show toast based on response
        window.showAjaxToast(response, 'Update');
        
        // Or manually
        if (response.success) {
            window.toastSuccess('Success', response.message);
        } else {
            window.toastError('Error', response.message || response.error);
        }
    },
    error: function(xhr) {
        const response = xhr.responseJSON || {};
        window.toastError('Error', response.message || 'Request failed');
    }
});

// ============================================
// VALIDATION ERRORS
// ============================================

// Show Laravel validation errors
$.ajax({
    url: '/api/submit',
    method: 'POST',
    data: formData,
    error: function(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors || xhr.responseJSON;
            window.showValidationErrors(errors);
        } else {
            window.toastError('Error', xhr.responseJSON?.message || 'Submission failed');
        }
    }
});

// ============================================
// REPLACING SWEETALERT
// ============================================

// OLD: Swal.fire('Success!', 'Saved', 'success');
// NEW:
window.toastSuccess('Success!', 'Saved');

// OLD: Swal.fire('Error!', 'Failed', 'error');
// NEW:
window.toastError('Error!', 'Failed');

// OLD: Swal.fire('Warning', 'Check input', 'warning');
// NEW:
window.toastWarning('Warning', 'Check input');

// OLD: Swal.fire({ icon: 'info', title: 'Info', text: 'Message' });
// NEW:
window.toastInfo('Info', 'Message');

// ============================================
// LARAVEL FLASH MESSAGES
// ============================================

// Flash messages are automatically shown on page load
// In your controller:
// return redirect()->back()->with('success', 'User updated successfully');
// return redirect()->back()->with('error', 'Update failed');
// return redirect()->back()->with('warning', 'Please check your input');
// return redirect()->back()->with('info', 'Information message');

// ============================================
// COMPLEX EXAMPLES
// ============================================

// Success with action to reload page
window.toastSuccess('User Updated', 'User has been updated successfully', {
    actions: [
        {
            label: 'Reload',
            name: 'reload',
            class: 'primary',
            callback: function() {
                location.reload();
            },
            dismiss: true
        }
    ],
    duration: 5000
});

// Error with retry action
window.toastError('Connection Failed', 'Unable to connect to server', {
    actions: [
        {
            label: 'Retry',
            name: 'retry',
            class: 'primary',
            callback: function() {
                // Retry logic here
                performRetry();
            },
            dismiss: false
        },
        {
            label: 'Dismiss',
            name: 'dismiss',
            class: 'secondary',
            callback: function() {},
            dismiss: true
        }
    ],
    persistent: true
});

// Info with multiple actions
window.toastInfo('New Update Available', 'A new version is available', {
    actions: [
        {
            label: 'Update Now',
            name: 'update',
            class: 'primary',
            callback: function() {
                window.location.href = '/update';
            },
            dismiss: true
        },
        {
            label: 'Later',
            name: 'later',
            class: 'secondary',
            callback: function() {},
            dismiss: true
        }
    ],
    duration: 10000
});






