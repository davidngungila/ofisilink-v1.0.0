<div class="folder-item" data-folder-id="{{ $folder->id }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="me-3">
                <i class="bx bx-folder fs-2 text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold">
                    <a href="{{ route('modules.files.digital.folder.detail', $folder->id) }}" class="text-decoration-none folder-link">
                        {{ $folder->name }}
                    </a>
                </h6>
                <div class="d-flex gap-3 flex-wrap">
                    <small class="text-muted">
                        <i class="bx bx-file me-1"></i>{{ $folder->files_count ?? 0 }} files
                    </small>
                    <small class="text-muted">
                        <i class="bx bx-folder me-1"></i>{{ $folder->subfolders_count ?? 0 }} subfolders
                    </small>
                    @if($folder->department)
                    <small class="text-muted">
                        <i class="bx bx-building me-1"></i>{{ $folder->department->name }}
                    </small>
                    @endif
                    <small class="text-muted">
                        <i class="bx bx-lock-alt me-1"></i>{{ ucfirst($folder->access_level) }}
                    </small>
                </div>
                @if($folder->description)
                <p class="text-muted mb-0 mt-1 small">{{ Str::limit($folder->description, 100) }}</p>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('modules.files.digital.folder.detail', $folder->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-show"></i> View
            </a>
            @if(isset($canManageFiles) && $canManageFiles)
            <button class="btn btn-sm btn-outline-info edit-folder-btn" data-folder-id="{{ $folder->id }}">
                <i class="bx bx-edit"></i> Edit
            </button>
            @endif
        </div>
    </div>
    
    @if(isset($foldersByParent) && $foldersByParent->has($folder->id))
        <div class="subfolders mt-3 ms-5" style="border-left: 2px solid #e9ecef; padding-left: 20px;">
            @foreach($foldersByParent->get($folder->id) as $subfolder)
                @include('modules.files.digital.partials.folder-item', ['folder' => $subfolder, 'level' => ($level ?? 0) + 1, 'foldersByParent' => $foldersByParent])
            @endforeach
        </div>
    @endif
</div>

