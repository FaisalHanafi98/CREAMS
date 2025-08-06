<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Volunteer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class VolunteerController extends Controller
{
/**
* Display the volunteer page
*
* @return \Illuminate\View\View
*/
public function index()
{
return view('volunteers.home');
}
/**
 * Handle volunteer form submission
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function submit(Request $request)
{
    try {
        Log::info('Volunteer form submission started', [
            'email' => $request->email,
            'name' => $request->first_name . ' ' . $request->last_name
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'interest' => 'required|string',
            'availability' => 'required|array|min:1',
            'commitment' => 'required|string',
            'motivation' => 'required|string',
            'consent' => 'required|accepted'
        ], [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email',
            'phone.required' => 'Phone number is required',
            'birth_date.date' => 'Please provide a valid birth date',
            'birth_date.before' => 'Birth date must be before today',
            'gender.in' => 'Please select a valid gender option',
            'emergency_contact_name.max' => 'Emergency contact name cannot exceed 255 characters',
            'emergency_contact_phone.max' => 'Emergency contact phone cannot exceed 20 characters',
            'interest.required' => 'Please select an area of interest',
            'availability.required' => 'Please select at least one availability option',
            'availability.min' => 'Please select at least one availability option',
            'commitment.required' => 'Please select your time commitment',
            'motivation.required' => 'Please tell us what motivates you',
            'consent.required' => 'You must agree to the terms',
            'consent.accepted' => 'You must agree to the terms'
        ]);

        if ($validator->fails()) {
            Log::warning('Volunteer validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the highlighted errors and try again.');
        }

        $validatedData = $validator->validated();

        // Save application to database
        $application = Volunteer::create([
            'volunteer_name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'volunteer_email' => strtolower(trim($validatedData['email'])),
            'volunteer_phone' => $validatedData['phone'],
            'volunteer_address' => $request->address ?: '',
            'volunteer_birth_date' => $validatedData['birth_date'] ?? '1990-01-01',
            'volunteer_gender' => $validatedData['gender'] ?? 'Other',
            'volunteer_skills' => $request->skills ?: '',
            'volunteer_experience' => $request->experience ?: '',
            'volunteer_availability' => implode(', ', $validatedData['availability']),
            'volunteer_status' => 'pending',
            'volunteer_start_date' => now()->format('Y-m-d'),
            'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',
            'emergency_contact_phone' => $validatedData['emergency_contact_phone'] ?? '',
        ]);

        Log::info('Volunteer application saved successfully', [
            'id' => $application->id,
            'email' => $application->volunteer_email
        ]);

        // Send confirmation email to volunteer (will be logged with log driver)
        try {
            Mail::raw(
                "Dear {$application->volunteer_name},\n\n" .
                "Thank you for your interest in volunteering with IIUM PD-CARE!\n\n" .
                "We have successfully received your volunteer application and are excited about your willingness to support children with special needs in our community.\n\n" .
                "Application Summary:\n" .
                "- Name: {$application->volunteer_name}\n" .
                "- Email: {$application->volunteer_email}\n" .
                "- Phone: {$application->volunteer_phone}\n" .
                "- Area of Interest: Volunteer Work\n" .
                "- Availability: {$application->volunteer_availability}\n" .
                "- Skills: {$application->volunteer_skills}\n" .
                "- Application ID: #VA" . str_pad($application->id, 6, '0', STR_PAD_LEFT) . "\n\n" .
                "What happens next?\n" .
                "1. Our volunteer coordinator will review your application\n" .
                "2. We will contact you within 7-10 business days\n" .
                "3. If selected, you'll be invited for an interview\n" .
                "4. Successful candidates will undergo orientation and training\n\n" .
                "If you have any questions, please don't hesitate to contact us at pdcare@iium.edu.my\n\n" .
                "Thank you again for your commitment to making a difference!\n\n" .
                "Best regards,\n" .
                "IIUM PD-CARE Volunteer Coordination Team", 
                function ($message) use ($application) {
                    $message->to($application->volunteer_email, $application->volunteer_name)
                            ->from(config('mail.from.address'), config('mail.from.name'))
                            ->subject('Volunteer Application Received - IIUM PD-CARE');
                }
            );
            
            Log::info('Volunteer confirmation email sent', ['email' => $application->volunteer_email]);
        } catch (\Exception $e) {
            Log::error('Failed to send volunteer confirmation email', [
                'error' => $e->getMessage(),
                'email' => $application->volunteer_email
            ]);
        }

        // Send admin notification (will be logged with log driver)
        try {
            $adminEmail = config('mail.from.address', 'pdcareuser1@gmail.com');
            
            Mail::raw(
                "New volunteer application received!\n\n" .
                "APPLICANT DETAILS:\n" .
                "==================\n" .
                "Name: {$application->volunteer_name}\n" .
                "Email: {$application->volunteer_email}\n" .
                "Phone: {$application->volunteer_phone}\n" .
                "Address: " . ($application->volunteer_address ?: 'Not provided') . "\n" .
                "" .
                "" .
                "VOLUNTEER PREFERENCES:\n" .
                "=====================\n" .
                "Area of Interest: Volunteer Work\n" .
                "" .
                "Availability: {$application->volunteer_availability}\n" .
                "" .
                "Skills: " . ($application->volunteer_skills ?: 'Not specified') . "\n\n" .
                "" .
                "EXPERIENCE:\n" .
                "===========\n" . 
                ($application->volunteer_experience ?: 'No previous experience specified') . "\n\n" .
                "ADDITIONAL INFO:\n" .
                "===============\n" .
                "" .
                "Application ID: #VA" . str_pad($application->id, 6, '0', STR_PAD_LEFT) . "\n" .
                "Submitted: " . $application->created_at->format('F j, Y \a\t g:i A') . "\n" .
                "" .
                "Please log in to the admin panel to review this application.",
                function ($message) use ($adminEmail, $application) {
                    $message->to($adminEmail)
                            ->from(config('mail.from.address'), config('mail.from.name'))
                            ->subject('🆕 New Volunteer Application - ' . $application->volunteer_name);
                }
            );
            
            Log::info('Admin notification sent');
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', ['error' => $e->getMessage()]);
        }

        // Redirect back with success message
        return redirect()->route('volunteer')
            ->with('success', 'Thank you for your volunteer application! We have received your submission and sent a confirmation email to ' . $application->volunteer_email . '. We will contact you within 7-10 business days regarding the next steps.');
        
    } catch (\Exception $e) {
        Log::error('Error in volunteer submission', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('volunteer')
            ->with('error', 'We encountered an issue processing your application. Please try again, or contact us directly at pdcare@iium.edu.my.')
            ->withInput();
    }
}

/**
 * Get volunteer applications for admin
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getApplications(Request $request)
{
    try {
        $query = Volunteer::with(['centre', 'approvedByUser'])
            ->orderBy('created_at', 'desc');

        // Filter by centre if user is centre admin
        $userRole = session('role');
        $centreId = session('centre_id');
        
        if ($userRole === 'centre_admin' && $centreId) {
            $query->where(function($q) use ($centreId) {
                $q->where('centre_id', $centreId)
                  ->orWhereNull('centre_id'); // Include unassigned applications
            });
        }

        // Additional filters
        if ($request->status) {
            $query->where('volunteer_status', $request->status);
        }

        if ($request->centre_id) {
            $query->where('centre_id', $request->centre_id);
        }

        $applications = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching volunteer applications', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error fetching applications'
        ], 500);
    }
}

/**
 * Show volunteer application details
 *
 * @param int $id
 * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
 */
public function show($id)
{
    try {
        $application = Volunteer::with(['centre', 'approvedByUser'])->findOrFail($id);
        
        // Return JSON for AJAX requests
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $application
            ]);
        }
        
        // Return view for direct access
        return view('admin.volunteers.show', compact('application'));
    } catch (\Exception $e) {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Volunteer application not found.'
            ], 404);
        }
        
        return redirect()->route('admin.volunteers.index')
            ->with('error', 'Volunteer application not found.');
    }
}

