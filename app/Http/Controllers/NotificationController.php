<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifications;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $role = session('role');
        $id = session('id');
        
        $notifications = Notifications::where('user_id', $id)
            ->where('user_type', $role)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $unreadCount = Notifications::where('user_id', $id)
            ->where('user_type', $role)
            ->where('read', false)
            ->count();
            
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }
    
    /**
     * Display the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $notification = Notifications::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to view this notification
        if ($notification->user_id != $userId || $notification->user_type != $role) {
            return redirect()->route('notifications.index')
                ->with('error', 'You are not authorized to view this notification');
        }
        
        // Mark as read if not already
        if (!$notification->read) {
            $notification->read = true;
            $notification->read_at = now();
            $notification->save();
        }
            
        return view('notifications.show', compact('notification'));
    }
    
    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $notification = Notifications::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to mark this notification as read
        if ($notification->user_id != $userId || $notification->user_type != $role) {
            return redirect()->route('notifications.index')
                ->with('error', 'You are not authorized to mark this notification as read');
        }
        
        $notification->read = true;
        $notification->read_at = now();
        $notification->save();
        
        return redirect()->back()
            ->with('success', 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        $role = session('role');
        $userId = session('id');
        
        Notifications::where('user_id', $userId)
            ->where('user_type', $role)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
            
        return redirect()->back()
            ->with('success', 'All notifications marked as read');
    }
    
    /**
     * Delete the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $notification = Notifications::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to delete this notification
        if ($notification->user_id != $userId || $notification->user_type != $role) {
            return redirect()->route('notifications.index')
                ->with('error', 'You are not authorized to delete this notification');
        }
        
        $notification->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully');
    }
    
    /**
     * Delete all read notifications.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearRead()
    {
        $role = session('role');
        $userId = session('id');
        
        Notifications::where('user_id', $userId)
            ->where('user_type', $role)
            ->where('read', true)
            ->delete();
            
        return redirect()->route('notifications.index')
            ->with('success', 'All read notifications have been cleared');
    }
    
    /**
     * Get unread notifications as JSON for AJAX requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnread()
    {
        $role = session('role');
        $userId = session('id');
        
        $notifications = Notifications::where('user_id', $userId)
            ->where('user_type', $role)
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $count = $notifications->count();
        
        // Format notifications for display
        $formattedNotifications = [];
        foreach ($notifications as $notification) {
            $formattedNotifications[] = [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'time' => $notification->created_at->diffForHumans(),
                'url' => route('notifications.show', $notification->id)
            ];
        }
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'notifications' => $formattedNotifications
        ]);
    }
}