<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileFolder;
use App\Models\File as FileModel;
use App\Models\FileAccessRequest;
use App\Models\RackCategory;
use App\Models\RackFolder;
use App\Models\RackFile;
use App\Models\RackFileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileApiController extends Controller
{
    // Digital Files
    
    public function digitalIndex(Request $request)
    {
        $user = Auth::user();
        $folderId = $request->get('folder_id');
        
        $query = FileModel::with(['uploader:id,name', 'folder']);
        
        if ($folderId) {
            $query->where('folder_id', $folderId);
        }
        
        $files = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'file_type' => $file->file_type,
                    'size' => $file->size,
                    'uploader' => $file->uploader ? $file->uploader->name : null,
                    'created_at' => $file->created_at->toIso8601String(),
                ];
            })
        ]);
    }

    public function digitalFolders()
    {
        $folders = FileFolder::whereNull('parent_id')
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'access_level' => $folder->access_level,
                ];
            })
        ]);
    }

    public function digitalFolderContents($id)
    {
        $folder = FileFolder::with(['files.uploader'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                ],
                'files' => $folder->files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'size' => $file->size,
                        'created_at' => $file->created_at->toIso8601String(),
                    ];
                })
            ]
        ]);
    }

    public function digitalShow($id)
    {
        $file = FileModel::with(['uploader', 'folder'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $file->id,
                'name' => $file->name,
                'file_type' => $file->file_type,
                'size' => $file->size,
                'description' => $file->description,
                'uploader' => $file->uploader ? $file->uploader->name : null,
                'created_at' => $file->created_at->toIso8601String(),
            ]
        ]);
    }

    public function digitalUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
            'folder_id' => 'required|exists:file_folders,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $filename = time() . '_' . $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->storeAs('files', $filename, 'public');

        $file = FileModel::create([
            'folder_id' => $request->folder_id,
            'name' => $request->name ?? $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $uploadedFile->getClientOriginalExtension(),
            'size' => $uploadedFile->getSize(),
            'description' => $request->description,
            'uploaded_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $file->id,
                'name' => $file->name,
            ]
        ], 201);
    }

    public function digitalDownload($id)
    {
        $file = FileModel::findOrFail($id);
        
        if (!Storage::disk('public')->exists($file->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->download($file->file_path, $file->name);
    }

    public function requestAccess(Request $request, $id)
    {
        $file = FileModel::findOrFail($id);
        $user = Auth::user();

        $request = FileAccessRequest::create([
            'file_id' => $file->id,
            'requester_id' => $user->id,
            'status' => 'pending',
            'reason' => $request->input('reason', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request submitted',
            'data' => $request
        ], 201);
    }

    public function myAccessRequests()
    {
        $user = Auth::user();
        
        $requests = FileAccessRequest::where('requester_id', $user->id)
            ->with(['file', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function pendingAccessRequests()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $requests = FileAccessRequest::where('status', 'pending')
            ->with(['file', 'requester'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function approveAccessRequest($id)
    {
        $request = FileAccessRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request approved'
        ]);
    }

    public function rejectAccessRequest($id)
    {
        $request = FileAccessRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request rejected'
        ]);
    }

    public function digitalSearch(Request $request)
    {
        $query = $request->get('q');
        
        $files = FileModel::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('uploader:id,name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    // Physical Racks

    public function physicalIndex(Request $request)
    {
        $racks = RackCategory::with('folders')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $racks
        ]);
    }

    public function physicalCategories()
    {
        $categories = RackCategory::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function physicalRackContents($id)
    {
        $folder = RackFolder::with(['files'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                ],
                'files' => $folder->files
            ]
        ]);
    }

    public function physicalShow($id)
    {
        $file = RackFile::with(['folder', 'category'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $file
        ]);
    }

    public function requestPhysicalFile(Request $request, $id)
    {
        $file = RackFile::findOrFail($id);
        $user = Auth::user();

        $fileRequest = RackFileRequest::create([
            'rack_file_id' => $file->id,
            'requester_id' => $user->id,
            'status' => 'pending',
            'reason' => $request->input('reason', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File request submitted',
            'data' => $fileRequest
        ], 201);
    }

    public function myPhysicalRequests()
    {
        $user = Auth::user();
        
        $requests = RackFileRequest::where('requester_id', $user->id)
            ->with(['rackFile', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function pendingPhysicalRequests()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $requests = RackFileRequest::where('status', 'pending')
            ->with(['rackFile', 'requester'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function approvePhysicalRequest($id)
    {
        $request = RackFileRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $request->rackFile->update(['status' => 'issued']);

        return response()->json([
            'success' => true,
            'message' => 'File request approved'
        ]);
    }

    public function rejectPhysicalRequest($id)
    {
        $request = RackFileRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO', 'Record Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File request rejected'
        ]);
    }

    public function returnPhysicalFile($id)
    {
        $request = RackFileRequest::findOrFail($id);
        $user = Auth::user();

        if ($request->requester_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update(['status' => 'returned']);
        $request->rackFile->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'File returned successfully'
        ]);
    }
}







