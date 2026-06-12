<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Volunteer;
use App\Models\Centre;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\HandlesErrors;
class VolunteerController extends Controller
{
    use HandlesErrors;

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
        $request->merge([
            'first_name' => trim((string) $request->input('first_name', '')),
            'last_name' => trim((string) $request->input('last_name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

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

        // Generate unique volunteer ID
        $volunteerIdNumber = Volunteer::count() + 1;
        $volunteerId = 'VOL' . sprintf('%04d', $volunteerIdNumber);
        
        // Ensure uniqueness
        while (Volunteer::where('volunteer_id', $volunteerId)->exists()) {
            $volunteerIdNumber++;
            $volunteerId = 'VOL' . sprintf('%04d', $volunteerIdNumber);
        }

        // Save application to database (using actual database columns)
        $application = Volunteer::create([
            'volunteer_id' => $volunteerId,
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'email' => strtolower(trim($validatedData['email'])),
            'phone' => $validatedData['phone'],
            'address' => $request->address ?: null,
            'date_of_birth' => $validatedData['birth_date'],
            'gender' => $validatedData['gender'],
            'occupation' => $request->occupation ?: null,
            'skills' => $request->skills ?: null,
            'availability' => implode(', ', $validatedData['availability']),
            'motivation' => $validatedData['motivation'],
            'status' => 'applied',
            'registration_date' => now()->toDateString(),
        ]);

        Log::info('Volunteer application saved successfully', [
            'id' => $application->id,
            'email' => $application->email
        ]);

        // Send confirmation email to volunteer (will be logged with log driver)
        try {
            Mail::raw(
                "Dear {$application->name},\n\n" .
                "Thank you for your interest in volunteering with IIUM PD-CARE!\n\n" .
                "We have successfully received your volunteer application and are excited about your willingness to support children with special needs in our community.\n\n" .
                "Application Summary:\n" .
                "- Name: {$application->name}\n" .
                "- Email: {$application->email}\n" .
                "- Phone: {$application->phone}\n" .
                "- Area of Interest: Volunteer Work\n" .
                "- Availability: {$application->availability}\n" .
                "- Skills: {$application->skills}\n" .
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
                    $message->to($application->email, $application->name)
                            ->from(config('mail.from.address'), config('mail.from.name'))
                            ->subject('Volunteer Application Received - IIUM PD-CARE');
                }
            );
            
            Log::info('Volunteer confirmation email sent', ['email' => $application->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send volunteer confirmation email', [
                'error' => $e->getMessage(),
                'email' => $application->email
            ]);
        }

        // Send admin notification (will be logged with log driver)
        try {
            $adminEmail = config('mail.from.address', 'pdcareuser1@gmail.com');
            
            Mail::raw(
                "New volunteer application received!\n\n" .
                "APPLICANT DETAILS:\n" .
                "==================\n" .
                "Name: {$application->name}\n" .
                "Email: {$application->email}\n" .
                "Phone: {$application->phone}\n" .
                "Address: " . ($application->address ?: 'Not provided') . "\n" .
                "" .
                "" .
                "VOLUNTEER PREFERENCES:\n" .
                "=====================\n" .
                "Area of Interest: Volunteer Work\n" .
                "" .
                "Availability: {$application->availability}\n" .
                "" .
                "Skills: " . ($application->skills ?: 'Not specified') . "\n\n" .
                "" .
                "MOTIVATION:\n" .
                "===========\n" . 
                ($application->motivation ?: 'No motivation specified') . "\n\n" .
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
                            ->subject('🆕 New Volunteer Application - ' . $application->name);
                }
            );
            
            Log::info('Admin notification sent');
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', ['error' => $e->getMessage()]);
        }

        // Redirect back with success message
        return redirect()->route('volunteer')
            ->with('volunteer_application_id', $application->volunteer_id)
            ->with('success', 'Thank you for your volunteer application! We have received your submission and sent a confirmation email to ' . $application->email . '. We will contact you within 7-10 business days regarding the next steps.');
        
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
        $query = Volunteer::with(['reviewedByUser', 'centre'])
            ->orderBy('created_at', 'desc');

        // All users can see all applications for now
        // Centre-based filtering removed as volunteers table doesn't have centre_id

        // Additional filters
        if ($request->status) {
            $query->where('status', $request->status);
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
        Log::info('Volunteer show method called', [
            'id' => $id,
            'wants_json' => request()->wantsJson(),
            'session_role' => session('role'),
            'session_user_id' => session('id')
        ]);
        
        $application = Volunteer::with(['reviewedByUser', 'centre'])->findOrFail($id);
        
        Log::info('Volunteer application found', [
            'application_id' => $application->id,
            'application_name' => $application->name
        ]);
        
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
        Log::error('Error in volunteer show method', [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Volunteer application not found.',
                'error' => $e->getMessage()
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
            'status' => 'required|in:applied,reviewed,approved,rejected,active,inactive',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application->status = $request->status;
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
        
        $adminUserId = session('id');
        
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Approve the application - centre assignment handled through reviewed_by relationship
        $application->approve($adminUserId, $request->notes);

        // Send approval email
        $this->sendApprovalEmail($application);

        Log::info('Volunteer application approved', [
            'application_id' => $id,
            'approved_by' => $adminUserId
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
        // Refresh the application to get updated relationships
        $application->refresh();
        
        // Default centre information
        $centreName = 'IIUM PD-CARE';
        $centreAddress = 'IIUM Campus, Gombak';
        $centrePhone = '+60 3-6196 4000';
        
        // Parse volunteer availability first to determine appropriate start date
        $availability = $application->availability;
        $today = \Carbon\Carbon::today();
        
        // Calculate start date based on volunteer's availability
        $startDate = $this->calculateStartDate($availability, $today);
        
        // Create schedule details
        $scheduleDetails = $this->parseVolunteerSchedule($availability, $startDate);
        
        Log::info('Sending approval email', [
            'application_id' => $application->id,
            'email' => $application->email,
            'centre' => $centreName,
            'start_date' => $startDate->format('Y-m-d'),
            'schedule' => $scheduleDetails
        ]);
        
        // Prepare email data
        $emailData = [
            'volunteer' => $application,
            'centreName' => $centreName,
            'centreAddress' => $centreAddress,
            'centrePhone' => $centrePhone,
            'startDate' => $startDate,
            'scheduleDetails' => $scheduleDetails
        ];
        
        // Use queue for better performance (if configured)
        if (config('queue.default') !== 'sync') {
            Mail::queue('emails.volunteer-approval', $emailData, function ($message) use ($application) {
                $message->to($application->email, $application->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('🎉 Volunteer Application Approved - Welcome to IIUM PD-CARE!');
            });
        } else {
            Mail::send('emails.volunteer-approval', $emailData, function ($message) use ($application) {
                $message->to($application->email, $application->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('🎉 Volunteer Application Approved - Welcome to IIUM PD-CARE!');
            });
        }

        Log::info('Volunteer approval email sent successfully', [
            'application_id' => $application->id,
            'email' => $application->email,
            'centre' => $centreName,
            'start_date' => $startDate->format('Y-m-d')
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to send volunteer approval email', [
            'application_id' => $application->id,
            'email' => $application->email ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Don't throw exception to avoid breaking the approval process
        // Admin will see the log error and can manually contact the volunteer
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
        // Refresh the application to get updated data
        $application->refresh();
        
        Log::info('Sending rejection email', [
            'application_id' => $application->id,
            'email' => $application->email,
            'rejection_reason' => $application->admin_notes ? 'Provided' : 'Not provided'
        ]);
        
        // Use queue for better performance (if configured)
        if (config('queue.default') !== 'sync') {
            Mail::queue('emails.volunteer-rejection', [
                'volunteer' => $application
            ], function ($message) use ($application) {
                $message->to($application->email, $application->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('📧 Volunteer Application Update - IIUM PD-CARE')
                        ->priority(3); // Normal priority
            });
        } else {
            Mail::send('emails.volunteer-rejection', [
                'volunteer' => $application
            ], function ($message) use ($application) {
                $message->to($application->email, $application->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('📧 Volunteer Application Update - IIUM PD-CARE')
                        ->priority(3); // Normal priority
            });
        }

        Log::info('Volunteer rejection email sent successfully', [
            'application_id' => $application->id,
            'email' => $application->email
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to send volunteer rejection email', [
            'application_id' => $application->id,
            'email' => $application->email ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Don't throw exception to avoid breaking the rejection process
        // Admin will see the log error and can manually contact the volunteer
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
        // Get statistics
        $stats = [
            'pending' => Volunteer::pending()->count(),
            'approved' => Volunteer::approved()->count(),
            'rejected' => Volunteer::rejected()->count(),
            'active' => Volunteer::active()->count(),
            'total' => Volunteer::count()
        ];

        // Get applications data for initial load
        $query = Volunteer::with(['reviewedByUser', 'centre'])
            ->orderBy('created_at', 'desc');

        // No centre-based filtering as volunteers table doesn't have centre_id

        // Apply filters from request
        $request = request();
        if ($request->status) {
            $query->where('status', $request->status);
        }
        // Centre filtering removed as volunteers table doesn't have centre_id
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

/**
 * Get centres for dropdown population
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getCentres()
{
    try {
        $centres = Centre::select('centre_id', 'centre_name')
            ->where('centre_status', 'active') // Only get active centres
            ->orderBy('centre_name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $centres
        ]);
    } catch (\Exception $e) {
        Log::error('Error loading centres for volunteer management', [
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error loading centres'
        ], 500);
    }
}


/**
 * Parse volunteer availability and create schedule details
 *
 * @param string $availability
 * @param \Carbon\Carbon $startDate
 * @return array
 */
private function parseVolunteerSchedule($availability, $startDate)
{
    $scheduleDetails = [
        'formatted_schedule' => [],
        'summary' => ''
    ];
    
    try {
        // Common availability patterns
        $availabilityLower = strtolower($availability);
        
        // Time patterns
        $morningTimes = ['morning', 'am', '9:00', '10:00', '8:00'];
        $afternoonTimes = ['afternoon', 'pm', '2:00', '3:00', '1:00', '4:00'];
        $eveningTimes = ['evening', '6:00', '7:00', '5:00'];
        
        // Day patterns  
        $weekdayPatterns = ['weekdays', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $weekendPatterns = ['weekend', 'saturday', 'sunday'];
        $specificDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        $isWeekdays = false;
        $isWeekends = false;
        $isMorning = false;
        $isAfternoon = false;
        $isEvening = false;
        $foundDays = [];
        
        // Check for time preferences
        foreach ($morningTimes as $time) {
            if (strpos($availabilityLower, $time) !== false) {
                $isMorning = true;
                break;
            }
        }
        
        foreach ($afternoonTimes as $time) {
            if (strpos($availabilityLower, $time) !== false) {
                $isAfternoon = true;
                break;
            }
        }
        
        foreach ($eveningTimes as $time) {
            if (strpos($availabilityLower, $time) !== false) {
                $isEvening = true;
                break;
            }
        }
        
        // Check for day preferences
        foreach ($weekdayPatterns as $pattern) {
            if (strpos($availabilityLower, $pattern) !== false) {
                if (in_array($pattern, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                    $foundDays[] = ucfirst($pattern);
                } else {
                    $isWeekdays = true;
                }
                break;
            }
        }
        
        foreach ($weekendPatterns as $pattern) {
            if (strpos($availabilityLower, $pattern) !== false) {
                if (in_array($pattern, ['saturday', 'sunday'])) {
                    $foundDays[] = ucfirst($pattern);
                } else {
                    $isWeekends = true;
                }
                break;
            }
        }
        
        // Default time if none specified
        if (!$isMorning && !$isAfternoon && !$isEvening) {
            $isMorning = true; // Default to morning
        }
        
        // Determine time slot
        $timeSlot = '';
        if ($isMorning) {
            $timeSlot = '9:00 AM - 12:00 PM';
        } elseif ($isAfternoon) {
            $timeSlot = '2:00 PM - 5:00 PM';
        } elseif ($isEvening) {
            $timeSlot = '6:00 PM - 8:00 PM';
        }
        
        // Determine days
        $workingDays = [];
        if ($isWeekdays || empty($foundDays)) {
            $workingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        } elseif ($isWeekends) {
            $workingDays = ['Saturday', 'Sunday'];
        } else {
            $workingDays = $foundDays;
        }
        
        // Create schedule for the week starting from next Monday
        $currentDate = $startDate->copy();
        for ($i = 0; $i < 7; $i++) {
            $dayName = $currentDate->format('l'); // Full day name
            if (in_array($dayName, $workingDays)) {
                $scheduleDetails['formatted_schedule'][] = [
                    'date' => $currentDate->format('F j, Y'),
                    'day' => $dayName,
                    'time' => $timeSlot,
                    'carbon_date' => $currentDate->copy()
                ];
            }
            $currentDate->addDay();
        }
        
        // Create summary
        $daysSummary = implode(', ', $workingDays);
        $scheduleDetails['summary'] = "{$daysSummary} from {$timeSlot}";
        
    } catch (\Exception $e) {
        Log::error('Error parsing volunteer schedule', [
            'availability' => $availability,
            'error' => $e->getMessage()
        ]);
        
        // Fallback schedule
        $scheduleDetails['formatted_schedule'] = [
            [
                'date' => $startDate->format('F j, Y'),
                'day' => 'Monday',
                'time' => '9:00 AM - 12:00 PM',
                'carbon_date' => $startDate->copy()
            ]
        ];
        $scheduleDetails['summary'] = 'Monday - Friday from 9:00 AM - 12:00 PM';
    }
    
    return $scheduleDetails;
}

/**
 * Calculate appropriate start date based on volunteer availability
 *
 * @param string $availability
 * @param \Carbon\Carbon $today
 * @return \Carbon\Carbon
 */
private function calculateStartDate($availability, $today)
{
    try {
        $availabilityLower = strtolower($availability);
        
        // Weekend patterns
        $weekendPatterns = ['weekend', 'saturday', 'sunday'];
        $weekdayPatterns = ['weekday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        
        $isWeekendVolunteer = false;
        $isWeekdayVolunteer = false;
        $specificDays = [];
        
        // Check for weekend preferences
        foreach ($weekendPatterns as $pattern) {
            if (strpos($availabilityLower, $pattern) !== false) {
                if (in_array($pattern, ['saturday', 'sunday'])) {
                    $specificDays[] = ucfirst($pattern);
                } else {
                    $isWeekendVolunteer = true;
                }
                break;
            }
        }
        
        // Check for weekday preferences  
        foreach ($weekdayPatterns as $pattern) {
            if (strpos($availabilityLower, $pattern) !== false) {
                if (in_array($pattern, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                    $specificDays[] = ucfirst($pattern);
                } else {
                    $isWeekdayVolunteer = true;
                }
                break;
            }
        }
        
        // Calculate start date based on availability
        if ($isWeekendVolunteer || (!empty($specificDays) && (in_array('Saturday', $specificDays) || in_array('Sunday', $specificDays)))) {
            // Weekend volunteer - start on next Saturday
            $startDate = $today->copy();
            
            // If today is already Saturday or Sunday, start this weekend
            if ($today->isSaturday()) {
                $startDate = $today->copy(); // Start this Saturday
            } elseif ($today->isSunday()) {
                $startDate = $today->copy(); // Start this Sunday
            } else {
                // Start next Saturday
                $startDate = $today->next(\Carbon\Carbon::SATURDAY);
            }
            
            Log::info('Weekend volunteer start date calculated', [
                'today' => $today->format('Y-m-d l'),
                'start_date' => $startDate->format('Y-m-d l'),
                'availability' => $availability
            ]);
            
        } elseif ($isWeekdayVolunteer || (!empty($specificDays) && array_intersect($specificDays, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']))) {
            // Weekday volunteer - start on next Monday
            $startDate = $today->copy();
            
            // If today is a weekday and before 5 PM, they could potentially start today
            // But for safety, start next Monday
            if ($today->isWeekday()) {
                // Start next Monday  
                $startDate = $today->next(\Carbon\Carbon::MONDAY);
            } else {
                // If today is weekend, start next Monday
                $startDate = $today->next(\Carbon\Carbon::MONDAY);
            }
            
            Log::info('Weekday volunteer start date calculated', [
                'today' => $today->format('Y-m-d l'),
                'start_date' => $startDate->format('Y-m-d l'),
                'availability' => $availability
            ]);
            
        } else {
            // Default case - start next Monday
            $startDate = $today->next(\Carbon\Carbon::MONDAY);
            
            Log::info('Default volunteer start date calculated', [
                'today' => $today->format('Y-m-d l'),
                'start_date' => $startDate->format('Y-m-d l'),
                'availability' => $availability
            ]);
        }
        
        return $startDate;
        
    } catch (\Exception $e) {
        Log::error('Error calculating volunteer start date', [
            'availability' => $availability,
            'today' => $today->format('Y-m-d'),
            'error' => $e->getMessage()
        ]);
        
        // Fallback to next Monday
        return $today->next(\Carbon\Carbon::MONDAY);
    }
}
}
