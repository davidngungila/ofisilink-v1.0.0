<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationsController extends Controller
{
    public function unread(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success'=>false]);
        }

        $items = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get(['id','message','link','created_at']);

        // Get total unread count (not just the limited items)
        $totalUnreadCount = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        // Mark fetched items as read (optional) or keep for explicit mark-read
        // Notification::whereIn('id', $items->pluck('id'))->update(['is_read'=>true]);

        return response()->json([
            'success' => true,
            'count' => $totalUnreadCount, // Return total unread count, not just items returned
            'notifications' => $items->map(function($n){
                return [
                    'id' => $n->id,
                    'message' => $n->message,
                    'link' => $n->link,
                    'time' => optional($n->created_at)->diffForHumans(),
                ];
            }),
        ]);
    }

    public function dropdown(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success'=>false]);
        }
        
        // Get only the 5 most recent notifications
        $items = Notification::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get(['id','message','link','is_read','created_at']);
        
        // Get total unread count (all unread, not just the 5 shown)
        $totalUnreadCount = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'success'=> true,
            'count_unread' => $totalUnreadCount, // Total unread count, not just the 5 shown
            'items' => $items->map(function($n){
                return [
                    'id'=>$n->id,
                    'message'=>$n->message,
                    'link'=>$n->link,
                    'is_read'=>$n->is_read,
                    'time'=> optional($n->created_at)->diffForHumans(),
                ];
            })
        ]);
    }

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        $notification->update(['is_read'=>true]);
        return response()->json(['success'=>true]);
    }
}








