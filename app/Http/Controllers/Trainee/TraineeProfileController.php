<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centre;
use Carbon\Carbon;
use Exception;
use App\Traits\HandlesErrors;
use App\Traits\HandlesEncryptedIds;
use Barryvdh\DomPDF\Facade\Pdf;

class TraineeProfileController extends Controller
{
    use HandlesErrors, HandlesEncryptedIds;
    /**
     * Display the trainee profile dashboard.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function index($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Accessing trainee profile dashboard', [
                'user_id' => session('id'),
                'user_role' => session('role'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id
            ]);
            
            // Find the trainee by ID with comprehensive eager loading to prevent N+1 queries
            $trainee = Trainee::with([
                'activities.sessions', 
                'activities.enrollments',
                'enrollments.activity',
                'sessionEnrollments.session.activity',
                'sessionEnrollments.session.teacher'
            ])->findOrFail($id);
            
            // Calculate some stats for the dashboard
            $age = Carbon::parse($trainee->trainee_date_of_birth)->age;
            $enrollmentDuration = Carbon::parse($trainee->created_at)->diffForHumans();
            $totalActivities = $trainee->activities->count();
            $recentActivities = $trainee->activities->where('activity_date', '>=', now()->subDays(30))->count();
            
            // Mock attendance data (in a real implementation, this would come from an attendance table)
            // We'll create some example attendance data for demonstration
            $currentMonth = date('Y-m');
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            
            // Generate mock attendance data
            $totalDays = 20; // Assuming 20 working days per month
            $attendanceDays = [
                'present' => rand(12, 18),
                'late' => rand(0, 3),
                'absent' => rand(0, 5),
                'excused' => rand(0, 2)
            ];
            
            // Ensure total adds up to totalDays
            $sum = array_sum($attendanceDays);
            if ($sum != $totalDays) {
                $diff = $totalDays - $sum;
                $attendanceDays['present'] += $diff;
            }
            
            // Calculate attendance rate
            $attendanceRate = (($attendanceDays['present'] + ($attendanceDays['late'] * 0.5)) / $totalDays) * 100;
            $attendanceRate = round($attendanceRate);
            
            // Get attendance history (mock data for now)
            $attendanceHistory = [];
            $date = Carbon::parse($startDate);
            $statusOptions = ['present', 'present', 'present', 'present', 'late', 'absent', 'excused']; // Weighted for more present days
            
            for ($i = 0; $i < $totalDays; $i++) {
                // Skip weekends
                if ($date->isWeekend()) {
                    $date->addDay();
                    continue;
                }
                
                $status = $statusOptions[array_rand($statusOptions)];
                $attendanceHistory[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->format('l'),
                    'status' => $status,
                    'remarks' => $status == 'excused' ? 'Medical appointment' : null
                ];
                
                $date->addDay();
            }
            
            // Sort by date descending
            usort($attendanceHistory, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            // Get guardian information from the trainee record
            $guardian = [
                'name' => $trainee->guardian_name ?? 'Not specified',
                'relationship' => $trainee->guardian_relationship ?? 'Not specified',
                'phone' => $trainee->guardian_phone ?? 'Not specified',
                'email' => $trainee->guardian_email ?? 'Not specified',
                'address' => $trainee->guardian_address ?? 'Not specified'
            ];
            
            Log::info('Trainee profile data retrieved successfully', [
                'trainee_id' => $id,
                'activities_count' => $totalActivities
            ]);
            
            // Return the view with all data
            return view('trainees.profile', [
                'trainee' => $trainee,
                'age' => $age,
                'enrollmentDuration' => $enrollmentDuration,
                'totalActivities' => $totalActivities,
                'recentActivities' => $recentActivities,
                'attendanceDays' => $attendanceDays,
                'attendanceRate' => $attendanceRate,
                'attendanceHistory' => $attendanceHistory,
                'guardian' => $guardian
            ]);
        } catch (Exception $e) {
            Log::error('Error accessing trainee profile dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('trainees.home')
                ->with('error', 'Unable to access trainee profile. ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the trainee profile.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = \App\Helpers\EncryptionHelper::decryptId($encrypted_id);
            
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Accessing trainee edit form', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id
            ]);
            
            $trainee = Trainee::findOrFail($id);
            $centres = Centre::where('is_active', 1)->get();
            
            // List of conditions (same as in registration)
            $conditions = [
                'Autism Spectrum Disorder',
                'Cerebral Palsy',
                'Down Syndrome',
                'Hearing Impairment',
                'Visual Impairment',
                'Intellectual Disability',
                'Physical Disability',
                'Speech Impairment',
                'Learning Disability',
                'Multiple Disabilities',
                'Other'
            ];
            
            return view('trainees.edit', [
                'trainee' => $trainee,
                'centres' => $centres,
                'conditions' => $conditions
            ]);
        } catch (Exception $e) {
            Log::error('Error accessing trainee edit form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('trainees.show', ['encrypted_id' => $encrypted_id])
                ->with('error', 'Unable to access edit form. ' . $e->getMessage());
        }
    }

    /**
     * Update the trainee profile.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Updating trainee profile', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'request_data' => $request->except(['trainee_avatar'])
            ]);
            
            // Validate the request data - updated to match form fields
            $validatedData = $request->validate([
                'trainee_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'trainee_first_name' => 'required|string|max:255',
                'trainee_last_name' => 'required|string|max:255',
                'trainee_email' => [
                    'required',
                    'email',
                    Rule::unique('trainees')->ignore($id)
                ],
                'trainee_phone_number' => 'nullable|string|max:20',
                'trainee_date_of_birth' => 'required|date|before_or_equal:today',
                'gender' => 'required|in:Male,Female',
                'trainee_address' => 'nullable|string|max:500',
                'centre_name' => 'required|string|exists:centres,centre_name',
                'trainee_condition' => 'required|string|max:255',
                'guardian_name' => 'required|string|max:255',
                'guardian_relationship' => 'nullable|string|max:255',
                'guardian_phone' => 'required|string|max:20',
                'guardian_email' => 'required|email|max:255',
                'guardian_address' => 'nullable|string|max:500',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                'medical_history' => 'nullable|string|max:1000',
                'additional_notes' => 'nullable|string|max:1000',
            ]);

            // Find the trainee by ID
            $trainee = Trainee::findOrFail($id);

            // Log the original trainee data
            Log::info('Original Trainee Data:', [
                'trainee_id' => $id,
                'first_name' => $trainee->trainee_first_name,
                'last_name' => $trainee->trainee_last_name,
                'email' => $trainee->trainee_email,
                'centre' => $trainee->centre_name,
                'condition' => $trainee->trainee_condition
            ]);

            // Update the trainee model with the validated data
            $trainee->trainee_first_name = $validatedData['trainee_first_name'];
            $trainee->trainee_last_name = $validatedData['trainee_last_name'];
            $trainee->trainee_email = $validatedData['trainee_email'];
            if (isset($validatedData['trainee_phone_number'])) {
                $trainee->trainee_phone_number = $validatedData['trainee_phone_number'];
            }
            $trainee->trainee_date_of_birth = $validatedData['trainee_date_of_birth'];
            $trainee->gender = $validatedData['gender'];
            $trainee->trainee_address = $validatedData['trainee_address'];
            $trainee->centre_name = $validatedData['centre_name'];
            $trainee->trainee_condition = $validatedData['trainee_condition'];
            
            // Update guardian information
            $trainee->guardian_name = $validatedData['guardian_name'];
            $trainee->guardian_relationship = $validatedData['guardian_relationship'];
            $trainee->guardian_phone = $validatedData['guardian_phone'];
            $trainee->guardian_email = $validatedData['guardian_email'];
            $trainee->guardian_address = $validatedData['guardian_address'];
            
            // Update emergency contact information
            $trainee->emergency_contact_name = $validatedData['emergency_contact_name'];
            $trainee->emergency_contact_phone = $validatedData['emergency_contact_phone'];
            $trainee->emergency_contact_relationship = $validatedData['emergency_contact_relationship'];
            
            // Update medical and additional information
            $trainee->medical_history = $validatedData['medical_history'];
            $trainee->additional_notes = $validatedData['additional_notes'];
            
            // Update consent information
            $trainee->photo_consent = $request->has('photo_consent') ? 1 : 0;
            $trainee->services_consent = $request->has('services_consent') ? 1 : 0;
            
            // Update status if provided
            if (isset($validatedData['status'])) {
                $trainee->status = $validatedData['status'];
            }

            // Handle the avatar image upload if provided
            if ($request->hasFile('trainee_avatar')) {
                try {
                    Log::info('Processing trainee avatar update');
                    
                    // Delete old avatar if exists and is not default
                    if ($trainee->avatar && 
                        !str_contains($trainee->avatar, 'default-avatar') && 
                        Storage::disk('public')->exists(str_replace('storage/', '', $trainee->avatar))) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $trainee->avatar));
                    }
                    
                    $avatar = $request->file('trainee_avatar');
                    $avatarName = time() . '_' . $avatar->getClientOriginalName();
                    $avatarPath = $avatar->storeAs('trainee_avatars', $avatarName, 'public');
                    $trainee->avatar = 'storage/' . $avatarPath;
                    
                    Log::info('Trainee avatar updated successfully', [
                        'path' => $avatarPath,
                        'original_name' => $avatar->getClientOriginalName()
                    ]);
                } catch (Exception $e) {
                    Log::error('Error updating trainee avatar', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Don't update avatar if upload fails, but continue with other updates
                }
            }

            // Save the updated trainee model
            $saved = $trainee->save();
            
            // Log the save result
            Log::info('Trainee save result:', [
                'trainee_id' => $id,
                'save_successful' => $saved,
                'dirty_attributes' => $trainee->getDirty(),
                'model_attributes' => $trainee->getAttributes()
            ]);

            // Log the updated trainee data
            Log::info('Updated Trainee Data - ALL FIELDS NOW SAVING:', [
                'trainee_id' => $id,
                'first_name' => $trainee->trainee_first_name,
                'last_name' => $trainee->trainee_last_name,
                'email' => $trainee->trainee_email,
                'centre' => $trainee->centre_name,
                'condition' => $trainee->trainee_condition,
                'guardian_name' => $trainee->guardian_name,
                'guardian_relationship' => $trainee->guardian_relationship,
                'guardian_phone' => $trainee->guardian_phone,
                'guardian_email' => $trainee->guardian_email,
                'guardian_address' => $trainee->guardian_address,
                'emergency_contact_name' => $trainee->emergency_contact_name,
                'emergency_contact_phone' => $trainee->emergency_contact_phone,
                'emergency_contact_relationship' => $trainee->emergency_contact_relationship,
                'medical_history' => $trainee->medical_history ? 'updated' : 'empty',
                'additional_notes' => $trainee->additional_notes ? 'updated' : 'empty',
                'photo_consent' => $trainee->photo_consent,
                'services_consent' => $trainee->services_consent
            ]);
            
            // Remove the old log message that was indicating data wasn't being stored
            // This was caused by guardian info, emergency contact, medical history, and consent fields
            // being in the guarded array and therefore not being saved to the database

            // Redirect back to the profile page with success message using encrypted ID
            return redirect()->route('trainees.show', ['encrypted_id' => $encrypted_id])
                ->with('success', 'Trainee profile updated successfully!');
        } catch (Exception $e) {
            // Log the error
            Log::error('Error updating trainee profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);

            // Redirect with error message using encrypted ID
            return redirect()->route('trainees.show', ['encrypted_id' => $encrypted_id])
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Update trainee progress.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProgress(Request $request, $encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Updating trainee progress', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'request_data' => $request->all()
            ]);
            
            $trainee = Trainee::findOrFail($id);
            
            // Validate the input
            $validatedData = $request->validate([
                'progress_notes' => 'required|string',
                'progress_date' => 'required|date|before_or_equal:today',
                'progress_type' => 'required|string',
                'progress_rating' => 'required|integer|min:1|max:5'
            ]);
            
            // Create a new activity record for this progress update
            $activity = new Activity();
            $activity->trainee_id = $id;
            $activity->activity_name = 'Progress Update: ' . $validatedData['progress_type'];
            $activity->activity_type = 'Progress';
            $activity->activity_date = $validatedData['progress_date'];
            $activity->activity_description = $validatedData['progress_notes'];
            $activity->activity_goals = 'Rating: ' . $validatedData['progress_rating'] . '/5';
            $activity->created_by = session('id');
            
            // Save the activity
            $activity->save();
            
            Log::info('Trainee progress updated successfully', [
                'trainee_id' => $id,
                'activity_id' => $activity->id,
                'type' => $validatedData['progress_type'],
                'rating' => $validatedData['progress_rating']
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('success', 'Progress updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating trainee progress', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('error', 'Failed to update progress: ' . $e->getMessage());
        }
    }
    
    /**
     * Record trainee attendance.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordAttendance(Request $request, $encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Recording trainee attendance', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'request_data' => $request->all()
            ]);
            
            // Validate the input
            $validatedData = $request->validate([
                'attendance_date' => 'required|date|before_or_equal:today',
                'attendance_status' => 'required|in:present,late,absent,excused',
                'attendance_remarks' => 'nullable|string|max:255'
            ]);
            
            // In a real implementation, this would create a record in an attendances table
            // For now, we'll just log it
            Log::info('Trainee attendance recorded', [
                'trainee_id' => $id,
                'date' => $validatedData['attendance_date'],
                'status' => $validatedData['attendance_status'],
                'remarks' => $validatedData['attendance_remarks'] ?? null,
                'recorded_by' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('success', 'Attendance recorded successfully');
        } catch (Exception $e) {
            Log::error('Error recording trainee attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('error', 'Failed to record attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete trainee profile.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Deleting trainee profile', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id
            ]);
            
            $trainee = Trainee::findOrFail($id);
            
            // Delete avatar file if exists and not default
            if ($trainee->avatar && 
                !str_contains($trainee->avatar, 'default-avatar') && 
                Storage::disk('public')->exists(str_replace('storage/', '', $trainee->avatar))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $trainee->avatar));
            }
            
            // Delete related activities
            Activity::where('trainee_id', $id)->delete();
            
            // Delete the trainee
            $trainee->delete();
            
            Log::info('Trainee deleted successfully', [
                'trainee_id' => $id,
                'deleted_by' => session('id')
            ]);
            
            return redirect()->route('trainees.home')
                ->with('success', 'Trainee deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting trainee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('error', 'Failed to delete trainee: ' . $e->getMessage());
        }
    }
    
    /**
     * Add a new activity for the trainee.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addActivity(Request $request, $encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Adding new activity for trainee', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'request_data' => $request->all()
            ]);
            
            // Validate the input
            $validatedData = $request->validate([
                'activity_name' => 'required|string|max:255',
                'activity_type' => 'required|string|max:255',
                'activity_date' => 'required|date',
                'activity_description' => 'required|string',
                'activity_goals' => 'nullable|string',
                'activity_outcomes' => 'nullable|string',
            ]);
            
            // Create a new activity
            $activity = new Activity();
            $activity->trainee_id = $id;
            $activity->activity_name = $validatedData['activity_name'];
            $activity->activity_type = $validatedData['activity_type'];
            $activity->activity_date = $validatedData['activity_date'];
            $activity->activity_description = $validatedData['activity_description'];
            $activity->activity_goals = $validatedData['activity_goals'];
            $activity->activity_outcomes = $validatedData['activity_outcomes'];
            $activity->created_by = session('id');
            
            // Save the activity
            $activity->save();
            
            Log::info('Activity added successfully', [
                'trainee_id' => $id,
                'activity_id' => $activity->id,
                'activity_name' => $activity->activity_name
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('success', 'Activity added successfully');
        } catch (Exception $e) {
            Log::error('Error adding activity for trainee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('error', 'Failed to add activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Download trainee profile as PDF.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadProfile($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }
            
            Log::info('Downloading trainee profile', [
                'user_id' => session('id'),
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id
            ]);
            
            // Find the trainee with related data
            $trainee = Trainee::with([
                'activities',
                'enrollments.activity',
                'sessionEnrollments.session.activity',
                'centre'
            ])->findOrFail($id);
            
            // Calculate age and other stats
            $age = Carbon::parse($trainee->trainee_date_of_birth)->age;
            $totalActivities = $trainee->activities->count();
            
            // Guardian information
            $guardian = [
                'name' => $trainee->guardian_name ?? 'Not specified',
                'relationship' => $trainee->guardian_relationship ?? 'Not specified',
                'phone' => $trainee->guardian_phone ?? 'Not specified',
                'email' => $trainee->guardian_email ?? 'Not specified',
                'address' => $trainee->guardian_address ?? 'Not specified'
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('trainees.pdf-profile', [
                'trainee' => $trainee,
                'age' => $age,
                'totalActivities' => $totalActivities,
                'guardian' => $guardian,
                'downloadDate' => now()->format('d M Y H:i:s')
            ]);
            
            $filename = "trainee-profile-{$trainee->trainee_first_name}-{$trainee->trainee_last_name}-" . date('Y-m-d') . ".pdf";
            
            Log::info('Trainee profile PDF generated successfully', [
                'trainee_id' => $id,
                'filename' => $filename,
                'generated_by' => session('id')
            ]);
            
            return $pdf->download($filename);
            
        } catch (Exception $e) {
            Log::error('Error downloading trainee profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($id)])
                ->with('error', 'Failed to download profile: ' . $e->getMessage());
        }
    }
}