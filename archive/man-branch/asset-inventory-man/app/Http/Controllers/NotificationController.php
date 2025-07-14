<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Notifications;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the notifications.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $role = session('role');
            $id = session('id');
            
            Log::info('Notifications index accessed', [
                'user_id' => $id,
                'role' => $role
            ]);
            
            $notifications = Notifications::where('user_id', $id)
                ->where('user_type', $role)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            $unreadCount = Notifications::where('user_id', $id)
                ->where('user_type', $role)
                ->where('read', false)
                ->count();
                
            return view('notifications.index', compact('notifications', 'unreadCount'));
        } catch (\Exception $e) {
            Log::error('Error loading notifications index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Failed to load notifications. Please try again later.');
        }
    }
    
    /**
     * Display the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $notification = Notifications::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Showing notification details', [
                'notification_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to view this notification
            if ($notification->user_id != $userId || $notification->user_type != $role) {
                Log::warning('Unauthorized notification access attempt', [
                    'notification_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('notifications.index')
                    ->with('error', 'You are not authorized to view this notification');
            }
            
            // Mark as read if not already
            if (!$notification->read) {
                $notification->read = true;
                $notification->read_at = now();
                $notification->save();
                
                Log::info('Notification marked as read', [
                    'notification_id' => $id,
                    'user_id' => $userId
                ]);
            }
                
            return view('notifications.show', compact('notification'));
        } catch (\Exception $e) {
            Log::error('Error showing notification', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('notifications.index')
                ->with('error', 'Failed to load notification. Please try again later.');
        }
    }
    
    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notifications::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Marking notification as read', [
                'notification_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to mark this notification as read
            if ($notification->user_id != $userId || $notification->user_type != $role) {
                Log::warning('Unauthorized attempt to mark notification as read', [
                    'notification_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('notifications.index')
                    ->with('error', 'You are not authorized to mark this notification as read');
            }
            
            $notification->read = true;
            $notification->read_at = now();
            $notification->save();
            
            Log::info('Notification marked as read successfully', [
                'notification_id' => $id,
                'user_id' => $userId
            ]);
            
            return redirect()->back()
                ->with('success', 'Notification marked as read');
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to mark notification as read. Please try again later.');
        }
    }
    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::info('Marking all notifications as read', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            $count = Notifications::where('user_id', $userId)
                ->where('user_type', $role)
                ->where('read', false)
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
                
            Log::info('Marked all notifications as read', [
                'count' => $count,
                'user_id' => $userId
            ]);
            
            return redirect()->back()
                ->with('success', $count . ' notifications marked as read');
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to mark notifications as read. Please try again later.');
        }
    }
    
    /**
     * Delete the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $notification = Notifications::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Deleting notification', [
                'notification_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to delete this notification
            if ($notification->user_id != $userId || $notification->user_type != $role) {
                Log::warning('Unauthorized notification delete attempt', [
                    'notification_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('notifications.index')
                    ->with('error', 'You are not authorized to delete this notification');
            }
            
            $notification->delete();
            
            Log::info('Notification deleted successfully', [
                'notification_id' => $id,
                'user_id' => $userId
            ]);
            
            return redirect()->route('notifications.index')
                ->with('success', 'Notification deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('notifications.index')
                ->with('error', 'Failed to delete notification. Please try again later.');
        }
    }
    
    /**
     * Delete all read notifications.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearRead()
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::info('Clearing read notifications', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            $count = Notifications::where('user_id', $userId)
                ->where('user_type', $role)
                ->where('read', true)
                ->delete();
                
            Log::info('Read notifications cleared', [
                'count' => $count,
                'user_id' => $userId
            ]);
            
            return redirect()->route('notifications.index')
                ->with('success', $count . ' read notifications have been cleared');
        } catch (\Exception $e) {
            Log::error('Error clearing read notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('notifications.index')
                ->with('error', 'Failed to clear read notifications. Please try again later.');
        }
    }
    
    /**
     * Get unread notifications as JSON for AJAX requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnread()
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::debug('Getting unread notifications via AJAX', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
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
            
            Log::debug('Returning unread notifications', [
                'count' => $count,
                'notifications' => count($formattedNotifications)
            ]);
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'notifications' => $formattedNotifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching unread notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications',
                'count' => 0,
                'notifications' => []
            ]);
        }
    }
}