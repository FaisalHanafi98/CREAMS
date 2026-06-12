<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use App\Models\User;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $userId = session('id');

            Log::info('Message index accessed', ['user_id' => $userId]);

            $sent = Message::where('sender_id', $userId)
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->get();

            $receivedPivots = MessageRecipient::where('recipient_id', $userId)
                ->where('is_deleted', false)
                ->get()
                ->keyBy('message_id');

            $received = Message::whereIn('id', $receivedPivots->keys())
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->get();

            $messages = $received->map(function ($msg) use ($receivedPivots) {
                return [
                    'id' => $msg->id,
                    'read' => (bool) ($receivedPivots[$msg->id]->is_read ?? false),
                    'sender_name' => $msg->sender ? $msg->sender->name : 'Unknown',
                    'sender_role' => 'Inbox',
                    'subject' => $msg->subject,
                    'message' => $msg->message_body,
                    'date' => $msg->sent_at ?? $msg->created_at,
                ];
            })->concat($sent->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'read' => $msg->status === 'read',
                    'sender_name' => $msg->sender ? $msg->sender->name : 'You',
                    'sender_role' => 'Sent',
                    'subject' => $msg->subject,
                    'message' => $msg->message_body,
                    'date' => $msg->sent_at ?? $msg->created_at,
                ];
            }))->sortByDesc('date')->values()->all();

            $recipients = User::where('id', '!=', $userId)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('messages.home', compact('messages', 'recipients'));
        } catch (\Exception $e) {
            Log::error('Error loading messages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to load messages. Please try again later.');
        }
    }

    public function create()
    {
        // Compose is handled via modal in the index view
        return redirect()->route('messages.index');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipient_id' => 'required|integer|exists:staffs,id',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $senderId = session('id');
            $senderName = session('name');

            Log::info('Sending message', [
                'sender_id' => $senderId,
                'recipient_id' => $request->recipient_id,
                'subject' => $request->subject
            ]);

            $message = Message::create([
                'sender_id' => $senderId,
                'subject' => $request->subject,
                'message_body' => $request->message,
                'priority' => $request->priority ?? 'normal',
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Without this row the message never appears in any inbox —
            // index() resolves received mail through message_recipients.
            MessageRecipient::create([
                'message_id' => $message->id,
                'recipient_id' => $request->recipient_id,
                'recipient_type' => 'user',
                'is_read' => false,
            ]);

            // Notify recipient via notification system
            Notification::create([
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $request->recipient_id,
                'type' => 'message',
                'data' => [
                    'title' => 'New Message',
                    'message' => 'You have received a new message from ' . $senderName,
                    'message_id' => $message->id,
                ],
            ]);

            Log::info('Message sent successfully', ['message_id' => $message->id]);

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

    public function show($id)
    {
        try {
            $message = Message::with('sender')->findOrFail($id);
            $userId = session('id');

            Log::info('Showing message', ['message_id' => $id, 'user_id' => $userId]);

            if ($message->sender_id != $userId) {
                Log::warning('Unauthorized message access', [
                    'message_id' => $id,
                    'user_id' => $userId
                ]);

                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to view this message');
            }

            $conversation = [
                'with' => 'Recipient',
                'with_role' => '',
                'messages' => [
                    [
                        'is_mine' => true,
                        'sender_name' => $message->sender ? $message->sender->name : 'You',
                        'date' => $message->sent_at ?? $message->created_at,
                        'content' => $message->message_body,
                    ],
                ],
            ];

            return view('messages.show', compact('conversation'));
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

    public function destroy($id)
    {
        try {
            $message = Message::findOrFail($id);
            $userId = session('id');

            Log::info('Deleting message', ['message_id' => $id, 'user_id' => $userId]);

            if ($message->sender_id != $userId) {
                Log::warning('Unauthorized message delete attempt', [
                    'message_id' => $id,
                    'user_id' => $userId
                ]);

                return redirect()->route('messages.index')
                    ->with('error', 'You are not authorized to delete this message');
            }

            $message->delete();

            Log::info('Message deleted', ['message_id' => $id, 'user_id' => $userId]);

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
