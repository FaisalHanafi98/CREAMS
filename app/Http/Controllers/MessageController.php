<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Messages;
use App\Models\Notifications;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;

class MessageController extends Controller
{
    /**
     * Display a listing of the messages.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $role = session('role');
        $id = session('id');
        
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
    }
    
    /**
     * Show the form for creating a new message.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get potential recipients based on role
        $role = session('role');
        
        $admins = Admins::all(['id', 'name', 'email']);
        $supervisors = Supervisors::all(['id', 'name', 'email']);
        $teachers = Teachers::all(['id', 'name', 'email']);
        $ajks = AJKs::all(['id', 'name', 'email']);
        
        return view('messages.create', compact('admins', 'supervisors', 'teachers', 'ajks'));
    }
    
    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
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
        
        // Create message
        $message = new Messages();
        $message->sender_id = session('id');
        $message->sender_type = session('role');
        $message->recipient_id = $request->recipient_id;
        $message->recipient_type = $request->recipient_type;
        $message->subject = $request->subject;
        $message->content = $request->content;
        $message->read = false;
        $message->save();
        
        // Create notification for recipient
        $notification = new Notifications();
        $notification->user_id = $request->recipient_id;
        $notification->user_type = $request->recipient_type;
        $notification->type = 'message';
        $notification->title = 'New Message';
        $notification->content = 'You have received a new message from ' . session('name');
        $notification->read = false;
        $notification->save();
        
        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully');
    }
    
    /**
     * Display the specified message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $message = Messages::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to view this message
        if (
            ($message->recipient_id != $userId || $message->recipient_type != $role) &&
            ($message->sender_id != $userId || $message->sender_type != $role)
        ) {
            return redirect()->route('messages.index')
                ->with('error', 'You are not authorized to view this message');
        }
        
        // Mark as read if the user is the recipient
        if ($message->recipient_id == $userId && $message->recipient_type == $role && !$message->read) {
            $message->read = true;
            $message->read_at = now();
            $message->save();
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
    }
    
    /**
     * Show the form for replying to a message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function reply($id)
    {
        $message = Messages::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to reply to this message
        if (
            ($message->recipient_id != $userId || $message->recipient_type != $role) &&
            ($message->sender_id != $userId || $message->sender_type != $role)
        ) {
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
    }
    
    /**
     * Mark message as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $message = Messages::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to mark this message as read
        if ($message->recipient_id != $userId || $message->recipient_type != $role) {
            return redirect()->route('messages.index')
                ->with('error', 'You are not authorized to mark this message as read');
        }
        
        $message->read = true;
        $message->read_at = now();
        $message->save();
        
        return redirect()->back()
            ->with('success', 'Message marked as read');
    }
    
    /**
     * Mark all messages as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        $role = session('role');
        $userId = session('id');
        
        Messages::where('recipient_id', $userId)
            ->where('recipient_type', $role)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
            
        return redirect()->back()
            ->with('success', 'All messages marked as read');
    }
    
    /**
     * Delete the specified message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $message = Messages::findOrFail($id);
        $role = session('role');
        $userId = session('id');
        
        // Check if the user is authorized to delete this message
        if (
            ($message->recipient_id != $userId || $message->recipient_type != $role) &&
            ($message->sender_id != $userId || $message->sender_type != $role)
        ) {
            return redirect()->route('messages.index')
                ->with('error', 'You are not authorized to delete this message');
        }
        
        $message->delete();
        
        return redirect()->route('messages.index')
            ->with('success', 'Message deleted successfully');
    }
}