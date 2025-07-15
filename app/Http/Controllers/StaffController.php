<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Users;
use App\Models\Centres;
use App\Models\Activity;
use App\Models\Trainee;
use App\Traits\HandlesErrors;

class StaffController extends Controller
{
    use HandlesErrors;
    /**
     * Display staff profile in view-only mode
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewProfile($id)
    {
        try {
            // Debug: Log what ID we're trying to fetch
            Log::info('StaffController@viewProfile called', [
                'requested_id' => $id,
                'session_id' => session('id'),
                'current_user' => session('name')
            ]);

            // Get staff member with ID
            $user = Users::findOrFail($id);
            
            // Debug: Log what user we found
            Log::info('Staff found for profile viewing', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role
            ]);

            // Get centre information
            $centre = null;
            if ($user->centre_id) {
                $centre = Centres::where('centre_id', $user->centre_id)->first();
            }

            // Check if current user has permission to view this profile
            $canView = $this->checkViewPermission($user);
            
            if (!$canView) {
                Log::warning('Unauthorized profile view attempt', [
                    'viewer_id' => session('id'),
                    'target_id' => $id
                ]);
                
                return redirect()->route('teachershome')
                    ->with('error', 'You do not have permission to view this profile.');
            }


            // Get real-time statistics for this staff member
            $stats = $this->getStaffStatistics($user);

            return view('staff.view', [
                'staffMember' => $user,
                'centre' => $centre,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in StaffController@viewProfile: ' . $e->getMessage());
            
            return redirect()->route('teachershome')
                ->with('error', 'Unable to find staff member with ID: ' . $id);
        }
    }

    /**
     * Display staff profile edit form
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editProfile($id)
    {
        try {
            // Debug: Log what ID we're trying to edit
            Log::info('StaffController@editProfile called', [
                'requested_id' => $id,
                'session_id' => session('id'),
                'current_user' => session('name')
            ]);

            // Get staff member with ID
            $user = Users::findOrFail($id);
            
            // Debug: Log what user we found
            Log::info('Staff found for profile editing', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);

            // Get centres for dropdown
            try {
                $centres = Centres::where('status', 'active')
                    ->get(['centre_id', 'centre_name']);
            } catch (\Exception $e) {
                // If status column doesn't exist, get all centres
                $centres = Centres::all(['centre_id', 'centre_name']);
            }

            // Get centre information for current assignment
            $centre = null;
            if ($user->centre_id) {
                $centre = Centres::where('centre_id', $user->centre_id)->first();
            }

            // Check if current user has permission to edit this profile
            $canEdit = $this->checkEditPermission($user);
            
            if (!$canEdit) {
                Log::warning('Unauthorized profile edit attempt', [
                    'editor_id' => session('id'),
                    'target_id' => $id
                ]);
                
                return redirect()->route('staff.view', $id)
                    ->with('error', 'You do not have permission to edit this profile.');
            }

            return view('staff.edit', [
                'staffMember' => $user,
                'centres' => $centres,
                'centre' => $centre
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in StaffController@editProfile: ' . $e->getMessage());
            
            return redirect()->route('teachershome')
                ->with('error', 'Unable to find staff member with ID: ' . $id);
        }
    }

    /**
     * Update staff profile information
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request, $id)
    {
        try {
            // Get staff member with ID
            $user = Users::findOrFail($id);
            
            // Check if current user has permission to edit this profile
            if (!$this->checkEditPermission($user)) {
                return redirect()->route('staff.view', $id)
                    ->with('error', 'You do not have permission to edit this profile.');
            }

            // Validate request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($id)
                ],
                'iium_id' => [
                    'required',
                    'string',
                    'max:8',
                    Rule::unique('users', 'iium_id')->ignore($id)
                ],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:1000',
                'date_of_birth' => 'nullable|date|before:today',
                'centre_id' => 'required|string|exists:centres,centre_id',
                'role' => 'required|in:admin,supervisor,teacher,ajk',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'name.required' => 'Full name is required.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already registered.',
                'iium_id.required' => 'IIUM ID is required.',
                'iium_id.unique' => 'This IIUM ID is already registered.',
                'centre_id.required' => 'Centre assignment is required.',
                'centre_id.exists' => 'The selected centre is invalid.',
                'role.required' => 'Role selection is required.',
                'role.in' => 'The selected role is invalid.',
                'date_of_birth.before' => 'Date of birth must be in the past.',
                'avatar.image' => 'Avatar must be an image file.',
                'avatar.mimes' => 'Avatar must be a JPEG, PNG, JPG, or GIF file.',
                'avatar.max' => 'Avatar file size must not exceed 2MB.'
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }

                // Store new avatar
                $avatarName = 'staff_' . $id . '_' . uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
                $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
                $validatedData['avatar'] = $avatarName;
            }

            // Update user data
            $user->update($validatedData);

            Log::info('Staff profile updated successfully', [
                'updated_by' => session('id'),
                'updated_user' => $id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return redirect()->route('staff.view', $id)
                ->with('success', 'Profile updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error updating staff profile: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'An error occurred while updating the profile. Please try again.')
                ->withInput();
        }
    }

    /**
     * Check if current user can view a profile
     *
     * @param Users $targetUser
     * @return bool
     */
    private function checkViewPermission($targetUser)
    {
        $currentUserRole = session('role');
        $currentUserId = session('id');
        
        // Users can always view their own profile
        if ($currentUserId == $targetUser->id) {
            return true;
        }
        
        // Define role hierarchy (higher number = more permissions)
        $roleHierarchy = [
            'teacher' => 1,
            'ajk' => 2,
            'supervisor' => 3,
            'admin' => 4
        ];
        
        $currentUserLevel = $roleHierarchy[$currentUserRole] ?? 0;
        $targetUserLevel = $roleHierarchy[$targetUser->role] ?? 0;
        
        // Admins can view all profiles
        if ($currentUserRole === 'admin') {
            return true;
        }
        
        // Supervisors can view teachers and ajk
        if ($currentUserRole === 'supervisor' && in_array($targetUser->role, ['teacher', 'ajk'])) {
            return true;
        }
        
        // Same role users can view each other (for collaboration)
        if ($currentUserRole === $targetUser->role) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if current user can edit a profile
     *
     * @param Users $targetUser
     * @return bool
     */
    private function checkEditPermission($targetUser)
    {
        $currentUserRole = session('role');
        $currentUserId = session('id');
        
        // Users can always edit their own profile
        if ($currentUserId == $targetUser->id) {
            return true;
        }
        
        // Define role hierarchy (higher number = more permissions)
        $roleHierarchy = [
            'teacher' => 1,
            'ajk' => 2,
            'supervisor' => 3,
            'admin' => 4
        ];
        
        $currentUserLevel = $roleHierarchy[$currentUserRole] ?? 0;
        $targetUserLevel = $roleHierarchy[$targetUser->role] ?? 0;
        
        // Can only edit users with lower hierarchy level
        return $currentUserLevel > $targetUserLevel;
    }

    /**
     * Get real-time statistics for a staff member using existing tables
     *
     * @param Users $staffMember
     * @return array
     */
    private function getStaffStatistics($staffMember)
    {
        try {
            // Fix N+1 queries: Use Eloquent with relationships instead of raw DB queries
            $activitiesCreated = 0;
            $totalTrainees = 0;
            $avgAttendance = 0;
            
            // Get activities created by this staff member with relationships
            $activities = Activity::with(['sessions', 'enrollments.trainee'])
                ->where('created_by', $staffMember->id)
                ->where('is_active', true)
                ->get();
                
            $activitiesCreated = $activities->count();

            // Count trainees in same centre using Eloquent
            $totalTrainees = Trainee::where('centre_id', $staffMember->centre_id)->count();

            // Calculate enrollments and attendance using loaded relationships
            if ($activities->isNotEmpty()) {
                $totalEnrollments = $activities->sum(function($activity) {
                    return $activity->enrollments->whereIn('status', ['enrolled', 'active'])->count();
                });
                
                if ($totalEnrollments > 0) {
                    $totalTrainees = $totalEnrollments;
                }

                // Calculate average attendance using relationships
                $attendanceRates = $activities->flatMap(function($activity) {
                    return $activity->enrollments->where('status', 'active')
                        ->pluck('attendance_rate')
                        ->filter(function($rate) { return !is_null($rate); });
                });
                
                $avgAttendance = $attendanceRates->avg() ?: 0;
            }

            // Calculate years of service
            $yearsService = \Carbon\Carbon::parse($staffMember->created_at)->diffInYears(\Carbon\Carbon::now());
            if ($yearsService == 0) {
                $monthsService = \Carbon\Carbon::parse($staffMember->created_at)->diffInMonths(\Carbon\Carbon::now());
                $yearsServiceDisplay = $monthsService . ' month' . ($monthsService != 1 ? 's' : '');
            } else {
                $yearsServiceDisplay = $yearsService . ' year' . ($yearsService != 1 ? 's' : '');
            }

            return [
                'active_sessions' => $activitiesCreated,
                'total_trainees' => $totalTrainees,
                'attendance_rate' => round($avgAttendance, 1),
                'years_service' => $yearsServiceDisplay
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating staff statistics: ' . $e->getMessage());
            
            // Return safe default values if calculation fails
            return [
                'active_sessions' => 0,
                'total_trainees' => 0,
                'attendance_rate' => 0,
                'years_service' => 'N/A'
            ];
        }
    }

    /**
     * Show teacher's schedule
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showSchedule($id)
    {
        try {
            $staffMember = Users::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('teachershome')
                    ->with('error', 'You do not have permission to view this schedule.');
            }

            // Get activities created by this staff member
            $activities = [];
            $schedules = [];
            $sessions = [];
            
            if (\Schema::hasTable('activities')) {
                $activities = \DB::table('activities')
                    ->where('created_by', $staffMember->id)
                    ->where('is_active', true)
                    ->get();
            }

            // Get scheduled sessions where this staff member is the teacher
            if (\Schema::hasTable('activity_sessions')) {
                $sessions = \DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activity_sessions.teacher_id', $staffMember->id)
                    ->where('activity_sessions.status', 'scheduled')
                    ->where('activity_sessions.scheduled_date', '>=', now())
                    ->select(
                        'activity_sessions.*',
                        'activities.activity_name',
                        'activities.category'
                    )
                    ->orderBy('activity_sessions.scheduled_date')
                    ->orderBy('activity_sessions.start_time')
                    ->limit(20) // Limit to next 20 sessions
                    ->get();
            }

            // Check for recurring schedule table
            if (\Schema::hasTable('activity_schedules')) {
                $schedules = \DB::table('activity_schedules')
                    ->join('activities', 'activity_schedules.activity_id', '=', 'activities.id')
                    ->join('activity_sessions', 'activity_schedules.activity_id', '=', 'activity_sessions.activity_id')
                    ->where('activity_sessions.teacher_id', $staffMember->id)
                    ->where('activity_schedules.status', 'active')
                    ->select('activity_schedules.*', 'activities.activity_name')
                    ->distinct()
                    ->orderBy('activity_schedules.day_of_week')
                    ->orderBy('activity_schedules.start_time')
                    ->get();
            }

            return view('staff.schedule', [
                'staffMember' => $staffMember,
                'activities' => $activities,
                'schedules' => $schedules,
                'sessions' => $sessions
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff schedule: ' . $e->getMessage());
            return redirect()->route('staff.view', $id)
                ->with('error', 'Unable to load schedule.');
        }
    }

    /**
     * Show teacher's activities
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showActivities($id)
    {
        try {
            $staffMember = Users::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('teachershome')
                    ->with('error', 'You do not have permission to view these activities.');
            }

            // Get activities created by this staff member with enrollment counts
            $activities = [];
            
            if (\Schema::hasTable('activities')) {
                $activitiesQuery = \DB::table('activities')
                    ->where('created_by', $staffMember->id)
                    ->where('is_active', true);
                
                // Add enrollment counts if table exists
                if (\Schema::hasTable('activity_enrollments')) {
                    $activities = $activitiesQuery
                        ->leftJoin('activity_enrollments', function($join) {
                            $join->on('activities.id', '=', 'activity_enrollments.activity_id')
                                 ->whereIn('activity_enrollments.status', ['enrolled', 'active']);
                        })
                        ->withCount(['enrollments as enrollment_count' => function($query) {
                            $query->whereIn('status', ['enrolled', 'active']);
                        }])
                        ->select('activities.*')
                        ->groupBy('activities.id')
                        ->get();
                } else {
                    $activities = $activitiesQuery->get();
                }
            }

            return view('staff.activities', [
                'staffMember' => $staffMember,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff activities: ' . $e->getMessage());
            return redirect()->route('staff.view', $id)
                ->with('error', 'Unable to load activities.');
        }
    }

    /**
     * Show assigned trainees for teacher
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showTrainees($id)
    {
        try {
            $staffMember = Users::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('teachershome')
                    ->with('error', 'You do not have permission to view these trainees.');
            }

            // Get trainees enrolled in this staff member's activities
            $trainees = [];
            
            if (\Schema::hasTable('trainees') && \Schema::hasTable('activities')) {
                if (\Schema::hasTable('activity_enrollments')) {
                    // Get trainees through enrollment table
                    $trainees = \DB::table('trainees')
                        ->join('activity_enrollments', 'trainees.id', '=', 'activity_enrollments.trainee_id')
                        ->join('activities', 'activity_enrollments.activity_id', '=', 'activities.id')
                        ->where('activities.created_by', $staffMember->id)
                        ->whereIn('activity_enrollments.status', ['enrolled', 'active'])
                        ->select('trainees.*', 'activities.activity_name', 'activity_enrollments.enrollment_date', 'activity_enrollments.status as enrollment_status')
                        ->distinct()
                        ->get();
                } else {
                    // Fallback: get trainees from same centre
                    $trainees = \DB::table('trainees')
                        ->where('centre_id', $staffMember->centre_id)
                        ->get();
                }
            }

            return view('staff.trainees', [
                'staffMember' => $staffMember,
                'trainees' => $trainees
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff trainees: ' . $e->getMessage());
            return redirect()->route('staff.view', $id)
                ->with('error', 'Unable to load assigned trainees.');
        }
    }
}