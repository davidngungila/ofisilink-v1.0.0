<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    /**
     * Get all notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count()
        ]);
    }

    /**
     * Get unread notifications
     */
    public function unread(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 20);
        
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Get single notification
     */
    public function show($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->findOrFail($id);

        // Mark as read
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'message' => $notification->message,
                'link' => $notification->link,
                'is_read' => true,
                'created_at' => $notification->created_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
}







