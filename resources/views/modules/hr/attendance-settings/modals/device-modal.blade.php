<!-- Device Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header bg-primary text-white" style="flex-shrink: 0;">
                <h5 class="modal-title" id="deviceModalTitle">
                    <i class="bx bx-plus me-2"></i>Add Device
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deviceForm" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                <div class="modal-body" style="overflow-y: auto; flex: 1; max-height: calc(90vh - 150px);">
                    <input type="hidden" id="deviceId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deviceName" class="form-label">Device Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="deviceName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deviceDeviceId" class="form-label">Device ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="deviceDeviceId" name="device_id" required>
                            <small class="text-muted">Unique identifier for the device</small>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deviceType" class="form-label">Device Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="deviceType" name="device_type" required>
                                <option value="biometric">Biometric</option>
                                <option value="rfid">RFID</option>
                                <option value="fingerprint">Fingerprint</option>
                                <option value="face_recognition">Face Recognition</option>
                                <option value="card_swipe">Card Swipe</option>
                                <option value="mobile">Mobile</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deviceLocation" class="form-label">Location</label>
                            <select class="form-select" id="deviceLocation" name="location_id">
                                <option value="">Select Location</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="deviceManufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="deviceManufacturer" name="manufacturer">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="deviceModel" class="form-label">Model</label>
                            <input type="text" class="form-control" id="deviceModel" name="model">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="deviceSerialNumber" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="deviceSerialNumber" name="serial_number">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="deviceIpAddress" class="form-label">IP Address</label>
                            <input type="text" class="form-control" id="deviceIpAddress" name="ip_address" placeholder="192.168.1.100">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="devicePort" class="form-label">Port</label>
                            <input type="number" class="form-control" id="devicePort" name="port" min="1" max="65535" placeholder="4370">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="deviceMacAddress" class="form-label">MAC Address</label>
                            <input type="text" class="form-control" id="deviceMacAddress" name="mac_address" placeholder="00:1B:44:11:3A:B7">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deviceConnectionType" class="form-label">Connection Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="deviceConnectionType" name="connection_type" required>
                                <option value="network">Network</option>
                                <option value="usb">USB</option>
                                <option value="bluetooth">Bluetooth</option>
                                <option value="wifi">WiFi</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deviceSyncInterval" class="form-label">Sync Interval (minutes)</label>
                            <input type="number" class="form-control" id="deviceSyncInterval" name="sync_interval_minutes" min="1" max="1440" value="5">
                            <small class="text-muted">How often to sync with device</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deviceConnectionConfig" class="form-label">Connection Config (JSON)</label>
                        <textarea class="form-control" id="deviceConnectionConfig" name="connection_config" rows="3" placeholder='{"api_key": "xxx", "endpoint": "xxx"}'></textarea>
                        <small class="text-muted">JSON format for connection settings</small>
                    </div>

                    <div class="mb-3">
                        <label for="deviceCapabilities" class="form-label">Capabilities (JSON)</label>
                        <textarea class="form-control" id="deviceCapabilities" name="capabilities" rows="2" placeholder='["fingerprint", "face_recognition"]'></textarea>
                        <small class="text-muted">Device capabilities in JSON array format</small>
                    </div>

                    <div class="mb-3">
                        <label for="deviceSettings" class="form-label">Settings (JSON)</label>
                        <textarea class="form-control" id="deviceSettings" name="settings" rows="2" placeholder='{"timezone": "UTC", "language": "en"}'></textarea>
                        <small class="text-muted">Device-specific settings in JSON format</small>
                    </div>

                    <div class="mb-3">
                        <label for="deviceNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="deviceNotes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="deviceIsActive" name="is_active" checked>
                            <label class="form-check-label" for="deviceIsActive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