/**
 * Update volunteer application status
 *
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */
public function updateStatus(Request $request, $id)
{
    try {
        $application = Volunteer::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,active,inactive',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application->volunteer_status = $request->status;
        // Admin notes functionality would require additional table columns
        $application->save();

        Log::info('Volunteer application status updated', [
            'application_id' => $id,
            'new_status' => $request->status,
            'updated_by' => session('id')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating volunteer application status', [
            'application_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error updating application status'
        ], 500);
    }
}

/**
 * Approve volunteer application
 *
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */
public function approve(Request $request, $id)
{
    try {
        $application = Volunteer::findOrFail($id);
        
        // Verify admin has permission to approve for this centre
        $userRole = session('role');
        $centreId = session('centre_id');
        $adminUserId = session('id');
        
        if ($userRole === 'centre_admin' && !$centreId) {
            return response()->json([
                'success' => false,
                'message' => 'Centre assignment required'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'centre_id' => ($userRole === 'admin' && !$centreId) ? 'required|exists:centres,id' : 'nullable|exists:centres,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Use centre from session for centre admins, or from request for system admins
        // For admin role, use their assigned centre if they have one, otherwise use request
        if ($userRole === 'admin' && $centreId) {
            $assignCentreId = $centreId; // Admin of specific centre (like Gombak) auto-assigns to their centre
        } elseif ($userRole === 'centre_admin') {
            $assignCentreId = $centreId; // Centre admin always assigns to their centre
        } else {
            $assignCentreId = $request->centre_id; // System admin can choose centre
        }

        // Approve the application
        $application->approve($assignCentreId, $adminUserId, $request->notes);

        // Send approval email
        $this->sendApprovalEmail($application);

        Log::info('Volunteer application approved', [
            'application_id' => $id,
            'approved_by' => $adminUserId,
            'centre_id' => $assignCentreId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application approved successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error approving volunteer application', [
            'application_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error approving application'
        ], 500);
    }
}

/**
 * Reject volunteer application
 *
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */
public function reject(Request $request, $id)
{
    try {
        $application = Volunteer::findOrFail($id);
        $adminUserId = session('id');
        
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Reject the application
        $application->reject($adminUserId, $request->notes);

        // Send rejection email
        $this->sendRejectionEmail($application);

        Log::info('Volunteer application rejected', [
            'application_id' => $id,
            'rejected_by' => $adminUserId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected'
        ]);

    } catch (\Exception $e) {
        Log::error('Error rejecting volunteer application', [
            'application_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error rejecting application'
        ], 500);
    }
}

/**
 * Send approval email to volunteer
 *
 * @param Volunteer $application
 * @return void
 */
private function sendApprovalEmail($application)
{
    try {
        $centreName = $application->centre ? $application->centre->centre_name : 'IIUM PD-CARE';
        
        Mail::send('emails.volunteer-approval', [
            'volunteer' => $application,
            'centreName' => $centreName
        ], function ($message) use ($application) {
            $message->to($application->volunteer_email, $application->volunteer_name)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Volunteer Application Approved - Welcome to IIUM PD-CARE!');
        });

        Log::info('Volunteer approval email sent', [
            'application_id' => $application->id,
            'email' => $application->volunteer_email
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to send volunteer approval email', [
            'application_id' => $application->id,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Send rejection email to volunteer
 *
 * @param Volunteer $application
 * @return void
 */
private function sendRejectionEmail($application)
{
    try {
        Mail::send('emails.volunteer-rejection', [
            'volunteer' => $application
        ], function ($message) use ($application) {
            $message->to($application->volunteer_email, $application->volunteer_name)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Volunteer Application Update - IIUM PD-CARE');
        });

        Log::info('Volunteer rejection email sent', [
            'application_id' => $application->id,
            'email' => $application->volunteer_email
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to send volunteer rejection email', [
            'application_id' => $application->id,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Admin dashboard for volunteer management
 *
 * @return \Illuminate\View\View
 */
public function adminIndex()
{
    try {
        $userRole = session('role');
        $centreId = session('centre_id');
        
        // Get statistics
        $stats = [
            'pending' => Volunteer::pending()->when($userRole === 'centre_admin' && $centreId, 
                function($q) use ($centreId) {
                    return $q->where(function($query) use ($centreId) {
                        $query->where('centre_id', $centreId)->orWhereNull('centre_id');
                    });
                })->count(),
            'approved' => Volunteer::approved()->when($userRole === 'centre_admin' && $centreId,
                function($q) use ($centreId) {
                    return $q->where('centre_id', $centreId);
                })->count(),
            'rejected' => Volunteer::rejected()->when($userRole === 'centre_admin' && $centreId,
                function($q) use ($centreId) {
                    return $q->where('centre_id', $centreId);
                })->count()
        ];

        // Get applications data for initial load
        $query = Volunteer::with(['centre', 'approvedByUser'])
            ->orderBy('created_at', 'desc');

        // Filter by centre if user is centre admin
        if ($userRole === 'centre_admin' && $centreId) {
            $query->where(function($q) use ($centreId) {
                $q->where('centre_id', $centreId)
                  ->orWhereNull('centre_id'); // Include unassigned applications
            });
        }

        // Apply filters from request
        $request = request();
        if ($request->status) {
            $query->where('volunteer_status', $request->status);
        }
        if ($request->centre_id) {
            $query->where('centre_id', $request->centre_id);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applications = $query->paginate(15);

        return view('admin.volunteers.index', compact('stats', 'applications'));
        
    } catch (\Exception $e) {
        Log::error('Error loading volunteer admin page', [
            'error' => $e->getMessage()
        ]);
        
        return redirect()->route('dashboard')->with('error', 'Error loading volunteer management');
    }
}
}