<!-- View Device Details Modal -->
<div class="modal fade" id="viewDeviceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header bg-info text-white" style="flex-shrink: 0;">
                <h5 class="modal-title">
                    <i class="bx bx-info-circle me-2"></i>Device Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewDeviceContent" style="overflow-y: auto; flex: 1; max-height: calc(90vh - 150px);">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading device details...</p>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="editDeviceFromViewBtn" onclick="editDeviceFromView()">
                    <i class="bx bx-edit me-1"></i>Edit Device
                </button>
            </div>
        </div>
    </div>
</div>

