<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Volunteers;
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
return view('volunteer');
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
        $application = Volunteers::create([
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => strtolower(trim($validatedData['email'])),
            'phone' => $validatedData['phone'],
            'address' => $request->address,
            'city' => $request->city,
            'postcode' => $request->postcode,
            'interest' => $validatedData['interest'],
            'other_interest' => $request->other_interest,
            'skills' => $request->skills,
            'availability' => $validatedData['availability'],
            'commitment' => $validatedData['commitment'],
            'motivation' => $validatedData['motivation'],
            'experience' => $request->experience,
            'referral' => $request->referral,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now()
        ]);

        Log::info('Volunteer application saved successfully', [
            'id' => $application->id,
            'email' => $application->email
        ]);

        // Send confirmation email to volunteer (will be logged with log driver)
        try {
            Mail::raw(
                "Dear {$application->first_name},\n\n" .
                "Thank you for your interest in volunteering with IIUM PD-CARE!\n\n" .
                "We have successfully received your volunteer application and are excited about your willingness to support children with special needs in our community.\n\n" .
                "Application Summary:\n" .
                "- Name: {$application->name}\n" .
                "- Email: {$application->email}\n" .
                "- Phone: {$application->phone}\n" .
                "- Area of Interest: " . ucwords(str_replace('-', ' ', $application->interest)) . "\n" .
                "- Availability: " . implode(', ', array_map(function($item) {
                    return ucfirst($item);
                }, $application->availability)) . "\n" .
                "- Time Commitment: {$application->commitment} hours per week\n" .
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
                "City: " . ($application->city ?: 'Not provided') . "\n" .
                "Postcode: " . ($application->postcode ?: 'Not provided') . "\n\n" .
                "VOLUNTEER PREFERENCES:\n" .
                "=====================\n" .
                "Area of Interest: " . ucwords(str_replace('-', ' ', $application->interest)) . "\n" .
                ($application->other_interest ? "Other Interest: {$application->other_interest}\n" : "") .
                "Availability: " . implode(', ', array_map(function($item) {
                    return ucfirst($item);
                }, $application->availability)) . "\n" .
                "Time Commitment: {$application->commitment} hours per week\n" .
                "Skills: " . ($application->skills ?: 'Not specified') . "\n\n" .
                "MOTIVATION:\n" .
                "===========\n" .
                "{$application->motivation}\n\n" .
                "EXPERIENCE:\n" .
                "===========\n" . 
                ($application->experience ?: 'No previous experience specified') . "\n\n" .
                "ADDITIONAL INFO:\n" .
                "===============\n" .
                "How they heard about us: " . ($application->referral ?: 'Not specified') . "\n" .
                "Application ID: #VA" . str_pad($application->id, 6, '0', STR_PAD_LEFT) . "\n" .
                "Submitted: " . $application->created_at->format('F j, Y \a\t g:i A') . "\n" .
                "IP Address: {$application->ip_address}\n\n" .
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
public function getApplications()
{
    try {
        $applications = Volunteers::with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
 * @return \Illuminate\View\View
 */
public function show($id)
{
    try {
        $application = Volunteers::findOrFail($id);
        return view('admin.volunteers.show', compact('application'));
    } catch (\Exception $e) {
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
        $application = Volunteers::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected,contacted',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application->status = $request->status;
        if ($request->notes) {
            $application->admin_notes = $request->notes;
        }
        $application->reviewed_by = session('id');
        $application->reviewed_at = now();
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
}