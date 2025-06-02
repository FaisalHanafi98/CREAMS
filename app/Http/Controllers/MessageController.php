<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Messages;
use App\Models\Notifications;
use App\Models\Users;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;

class MessageController extends Controller
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
     * Display a listing of the messages.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $role = session('role');
            $id = session('id');
            
            Log::info('Messages index accessed', [
                'user_id' => $id,
                'role' => $role
            ]);
            
            $inbox = Messages::where('recipient_id', $id)
                ->where('recipient_type', $role)
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'inbox_page');
                
            $sent = Messages::where('sender_id', $id)
                ->where('sender_type', $role)
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'sent_page');
                
            $unreadCount = Messages::where('recipient_id', $id)
                ->where('recipient_type', $role)
                ->where('read', false)
                ->count();
                
            return view('messages.index', compact('inbox', 'sent', 'unreadCount'));
        } catch (\Exception $e) {
            Log::error('Error loading messages index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Failed to load messages. Please try again later.');
        }
    }
    
    /**
     * Show the form for creating a new message.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Get potential recipients based on role
            $role = session('role');
            $id = session('id');
            
            Log::info('Message create form accessed', [
                'user_id' => $id,
                'role' => $role
            ]);
            
            // Get users for each role
            $admins = Admins::where('id', '!=', ($role === 'admin' ? $id : 0))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
                
            $supervisors = Supervisors::where('id', '!=', ($role === 'supervisor' ? $id : 0))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
                
            $teachers = Teachers::where('id', '!=', ($role === 'teacher' ? $id : 0))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
                
            $ajks = AJKs::where('id', '!=', ($role === 'ajk' ? $id : 0))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
            
            return view('messages.create', compact('admins', 'supervisors', 'teachers', 'ajks'));
        } catch (\Exception $e) {
            Log::error('Error loading message create form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('messages.index')
                ->with('error', 'Failed to load the compose form. Please try again later.');
        }
    }
    
    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipient_id' => 'required|integer',
                'recipient_type' => 'required|string|in:admin,supervisor,teacher,ajk',
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $senderId = session('id');
            $senderRole = session('role');
            $senderName = session('name');
            
            Log::info('Storing new message', [
                'sender_id' => $senderId,
                'sender_role' => $senderRole,
                'recipient_id' => $request->recipient_id,
                'recipient_type' => $request->recipient_type,
                'subject' => $request->subject
            ]);
            
            // Create message
            $message = new Messages();
            $message->sender_id = $senderId;
            $message->sender_type = $senderRole;
            $message->recipient_id = $request->recipient_id;
            $message->recipient_type = $request->recipient_type;
            $message->subject = $request->subject;
            $message->content = $request->content;
            $message->read = false;
            
            if(!$message->save()) {
                Log::error('Failed to save message', [
                    'sender_id' => $senderId,
                    'recipient_id' => $request->recipient_id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Failed to send message. Please try again.')
                    ->withInput();
            }
            
            // Create notification for recipient
            $notification = new Notifications();
            $notification->user_id = $request->recipient_id;
            $notification->user_type = $request->recipient_type;
            $notification->type = 'message';
            $notification->title = 'New Message';
            $notification->content = 'You have received a new message from ' . $senderName;
            $notification->read = false;
            
            if(!$notification->save()) {
                Log::warning('Failed to create notification for message', [
                    'message_id' => $message->id,
                    'recipient_id' => $request->recipient_id
                ]);
                
                // Continue anyway as the message was sent successfully
            }
            
            Log::info('Message sent successfully', [
                'message_id' => $message->id,
                'notification_created' => isset($notification->id)
            ]);
            
            return redirect()->route('messages.index')
                ->with('success', 'Message sent successfully');
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while sending the message. Please try again later.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $message = Messages::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Showing message details', [
                'message_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to view this message
            if (
                ($message->recipient_id != $userId || $message->recipient_type != $role) &&
                ($message->sender_id != $userId || $message->sender_type != $role)
            ) {
                Log::warning('Unauthorized message access attempt', [
                    'message_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to view this message');
            }
            
            // Mark as read if the user is the recipient
            if ($message->recipient_id == $userId && $message->recipient_type == $role && !$message->read) {
                $message->read = true;
                $message->read_at = now();
                $message->save();
                
                Log::info('Message marked as read', [
                    'message_id' => $id,
                    'user_id' => $userId
                ]);
            }
            
            // Get conversation history
            $conversation = Messages::where(function($query) use ($message) {
                    $query->where('sender_id', $message->sender_id)
                        ->where('sender_type', $message->sender_type)
                        ->where('recipient_id', $message->recipient_id)
                        ->where('recipient_type', $message->recipient_type);
                })
                ->orWhere(function($query) use ($message) {
                    $query->where('sender_id', $message->recipient_id)
                        ->where('sender_type', $message->recipient_type)
                        ->where('recipient_id', $message->sender_id)
                        ->where('recipient_type', $message->sender_type);
                })
                ->orderBy('created_at', 'asc')
                ->get();
                
            return view('messages.show', compact('message', 'conversation'));
        } catch (\Exception $e) {
            Log::error('Error showing message', [
                'message_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('messages.index')
                ->with('error', 'Failed to load message. Please try again later.');
        }
    }
    
    /**
     * Show the form for replying to a message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function reply($id)
    {
        try {
            $message = Messages::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Message reply form accessed', [
                'message_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to reply to this message
            if (
                ($message->recipient_id != $userId || $message->recipient_type != $role) &&
                ($message->sender_id != $userId || $message->sender_type != $role)
            ) {
                Log::warning('Unauthorized message reply attempt', [
                    'message_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to reply to this message');
            }
            
            // Set reply details
            if ($message->recipient_id == $userId && $message->recipient_type == $role) {
                // If user is the recipient, reply to the sender
                $recipient_id = $message->sender_id;
                $recipient_type = $message->sender_type;
                $recipient_name = $message->sender_name;
            } else {
                // If user is the sender, reply to the recipient
                $recipient_id = $message->recipient_id;
                $recipient_type = $message->recipient_type;
                $recipient_name = $message->recipient_name;
            }
            
            $subject = 'Re: ' . $message->subject;
            
            return view('messages.reply', compact('message', 'recipient_id', 'recipient_type', 'recipient_name', 'subject'));
        } catch (\Exception $e) {
            Log::error('Error loading message reply form', [
                'message_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('messages.index')
                ->with('error', 'Failed to load reply form. Please try again later.');
        }
    }
    
    /**
     * Mark message as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        try {
            $message = Messages::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Marking message as read', [
                'message_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to mark this message as read
            if ($message->recipient_id != $userId || $message->recipient_type != $role) {
                Log::warning('Unauthorized attempt to mark message as read', [
                    'message_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to mark this message as read');
            }
            
            $message->read = true;
            $message->read_at = now();
            $message->save();
            
            Log::info('Message marked as read successfully', [
                'message_id' => $id,
                'user_id' => $userId
            ]);
            
            return redirect()->back()
                ->with('success', 'Message marked as read');
        } catch (\Exception $e) {
            Log::error('Error marking message as read', [
                'message_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to mark message as read. Please try again later.');
        }
    }
    
    /**
     * Mark all messages as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::info('Marking all messages as read', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            $count = Messages::where('recipient_id', $userId)
                ->where('recipient_type', $role)
                ->where('read', false)
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
                
            Log::info('Marked all messages as read', [
                'count' => $count,
                'user_id' => $userId
            ]);
            
            return redirect()->back()
                ->with('success', $count . ' messages marked as read');
        } catch (\Exception $e) {
            Log::error('Error marking all messages as read', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to mark messages as read. Please try again later.');
        }
    }
    
    /**
     * Delete the specified message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $message = Messages::findOrFail($id);
            $role = session('role');
            $userId = session('id');
            
            Log::info('Deleting message', [
                'message_id' => $id,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Check if the user is authorized to delete this message
            if (
                ($message->recipient_id != $userId || $message->recipient_type != $role) &&
                ($message->sender_id != $userId || $message->sender_type != $role)
            ) {
                Log::warning('Unauthorized message delete attempt', [
                    'message_id' => $id,
                    'user_id' => $userId,
                    'role' => $role
                ]);
                
                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to delete this message');
            }
            
            $message->delete();
            
            Log::info('Message deleted successfully', [
                'message_id' => $id,
                'user_id' => $userId
            ]);
            
            return redirect()->route('messages.index')
                ->with('success', 'Message deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting message', [
                'message_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('messages.index')
                ->with('error', 'Failed to delete message. Please try again later.');
        }
    }
}