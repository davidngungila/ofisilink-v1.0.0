<div class="row">
    <!-- Profile Image -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-image"></i> Profile Photo</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3 position-relative d-inline-block">
                    <div class="avatar avatar-xxl">
                        @if($adminUser->photo)
                            <img src="{{ Storage::url('photos/' . $adminUser->photo) }}" alt="Profile Photo" class="rounded-circle" id="adminProfileImagePreview" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <span class="avatar-initial rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white" style="width: 150px; height: 150px; font-size: 4rem;" id="adminProfileImagePreview">
                                {{ substr($adminUser->name, 0, 1) }}
                            </span>
                        @endif
                    </div>
                    <button type="button" class="btn btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 40px; height: 40px; padding: 0;" id="btnChangeAdminPhoto" title="Change Photo">
                        <i class="bx bx-camera"></i>
                    </button>
                </div>
                <input type="file" id="inpAdminPhoto" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" style="display: none;">
                <p class="text-muted small mb-0">JPG, PNG or GIF. Max size 5MB</p>
                <div id="adminPhotoUploadProgress" class="mt-2" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-user"></i> Profile Information</h5>
            </div>
            <div class="card-body">
                <form id="adminProfileForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="adminName" class="form-control" value="{{ $adminUser->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="adminEmail" class="form-control" value="{{ $adminUser->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $adminUser->roles->pluck('name')->join(', ') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-control" value="{{ $adminUser->created_at->format('M d, Y') }}" readonly>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-xxl {
        transition: transform 0.3s ease;
    }
    .avatar-xxl:hover {
        transform: scale(1.05);
    }
    #btnChangeAdminPhoto {
        transition: all 0.3s ease;
    }
    #btnChangeAdminPhoto:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    const $ = jQuery;
    
    $(document).ready(function() {
    // Change Photo Button
    $('#btnChangeAdminPhoto').on('click', function() {
        $('#inpAdminPhoto').click();
    });

    // Photo Upload
    $('#inpAdminPhoto').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Error!', 'File size must be less than 5MB', 'error');
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Error!', 'Only image files (JPG, PNG, GIF, WEBP) are allowed', 'error');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#adminProfileImagePreview').replaceWith(
                '<img src="' + e.target.result + '" alt="Profile Photo" class="rounded-circle" id="adminProfileImagePreview" style="width: 150px; height: 150px; object-fit: cover;">'
            );
        };
        reader.readAsDataURL(file);

        // Upload file
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', '{{ csrf_token() }}');

        $('#adminPhotoUploadProgress').show();

        $.ajax({
            url: '{{ route("admin.settings.profile.photo") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        $('#adminPhotoUploadProgress .progress-bar').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if(response.success) {
                    $('#adminPhotoUploadProgress').hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Update image preview with new URL
                    if(response.photo_url) {
                        $('#adminProfileImagePreview').attr('src', response.photo_url);
                    }
                }
            },
            error: function(xhr) {
                $('#adminPhotoUploadProgress').hide();
                const errorMsg = xhr.responseJSON?.message || 'Error uploading photo';
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });

    // Update Profile Form
    $('#adminProfileForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Updating...');
        
        $.ajax({
            url: '{{ route("admin.settings.profile") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Update form fields if user data is returned
                    if(response.user) {
                        $('#adminName').val(response.user.name);
                        $('#adminEmail').val(response.user.email);
                    }
                }
                submitBtn.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMsg = xhr.responseJSON?.message || 'Error updating profile';
                if(Object.keys(errors).length > 0) {
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMsg
                });
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    });
});
</script>
@endpush



