@extends('layouts.app')

@section('title','Account Settings')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-0"><i class="bx bx-user"></i> My Profile</h4>
      <p class="text-muted mb-0">Manage your profile, photo, phone and security settings</p>
    </div>
    <div>
      <span class="badge bg-label-success me-2">
        <i class="bx bx-check-circle me-1"></i>Active
      </span>
      <span class="badge bg-label-info">
        <i class="bx bx-calendar me-1"></i>Joined {{ $user->created_at->format('M Y') }}
      </span>
    </div>
  </div>

  <div class="row g-4">
    <!-- Profile Image with Advanced Features -->
    <div class="col-xl-4 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold"><i class="bx bx-image me-2"></i>Profile Photo</span>
          @if($user->photo)
            <button type="button" class="btn btn-sm btn-label-danger" id="btnDeletePhoto" title="Remove Photo">
              <i class="bx bx-trash"></i>
            </button>
          @endif
        </div>
        <div class="card-body">
          <!-- Avatar Container -->
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <div class="avatar avatar-xxl" id="avatarContainer" style="cursor: pointer;">
                @if($user->photo)
                  @php
                    $photoUrl = route('storage.photos', ['filename' => $user->photo]);
                  @endphp
                  <img src="{{ $photoUrl }}?t={{ time() }}" alt="Profile Photo" class="rounded-circle" id="profileImagePreview" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #e7e7ff;" title="Click to change photo">
                @else
                  <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px; font-size: 3.5rem; border: 3px solid #e7e7ff;" id="profileImagePreview" title="Click to change photo">{{ substr($user->name, 0, 1) }}</span>
                @endif
              </div>
              <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle shadow-lg" style="width: 42px; height: 42px; padding: 0; z-index: 10; border: 3px solid white;" id="btnChangePhoto" title="Change Photo">
                <i class="bx bx-camera fs-5"></i>
              </button>
            </div>
          </div>
          
          <!-- Drag & Drop Overlay -->
          <div id="dropZone" class="border-2 border-dashed rounded-3 p-4 mb-3 text-center" style="display: none; border-color: #696cff; background: rgba(105, 108, 255, 0.05); transition: all 0.3s;">
            <i class="bx bx-cloud-upload fs-1 text-primary mb-2 d-block"></i>
            <p class="mb-0 fw-semibold">Drop your image here</p>
            <small class="text-muted">or click to browse</small>
          </div>
          
          <input type="file" id="inpPhoto" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" style="display: none;">
          
          <!-- File Info -->
          <div class="bg-label-secondary rounded p-3 mb-3">
            <div class="d-flex align-items-center mb-2">
              <i class="bx bx-info-circle text-primary me-2"></i>
              <span class="small fw-semibold">Supported Formats</span>
            </div>
            <p class="small mb-2 text-muted ms-4">
              JPG, PNG, GIF or WEBP
            </p>
            <div class="d-flex align-items-center mb-0">
              <i class="bx bx-file text-primary me-2"></i>
              <span class="small fw-semibold">Maximum Size</span>
            </div>
            <p class="small mb-0 text-muted ms-4">
              5MB
            </p>
          </div>
          
          <!-- Drag & Drop Hint -->
          <div class="text-center">
            <small class="text-muted d-flex align-items-center justify-content-center">
              <i class="bx bx-drag me-1"></i>
              <span>You can drag and drop image here</span>
            </small>
          </div>
          
          <!-- Upload Progress -->
          <div id="photoUploadProgress" class="mt-3" style="display: none;">
            <div class="progress mb-2" style="height: 10px; border-radius: 10px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;"></div>
            </div>
            <small class="text-muted d-block text-center" id="uploadStatus">
              <i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...
            </small>
          </div>
          
          <!-- Image Preview Before Upload -->
          <div id="imagePreviewContainer" class="mt-3" style="display: none;">
            <div class="card border">
              <div class="card-body p-2">
                <div class="text-center mb-2">
                  <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px; border: 1px solid #e7e7ff;">
                </div>
              </div>
            </div>
            <div class="d-grid gap-2 mt-2">
              <button type="button" class="btn btn-success" id="btnConfirmUpload">
                <i class="bx bx-check me-1"></i>Confirm Upload
              </button>
              <button type="button" class="btn btn-outline-secondary" id="btnCancelPreview">
                <i class="bx bx-x me-1"></i>Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Basic Profile Info -->
    <div class="col-xl-4 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bx bx-user me-2"></i>Basic Information</span>
          <button class="btn btn-sm btn-icon btn-label-secondary" id="btnEditProfile" title="Edit">
            <i class="bx bx-edit"></i>
          </button>
        </div>
        <div class="card-body">
          <form id="profileForm">
            <div class="mb-3">
              <label class="form-label">
                <i class="bx bx-user me-1"></i>Full Name <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="inpName" value="{{ $user->name }}" required />
              <small class="text-muted">Your display name visible to others</small>
            </div>
            <div class="mb-3">
              <label class="form-label">
                <i class="bx bx-envelope me-1"></i>Email Address <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text"><i class="bx bx-at"></i></span>
                <input type="email" class="form-control" id="inpEmail" value="{{ $user->email }}" required />
              </div>
              <small class="text-muted">Used for login and notifications</small>
            </div>
            <div class="mb-3">
              <label class="form-label">
                <i class="bx bx-heart me-1"></i>Marital Status
              </label>
              <select class="form-select" id="inpMaritalStatus">
                <option value="">Select...</option>
                <option value="single" {{ $user->marital_status === 'single' ? 'selected' : '' }}>Single</option>
                <option value="married" {{ $user->marital_status === 'married' ? 'selected' : '' }}>Married</option>
                <option value="divorced" {{ $user->marital_status === 'divorced' ? 'selected' : '' }}>Divorced</option>
                <option value="widowed" {{ $user->marital_status === 'widowed' ? 'selected' : '' }}>Widowed</option>
              </select>
            </div>
            @if($user->employee_id)
            <div class="mb-3">
              <label class="form-label">
                <i class="bx bx-id-card me-1"></i>Employee ID
              </label>
              <input type="text" class="form-control" value="{{ $user->employee_id }}" readonly />
              <small class="text-muted">Your unique employee identifier</small>
            </div>
            @endif
            <div class="d-grid">
              <button type="button" class="btn btn-primary" id="btnUpdateProfile">
                <i class="bx bx-save me-1"></i>Update Profile
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Phone Number (with OTP) -->
    <div class="col-xl-4 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bx bx-phone me-2"></i>Phone Number</span>
          @if($user->mobile || $user->phone)
            <span class="badge bg-label-success">
              <i class="bx bx-check-circle me-1"></i>Verified
            </span>
          @endif
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">
              <i class="bx bx-phone-call me-1"></i>Current Phone Number
            </label>
            <div class="input-group">
              <span class="input-group-text bg-label-secondary"><i class="bx bx-phone"></i></span>
              <input type="text" class="form-control bg-label-secondary" value="{{ $user->mobile ?? $user->phone ?? 'Not set' }}" readonly />
            </div>
            @if(!$user->mobile && !$user->phone)
              <div class="alert alert-warning mt-2 mb-0">
                <i class="bx bx-info-circle me-2"></i>
                <small>No phone number registered. Please contact administrator.</small>
              </div>
            @else
              <small class="text-muted">This is your verified phone number</small>
            @endif
          </div>
          
          <div class="mb-3">
            <label class="form-label">
              <i class="bx bx-mobile me-1"></i>New Phone Number
            </label>
            <small class="text-muted d-block mb-2">
              <i class="bx bx-info-circle me-1"></i>Format: 0712345678 or 255712345678
            </small>
            <div class="input-group">
              <span class="input-group-text"><i class="bx bx-phone"></i></span>
              <input type="tel" class="form-control" id="inpMobile" placeholder="2556XXXXXXXX" />
            </div>
          </div>
          
          <!-- OTP Verification for Phone -->
          <div id="phoneOtpArea" style="display:none" class="mb-3">
            <label class="form-label">
              <i class="bx bx-key me-1"></i>Enter OTP Code
            </label>
            <div class="input-group mb-2">
              <input type="text" class="form-control text-center fw-bold fs-5" id="inpPhoneOtp" maxlength="6" placeholder="000000" style="letter-spacing: 0.5rem;" />
              <button class="btn btn-success" id="btnVerifyPhoneOtp">
                <i class="bx bx-check me-1"></i>Verify
              </button>
            </div>
            <small class="text-muted">
              <i class="bx bx-time me-1"></i>OTP sent to your current phone number. Valid for 10 minutes.
            </small>
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnResendPhoneOtp" style="display: none;">
                <i class="bx bx-refresh me-1"></i>Resend OTP
              </button>
            </div>
          </div>
          
          <div class="d-grid gap-2">
            <button class="btn btn-outline-primary" id="btnSendPhoneOtp">
              <i class="bx bx-send me-1"></i>Send OTP to Update Phone
            </button>
            <button class="btn btn-primary" id="btnUpdatePhone" style="display:none;">
              <i class="bx bx-check me-1"></i>Update Phone Number
            </button>
          </div>
          
          <div class="alert alert-info mt-3 mb-0" style="font-size: 0.875rem;">
            <i class="bx bx-info-circle me-1"></i>OTP verification required to change phone number for security
          </div>
        </div>
      </div>
    </div>

    <!-- Password & Security (with OTP) -->
    <div class="col-xl-12 col-lg-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bx bx-lock me-2"></i>Password & Security</span>
          <span class="badge bg-label-warning">
            <i class="bx bx-shield-quarter me-1"></i>Protected
          </span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">
                  <i class="bx bx-key me-1"></i>Start Password Change
                </label>
                <button class="btn btn-outline-primary w-100" id="btnSendOtp">
                  <i class="bx bx-send me-1"></i>Send OTP to Change Password
                </button>
                <small class="text-muted d-block mt-2">
                  <i class="bx bx-info-circle me-1"></i>OTP will be sent to your verified phone number
                </small>
              </div>
              
              <!-- OTP Verification Area -->
              <div id="otpArea" style="display:none" class="mb-3">
                <label class="form-label">
                  <i class="bx bx-key me-1"></i>Enter OTP Code
                </label>
                <div class="input-group mb-2">
                  <input type="text" class="form-control text-center fw-bold fs-5" id="inpOtp" maxlength="6" placeholder="000000" style="letter-spacing: 0.5rem;" />
                  <button class="btn btn-success" id="btnVerifyOtp">
                    <i class="bx bx-check me-1"></i>Verify
                  </button>
                </div>
                <small class="text-muted">
                  <i class="bx bx-time me-1"></i>OTP valid for 10 minutes
                </small>
                <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="btnResendOtp" style="display: none;">
                    <i class="bx bx-refresh me-1"></i>Resend OTP
                  </button>
                </div>
              </div>
              
              <div class="alert alert-warning mt-3">
                <i class="bx bx-info-circle me-2"></i>
                <strong>Security Tip:</strong> Use a strong password with at least 8 characters, including uppercase, lowercase, numbers and symbols.
              </div>
            </div>
            
            <!-- Password Change Form -->
            <div class="col-md-6">
              <div id="pwdArea" style="display:none">
                <h6 class="mb-3">
                  <i class="bx bx-lock-alt me-1"></i>Change Your Password
                </h6>
                
                <div class="mb-3">
                  <label class="form-label">
                    <i class="bx bx-lock me-1"></i>Current Password <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="inpCurrentPass" placeholder="Enter current password" />
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inpCurrentPass')">
                      <i class="bx bx-hide" id="iconCurrentPass"></i>
                    </button>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">
                    <i class="bx bx-lock-open me-1"></i>New Password <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="inpNewPass" placeholder="Enter new password" />
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inpNewPass')">
                      <i class="bx bx-hide" id="iconNewPass"></i>
                    </button>
                  </div>
                  <div class="mt-2">
                    <div class="progress" style="height: 4px; display: none;" id="passwordStrengthBar">
                      <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Minimum 8 characters</small>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">
                    <i class="bx bx-lock-open-alt me-1"></i>Confirm New Password <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="inpNewPassConf" placeholder="Confirm new password" />
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inpNewPassConf')">
                      <i class="bx bx-hide" id="iconNewPassConf"></i>
                    </button>
                  </div>
                  <small class="text-muted" id="passwordMatch"></small>
                </div>
                
                <button class="btn btn-primary w-100" id="btnUpdatePassword">
                  <i class="bx bx-check me-1"></i>Update Password
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let selectedFile = null;
  let phoneOtpVerified = false;

  // Enhanced Toast Notification
  function showToast(msg, type = 'info'){
    const cls = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : (type === 'warning' ? 'bg-warning' : 'bg-info'));
    const el = document.createElement('div');
    el.className = `bs-toast toast toast-placement-ex m-2 ${cls}`;
    el.style.zIndex = 9999;
    el.innerHTML = `
      <div class="toast-header">
        <i class="bx bx-${type === 'success' ? 'check' : type === 'error' ? 'x' : type === 'warning' ? 'error-circle' : 'info-circle'} me-2"></i>
        <div class="me-auto fw-semibold">${type === 'success' ? 'Success' : type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Info'}</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body">${msg}</div>
    `;
    document.body.appendChild(el);
    const toast = new bootstrap.Toast(el, {delay: 5000});
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  }

  // Update Header Avatar Function - More comprehensive
  function updateHeaderAvatars(photoUrl) {
    if (!photoUrl) return;
    
    const timestamp = '?t=' + Date.now();
    const fullUrl = photoUrl + timestamp;
    const userName = '@json(auth()->user()->name)';
    
    console.log('Updating avatars with URL:', fullUrl);
    
    // Function to update an img element or replace span with img
    function updateAvatarElement(element) {
      if (element.tagName === 'SPAN') {
        // Replace span with image
        const oldSpan = element.cloneNode(true);
        const newImg = document.createElement('img');
        newImg.src = fullUrl;
        newImg.alt = userName;
        newImg.className = element.className.replace('avatar-initial', '') + ' rounded-circle';
        newImg.style.objectFit = 'cover';
        
        // Preserve dimensions
        if (element.style.width) newImg.style.width = element.style.width;
        if (element.style.height) newImg.style.height = element.style.height;
        
        // Preserve data attributes
        newImg.setAttribute('data-profile-image', 'true');
        if (element.classList.contains('user-profile-avatar')) {
          newImg.classList.add('user-profile-avatar');
        }
        
        // Error handling
        newImg.onerror = function() {
          console.error('Failed to load avatar image, reverting to span');
          // Keep the span on error
          if (this.parentNode && oldSpan) {
            this.parentNode.replaceChild(oldSpan, this);
          }
        };
        
        newImg.onload = function() {
          console.log('Avatar image loaded successfully:', fullUrl);
        };
        
        element.parentNode.replaceChild(newImg, element);
        return newImg;
      } else if (element.tagName === 'IMG') {
        // Update existing image
        const oldSrc = element.src;
        const photoUrlAlt = fullUrl.replace('/storage/', '/storage/').replace('//', '/');
        
        element.src = fullUrl;
        element.setAttribute('data-profile-image', 'true');
        
        element.onerror = function() {
          console.error('Failed to load updated avatar with URL:', fullUrl);
          // Try alternative URL if available
          if (window.photoUrlAlt && window.photoUrlAlt !== fullUrl) {
            console.log('Trying alternative URL:', window.photoUrlAlt);
            this.src = window.photoUrlAlt + '?t=' + Date.now();
            return;
          }
          // Revert to old src as last resort
          console.log('Reverting to old source');
          this.src = oldSrc;
        };
        
        element.onload = function() {
          console.log('Avatar loaded successfully from:', this.src);
        };
        
        return element;
      }
      return element;
    }
    
    // Update all profile avatars using the data attribute and class
    const allProfileAvatars = document.querySelectorAll('.user-profile-avatar, [data-profile-image="true"], .avatar img, .avatar span, .ttr-user-avatar img, .ttr-user-avatar span');
    
    console.log('Found', allProfileAvatars.length, 'avatar elements to update');
    
    allProfileAvatars.forEach(el => {
      try {
        updateAvatarElement(el);
      } catch (error) {
        console.error('Error updating avatar element:', error);
      }
    });
    
    // Also specifically update dropdown avatars
    const dropdownAvatars = document.querySelectorAll('.dropdown-menu .avatar img, .dropdown-menu .avatar span, .navbar-dropdown .avatar img');
    dropdownAvatars.forEach(el => {
      try {
        updateAvatarElement(el);
      } catch (error) {
        console.error('Error updating dropdown avatar:', error);
      }
    });
    
    // Force reload of any cached images
    const cachedAvatars = document.querySelectorAll('img[src*="photos/"]');
    cachedAvatars.forEach(img => {
      if (img.src.includes('/photos/')) {
        const url = new URL(img.src);
        url.searchParams.set('t', Date.now());
        img.src = url.toString();
      }
    });
  }

  // File Validation
  function validateImageFile(file) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      showToast('Please select a valid image file (JPG, PNG, GIF or WEBP)', 'error');
      return false;
    }
    if (file.size > 5 * 1024 * 1024) {
      showToast('File size must be less than 5MB', 'error');
      return false;
    }
    return true;
  }

  // Handle File Selection
  function handleFileSelect(file) {
    if (!validateImageFile(file)) return;
    
    selectedFile = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreview').src = e.target.result;
      document.getElementById('imagePreviewContainer').style.display = 'block';
      document.getElementById('dropZone').style.display = 'none';
    };
    reader.readAsDataURL(file);
  }

  // Profile Photo Upload with Drag & Drop
  const avatarContainer = document.getElementById('avatarContainer');
  const dropZone = document.getElementById('dropZone');
  const inpPhoto = document.getElementById('inpPhoto');
  
  // Click to change photo
  avatarContainer.addEventListener('click', function(e) {
    if (e.target.id !== 'btnChangePhoto') {
      inpPhoto.click();
    }
  });

  document.getElementById('btnChangePhoto').addEventListener('click', function(e){
    e.stopPropagation();
    inpPhoto.click();
  });

  // Drag and Drop Events
  avatarContainer.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.display = 'block';
    avatarContainer.style.opacity = '0.5';
  });

  avatarContainer.addEventListener('dragleave', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.display = 'none';
    avatarContainer.style.opacity = '1';
  });

  avatarContainer.addEventListener('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.display = 'none';
    avatarContainer.style.opacity = '1';
    
    const file = e.dataTransfer.files[0];
    if (file) {
      handleFileSelect(file);
    }
  });

  // File input change
  inpPhoto.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      handleFileSelect(file);
    }
  });

  // Confirm Upload
  document.getElementById('btnConfirmUpload').addEventListener('click', async function() {
    if (!selectedFile) return;
    
    const formData = new FormData();
    formData.append('photo', selectedFile);

    const progressDiv = document.getElementById('photoUploadProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    const uploadStatus = document.getElementById('uploadStatus');
    
    progressDiv.style.display = 'block';
    progressBar.style.width = '0%';
    uploadStatus.textContent = 'Uploading...';

    try {
      const res = await fetch(@json(route('account.settings.photo')), {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': token},
        body: formData
      });
      
      const data = await res.json();

      if (data.success) {
        // Update preview in settings page
        const preview = document.getElementById('profileImagePreview');
        if (preview) {
          if (preview.tagName === 'IMG') {
            preview.src = data.photo_url + '?t=' + Date.now();
            preview.onload = function() {
              console.log('Settings page avatar updated successfully');
            };
            preview.onerror = function() {
              console.error('Failed to load updated avatar in settings page');
            };
          } else {
            // Replace span with img
            const newImg = document.createElement('img');
            newImg.src = data.photo_url + '?t=' + Date.now();
            newImg.alt = 'Profile Photo';
            newImg.className = 'rounded-circle';
            newImg.style.width = '150px';
            newImg.style.height = '150px';
            newImg.style.objectFit = 'cover';
            newImg.style.cursor = 'pointer';
            newImg.title = 'Click to change photo';
            newImg.id = 'profileImagePreview';
            newImg.onload = function() {
              console.log('Settings page avatar created successfully');
            };
            newImg.onerror = function() {
              console.error('Failed to load new avatar in settings page');
            };
            preview.parentNode.replaceChild(newImg, preview);
          }
        }
        
        // Store alternative URL globally for fallback
        if (data.photo_url_alt) {
          window.photoUrlAlt = data.photo_url_alt;
        }
        
        // Update header avatars immediately
        console.log('Photo upload successful.');
        console.log('Route Photo URL (Primary):', data.photo_url);
        console.log('Storage Photo URL:', data.photo_url_storage || 'Not provided');
        console.log('Alternative Photo URL:', data.photo_url_alt || 'Not provided');
        console.log('Photo stored at:', data.photo_path || 'Unknown');
        console.log('Absolute path:', data.absolute_path || 'Not provided');
        console.log('File exists:', data.file_exists);
        console.log('File size:', data.file_size, 'bytes');
        if (data.debug_info) {
          console.log('Debug info:', data.debug_info);
        }
        
        // Update header avatars immediately with multiple attempts
        if (data.photo_url) {
          updateHeaderAvatars(data.photo_url);
          
          // Also try updating after a small delay to ensure DOM is ready
          setTimeout(() => {
            updateHeaderAvatars(data.photo_url);
          }, 100);
          
          // One more attempt after DOM updates
          setTimeout(() => {
            updateHeaderAvatars(data.photo_url);
          }, 500);
          
          // Final attempt with alternative URL if provided
          if (data.photo_url_alt) {
            setTimeout(() => {
              console.log('Trying alternative URL for avatars');
              updateHeaderAvatars(data.photo_url_alt);
            }, 800);
          }
        }
        
        // Show delete button
        const deleteBtn = document.getElementById('btnDeletePhoto');
        if (deleteBtn) {
          deleteBtn.style.display = 'block';
        }
        
        showToast('Profile photo updated successfully. Refreshing page...', 'success');
        document.getElementById('imagePreviewContainer').style.display = 'none';
        selectedFile = null;
        
        // Wait a bit for images to load, then reload page
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        showToast(data.message || 'Failed to upload photo', 'error');
      }
    } catch (error) {
      showToast('Error uploading photo: ' + error.message, 'error');
    } finally {
      progressDiv.style.display = 'none';
      inpPhoto.value = '';
    }
  });

  // Cancel Preview
  document.getElementById('btnCancelPreview').addEventListener('click', function() {
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('dropZone').style.display = 'none';
    selectedFile = null;
    inpPhoto.value = '';
  });

  // Delete Photo
  const btnDeletePhoto = document.getElementById('btnDeletePhoto');
  if (btnDeletePhoto) {
    btnDeletePhoto.addEventListener('click', async function() {
      if (!confirm('Are you sure you want to remove your profile photo?')) return;
      
      try {
        const formData = new FormData();
        formData.append('delete', '1');
        
        const res = await fetch(@json(route('account.settings.photo')), {
          method: 'POST',
          headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
          body: formData
        });
        
        const data = await res.json();
        if (data.success) {
          // Replace img with initial
          const preview = document.getElementById('profileImagePreview');
          const userName = '@json($user->name)';
          const initial = userName.charAt(0).toUpperCase();
          
          const newSpan = document.createElement('span');
          newSpan.className = 'avatar-initial rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center';
          newSpan.style.width = '150px';
          newSpan.style.height = '150px';
          newSpan.style.fontSize = '3.5rem';
          newSpan.style.cursor = 'pointer';
          newSpan.title = 'Click to change photo';
          newSpan.id = 'profileImagePreview';
          newSpan.textContent = initial;
          preview.parentNode.replaceChild(newSpan, preview);
          
          // Update header - remove images, show initials
          const headerAvatars = document.querySelectorAll('.avatar img, .ttr-user-avatar img, [id*="profileImagePreview"]');
          headerAvatars.forEach(avatar => {
            if (avatar.tagName === 'IMG') {
              const userName = '@json(auth()->user()->name)';
              const initial = userName.charAt(0).toUpperCase();
              const newSpan = document.createElement('span');
              newSpan.className = 'avatar-initial rounded-circle bg-label-primary';
              newSpan.id = 'profileImagePreview';
              newSpan.textContent = initial;
              avatar.parentNode.replaceChild(newSpan, avatar);
            }
          });
          
          btnDeletePhoto.style.display = 'none';
          showToast('Profile photo removed successfully', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast(data.message || 'Failed to remove photo', 'error');
        }
      } catch (error) {
        showToast('Error removing photo: ' + error.message, 'error');
      }
    });
  }

  // Update Profile
  document.getElementById('btnUpdateProfile').addEventListener('click', async function(){
    const name = document.getElementById('inpName').value.trim();
    const email = document.getElementById('inpEmail').value.trim();
    const maritalStatus = document.getElementById('inpMaritalStatus').value;

    if (!name || !email) {
      showToast('Name and email are required', 'error');
      return;
    }

    const fd = new FormData();
    fd.append('name', name);
    fd.append('email', email);
    if (maritalStatus) fd.append('marital_status', maritalStatus);

    try {
      const res = await fetch(@json(route('account.settings.profile')), {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
        body: fd
      });
      const data = await res.json();
      if (data.success) {
        showToast('Profile updated successfully', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Failed to update profile', 'error');
      }
    } catch (error) {
      showToast('Error updating profile: ' + error.message, 'error');
    }
  });

  // Password Strength Checker
  function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    const strengthBar = document.getElementById('passwordStrengthBar');
    const progressBar = strengthBar.querySelector('.progress-bar');
    
    strengthBar.style.display = 'block';
    
    if (strength <= 2) {
      progressBar.style.width = '33%';
      progressBar.className = 'progress-bar bg-danger';
    } else if (strength <= 4) {
      progressBar.style.width = '66%';
      progressBar.className = 'progress-bar bg-warning';
    } else {
      progressBar.style.width = '100%';
      progressBar.className = 'progress-bar bg-success';
    }
  }

  // Password Match Checker
  document.getElementById('inpNewPass').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    const confirmPass = document.getElementById('inpNewPassConf');
    const matchText = document.getElementById('passwordMatch');
    
    if (confirmPass.value) {
      if (this.value === confirmPass.value) {
        matchText.textContent = 'Passwords match';
        matchText.className = 'text-success';
      } else {
        matchText.textContent = 'Passwords do not match';
        matchText.className = 'text-danger';
      }
    }
  });

  document.getElementById('inpNewPassConf').addEventListener('input', function() {
    const newPass = document.getElementById('inpNewPass').value;
    const matchText = document.getElementById('passwordMatch');
    
    if (this.value === newPass) {
      matchText.textContent = 'Passwords match';
      matchText.className = 'text-success';
    } else {
      matchText.textContent = 'Passwords do not match';
      matchText.className = 'text-danger';
    }
  });

  // Phone OTP Flow
  document.getElementById('btnSendPhoneOtp').addEventListener('click', async function(){
    const res = await fetch(@json(route('account.settings.phone.otp.send')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'}
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('phoneOtpArea').style.display = 'block';
      showToast('OTP sent to your current phone number', 'success');
    } else {
      showToast(data.message || 'Failed to send OTP', 'error');
    }
  });

  document.getElementById('btnVerifyPhoneOtp').addEventListener('click', async function(){
    const code = document.getElementById('inpPhoneOtp').value.trim();
    if (!code || code.length !== 6) {
      showToast('Please enter a valid 6-digit OTP', 'error');
      return;
    }
    const fd = new FormData();
    fd.append('otp_code', code);
    const res = await fetch(@json(route('account.settings.phone.otp.verify')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      phoneOtpVerified = true;
      document.getElementById('phoneOtpArea').style.display = 'none';
      document.getElementById('btnSendPhoneOtp').style.display = 'none';
      document.getElementById('btnUpdatePhone').style.display = 'block';
      showToast('OTP verified. You can now update your phone number', 'success');
    } else {
      showToast(data.message || 'Invalid OTP', 'error');
    }
  });

  document.getElementById('btnUpdatePhone').addEventListener('click', async function(){
    if (!phoneOtpVerified) {
      showToast('Please verify OTP first', 'error');
      return;
    }
    const mobile = document.getElementById('inpMobile').value.trim();
    if (!mobile) {
      showToast('Please enter a phone number', 'error');
      return;
    }
    const fd = new FormData();
    fd.append('mobile', mobile);
    const res = await fetch(@json(route('account.settings.phone')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showToast('Phone number updated successfully', 'success');
      phoneOtpVerified = false;
      document.getElementById('inpMobile').value = '';
      document.getElementById('btnUpdatePhone').style.display = 'none';
      document.getElementById('btnSendPhoneOtp').style.display = 'block';
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message || 'Failed to update phone', 'error');
    }
  });

  // Password OTP Flow
  document.getElementById('btnSendOtp').addEventListener('click', async function(){
    const res = await fetch(@json(route('account.settings.password.otp.send')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'}
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('otpArea').style.display = 'block';
      showToast('OTP sent to your phone number', 'success');
    } else {
      showToast(data.message || 'Failed to send OTP', 'error');
    }
  });

  document.getElementById('btnVerifyOtp').addEventListener('click', async function(){
    const code = document.getElementById('inpOtp').value.trim();
    if (!code || code.length !== 6) {
      showToast('Please enter a valid 6-digit OTP', 'error');
      return;
    }
    const fd = new FormData();
    fd.append('otp_code', code);
    const res = await fetch(@json(route('account.settings.password.otp.verify')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('pwdArea').style.display = 'block';
      document.getElementById('otpArea').style.display = 'none';
      showToast('OTP verified. You can now change your password', 'success');
    } else {
      showToast(data.message || 'Invalid OTP', 'error');
    }
  });

  document.getElementById('btnUpdatePassword').addEventListener('click', async function(){
    const fd = new FormData();
    fd.append('current_password', document.getElementById('inpCurrentPass').value);
    fd.append('new_password', document.getElementById('inpNewPass').value);
    fd.append('new_password_confirmation', document.getElementById('inpNewPassConf').value);
    const res = await fetch(@json(route('account.settings.password.update')), {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showToast('Password updated successfully', 'success');
      document.getElementById('pwdArea').style.display = 'none';
      document.getElementById('inpCurrentPass').value = '';
      document.getElementById('inpNewPass').value = '';
      document.getElementById('inpNewPassConf').value = '';
      document.getElementById('inpOtp').value = '';
    } else {
      showToast(data.message || 'Failed to update password', 'error');
    }
  });

  // Make togglePassword globally accessible
  window.togglePassword = function(inputId) {
    const input = document.getElementById(inputId);
    if (!input) {
      console.error('Input field not found:', inputId);
      return;
    }
    
    // Map input IDs to icon IDs
    const iconIdMap = {
      'inpCurrentPass': 'iconCurrentPass',
      'inpNewPass': 'iconNewPass',
      'inpNewPassConf': 'iconNewPassConf'
    };
    
    // Get icon ID from map or construct it
    let iconId = iconIdMap[inputId];
    if (!iconId) {
      // Fallback: try to construct icon ID
      if (inputId.startsWith('inp')) {
        iconId = 'icon' + inputId.substring(3);
      } else {
        iconId = 'icon' + inputId.charAt(0).toUpperCase() + inputId.slice(1);
      }
    }
    
    const icon = document.getElementById(iconId);
    if (!icon) {
      console.error('Icon element not found:', iconId, 'for input:', inputId);
      return;
    }
    
    // Toggle input type and icon
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bx-hide');
      icon.classList.add('bx-show');
      icon.setAttribute('title', 'Hide password');
    } else {
      input.type = 'password';
      icon.classList.remove('bx-show');
      icon.classList.add('bx-hide');
      icon.setAttribute('title', 'Show password');
    }
  };
})();
</script>
@endpush
