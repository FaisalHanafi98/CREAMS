<?php
namespace App\Http\Controllers;
use App\Models\ContactMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class ContactController extends Controller
{
/**
* Display the contact page
*
* @return \Illuminate\View\View
*/
public function index()
{
try {
Log::info('Contact page accessed', [
'ip' => request()->ip(),
'user_agent' => request()->userAgent()
]);
        return view('contactus');
    } catch (\Exception $e) {
        Log::error('Error loading contact page', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return view('contactus');
    }
}

/**
 * Handle contact form submission
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function submit(Request $request)
{
    try {
        Log::info('Contact form submission started', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $request->reason
        ]);

        // Enhanced validation with detailed error messages
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'phone' => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]{8,}$/'
            ],
            'reason' => [
                'required',
                'in:services,support,volunteer,partnership,general,other,admission,complaint,feedback'
            ],
            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ],
            'subject' => [
                'nullable',
                'string',
                'max:255'
            ],
            'organization' => [
                'nullable',
                'string',
                'max:255'
            ],
            'preferred_contact_method' => [
                'nullable',
                'in:email,phone,both'
            ],
            'urgency' => [
                'nullable',
                'in:low,medium,high,urgent'
            ]
        ], [
            'name.required' => 'Your full name is required.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.min' => 'Phone number must be at least 8 digits.',
            'phone.regex' => 'Please provide a valid phone number.',
            'reason.required' => 'Please select a reason for contacting us.',
            'reason.in' => 'Please select a valid reason for contact.',
            'message.required' => 'Please provide your message.',
            'message.min' => 'Message must be at least 10 characters long.',
            'message.max' => 'Message cannot exceed 2000 characters.',
            'subject.max' => 'Subject cannot exceed 255 characters.',
            'organization.max' => 'Organization name cannot exceed 255 characters.'
        ]);

        if ($validator->fails()) {
            Log::warning('Contact form validation failed', [
                'email' => $request->email,
                'reason' => $request->reason,
                'errors' => $validator->errors()->toArray()
            ]);
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the highlighted errors and try again.');
        }

        $validatedData = $validator->validated();

        // Prepare enhanced data for database storage
        $contactData = [
            'name' => $this->formatName($validatedData['name']),
            'email' => strtolower(trim($validatedData['email'])),
            'phone' => $this->formatPhone($validatedData['phone'] ?? null),
            'reason' => $validatedData['reason'],
            'message' => trim($validatedData['message']),
            'subject' => $validatedData['subject'] ?? $this->generateSubject($validatedData['reason']),
            'organization' => $validatedData['organization'] ?? null,
            'preferred_contact_method' => $validatedData['preferred_contact_method'] ?? 'email',
            'urgency' => $validatedData['urgency'] ?? 'medium',
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'submitted_at' => now(),
        ];

        // Save to database with enhanced error handling
        $contact = ContactMessages::create($contactData);

        Log::info('Contact message saved to database', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'reason' => $contact->reason,
            'urgency' => $contact->urgency
        ]);

        // Send notification emails
        $this->sendNotificationEmails($contact, $validatedData);

        // Log successful submission
        Log::info('Contact form submission completed successfully', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'reason' => $contact->reason
        ]);

        // Redirect with success message based on urgency
        $successMessage = $this->getSuccessMessage($contact->urgency, $contact->reason);
        
        return redirect()->back()->with('success', $successMessage);

    } catch (\Exception $e) {
        Log::error('Error processing contact form submission', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['_token'])
        ]);

        return redirect()->back()
            ->with('error', 'We encountered an issue processing your message. Please try again, or contact us directly at pdcare@iium.edu.my.')
            ->withInput();
    }
}

/**
 * Send notification emails for contact submission
 *
 * @param ContactMessages $contact
 * @param array $validatedData
 * @return void
 */
private function sendNotificationEmails($contact, $validatedData)
{
    try {
        // Send confirmation email to user
        Mail::send('emails.contact-confirmation', [
            'contact' => $contact,
            'data' => $validatedData
        ], function ($message) use ($contact) {
            $message->to($contact->email, $contact->name)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Message Received - IIUM PD-CARE')
                    ->replyTo(config('mail.from.address'));
        });

        Log::info('Confirmation email sent to user', [
            'contact_id' => $contact->id,
            'email' => $contact->email
        ]);

        // Send notification email to admin with urgency handling
        $adminEmail = config('mail.admin_email', 'asbourne1998@gmail.com');
        $subject = $this->getAdminEmailSubject($contact);
        
        Mail::send('emails.contact-admin-notification', [
            'contact' => $contact,
            'data' => $validatedData
        ], function ($message) use ($adminEmail, $subject, $contact) {
            $message->to($adminEmail)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject)
                    ->replyTo($contact->email, $contact->name);
                    
            // Set priority for urgent messages
            if ($contact->urgency === 'urgent') {
                $message->priority(1);
            }
        });

        Log::info('Admin notification email sent', [
            'contact_id' => $contact->id,
            'admin_email' => $adminEmail,
            'urgency' => $contact->urgency
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to send contact form emails', [
            'contact_id' => $contact->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        // Don't throw exception - message was saved successfully
    }
}

/**
 * Format name with proper capitalization
 *
 * @param string $name
 * @return string
 */
private function formatName($name)
{
    return ucwords(strtolower(trim($name)));
}

/**
 * Format phone number
 *
 * @param string|null $phone
 * @return string|null
 */
private function formatPhone($phone)
{
    if (!$phone) return null;
    
    // Remove all non-numeric characters except +
    $phone = preg_replace('/[^\d+]/', '', $phone);
    
    // Add country code if not present
    if (!str_starts_with($phone, '+') && !str_starts_with($phone, '60')) {
        $phone = '+60' . ltrim($phone, '0');
    }
    
    return $phone;
}

/**
 * Generate subject based on reason
 *
 * @param string $reason
 * @return string
 */
private function generateSubject($reason)
{
    $subjects = [
        'services' => 'Inquiry About Rehabilitation Services',
        'support' => 'Support and Assistance Request',
        'volunteer' => 'Volunteer Opportunity Inquiry',
        'partnership' => 'Partnership Opportunity',
        'general' => 'General Inquiry',
        'admission' => 'Admission Inquiry',
        'complaint' => 'Complaint Submission',
        'feedback' => 'Feedback Submission',
        'other' => 'Contact Form Submission'
    ];

    return $subjects[$reason] ?? 'Contact Form Submission';
}

/**
 * Get success message based on urgency and reason
 *
 * @param string $urgency
 * @param string $reason
 * @return string
 */
private function getSuccessMessage($urgency, $reason)
{
    if ($urgency === 'urgent') {
        return 'Your urgent message has been received and flagged for immediate attention. We will respond within 24 hours.';
    }

    $messages = [
        'services' => 'Thank you for your interest in our rehabilitation services. We will contact you within 2-3 business days to discuss your needs.',
        'volunteer' => 'Thank you for your interest in volunteering! We will review your inquiry and contact you within a week.',
        'partnership' => 'Thank you for your partnership interest. Our team will review your proposal and respond within 5 business days.',
        'complaint' => 'Your complaint has been logged and will be reviewed by our management team. We will respond within 48 hours.',
        'admission' => 'Thank you for your admission inquiry. Our admissions team will contact you within 2-3 business days.',
        'feedback' => 'Thank you for your valuable feedback. We appreciate you taking the time to share your thoughts with us.'
    ];

    return $messages[$reason] ?? 'Thank you for contacting IIUM PD-CARE. We have received your message and will respond within 3-5 business days.';
}

/**
 * Get admin email subject with urgency and type
 *
 * @param ContactMessages $contact
 * @return string
 */
private function getAdminEmailSubject($contact)
{
    $prefix = $contact->urgency === 'urgent' ? '🚨 URGENT - ' : '';
    $typeMap = [
        'services' => 'Service Inquiry',
        'support' => 'Support Request',
        'volunteer' => 'Volunteer Inquiry',
        'partnership' => 'Partnership Request',
        'complaint' => 'Complaint',
        'admission' => 'Admission Inquiry',
        'feedback' => 'Feedback',
        'general' => 'General Inquiry',
        'other' => 'Contact Form'
    ];

    $type = $typeMap[$contact->reason] ?? 'Contact Form';
    
    return $prefix . 'New ' . $type . ' - ' . $contact->name;
}

/**
 * Get contact messages for admin (future use)
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getMessages()
{
    try {
        $messages = ContactMessages::with('assignedUser')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching contact messages', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error fetching messages'
        ], 500);
    }
}

/**
 * Update message status (future use)
 *
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */
public function updateStatus(Request $request, $id)
{
    try {
        $message = ContactMessages::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message->status = $request->status;
        if ($request->notes) {
            $message->admin_notes = $request->notes;
        }
        if ($request->assigned_to) {
            $message->assigned_to = $request->assigned_to;
        }
        $message->save();

        Log::info('Contact message status updated', [
            'message_id' => $id,
            'new_status' => $request->status,
            'updated_by' => session('id')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message status updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating contact message status', [
            'message_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error updating message status'
        ], 500);
    }
}
}