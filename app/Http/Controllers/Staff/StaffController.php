<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Centre;
use App\Models\Activity;
use App\Models\Trainee;
use App\Models\StaffAttendance;
use App\Traits\HandlesErrors;
use App\Traits\HandlesEncryptedIds;

class StaffController extends Controller
{
    use HandlesErrors, HandlesEncryptedIds;
    /**
     * Display staff profile in view-only mode
     *
     * @param string $encrypted_id
     * @return \Illuminate\View\View
     */
    public function viewProfile($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            // Debug: Log what ID we're trying to fetch
            Log::info('StaffController@viewProfile called', [
                'decrypted_id' => $id,
                'session_id' => session('id'),
                'current_user' => session('name')
            ]);

            // Get staff member with ID
            $user = User::findOrFail($id);
            
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
                $centre = Centre::where('centre_id', $user->centre_id)->first();
            }

            // Check if current user has permission to view this profile
            $canView = $this->checkViewPermission($user);
            
            if (!$canView) {
                Log::warning('Unauthorized profile view attempt', [
                    'viewer_id' => session('id'),
                    'target_id' => $id
                ]);
                
                return redirect()->route('staffs.home')
                    ->with('error', 'You do not have permission to view this profile.');
            }


            // Get real-time statistics for this staff member
            $stats = $this->getStaffStatistics($user);

            // Add encrypted ID to staff member object for view links
            $user->encrypted_id = $encrypted_id;

            return view('staff.view', [
                'staffMember' => $user,
                'centre' => $centre,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in StaffController@viewProfile: ' . $e->getMessage());
            
            return redirect()->route('staffs.home')
                ->with('error', 'Unable to find staff member with ID: ' . $id);
        }
    }

    /**
     * Display staff profile edit form
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editProfile($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            // Debug: Log what ID we're trying to edit
            Log::info('StaffController@editProfile called', [
                'decrypted_id' => $id,
                'session_id' => session('id'),
                'current_user' => session('name')
            ]);

            // Get staff member with ID
            $user = User::findOrFail($id);
            
            // Debug: Log what user we found
            Log::info('Staff found for profile editing', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);

            // Get centres for dropdown
            try {
                $centres = Centre::where('status', 'active')
                    ->get(['centre_id', 'centre_name']);
            } catch (\Exception $e) {
                // If status column doesn't exist, get all centres
                $centres = Centre::all(['centre_id', 'centre_name']);
            }

            // Get centre information for current assignment
            $centre = null;
            if ($user->centre_id) {
                $centre = Centre::where('centre_id', $user->centre_id)->first();
            }

            // Check if current user has permission to edit this profile
            $canEdit = $this->checkEditPermission($user);
            
            if (!$canEdit) {
                Log::warning('Unauthorized profile edit attempt', [
                    'editor_id' => session('id'),
                    'target_id' => $id
                ]);
                
                return redirect()->route('staffs.profile', ['encrypted_id' => $this->generateEncryptedId($id)])
                    ->with('error', 'You do not have permission to edit this profile.');
            }

            return view('staff.edit', [
                'staffMember' => $user,
                'centres' => $centres,
                'centre' => $centre,
                'encrypted_id' => $encrypted_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in StaffController@editProfile: ' . $e->getMessage());
            
            return redirect()->route('staffs.home')
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
    public function updateProfile(Request $request, $encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            // Get staff member with ID
            $user = User::findOrFail($id);
            
            // Check if current user has permission to edit this profile
            if (!$this->checkEditPermission($user)) {
                return redirect()->route('staffs.profile', ['encrypted_id' => $this->generateEncryptedId($id)])
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
                'about' => 'nullable|string|max:1000',
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

            return redirect()->route('staffs.profile', ['encrypted_id' => $this->generateEncryptedId($id)])
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
     * @param User $targetUser
     * @return bool
     */
    private function checkViewPermission($targetUser)
    {
        $currentUserRole = session('role');
        $currentUserId = session('id');
        
        // User can always view their own profile
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
        
        // Admin can view all profiles
        if ($currentUserRole === 'admin') {
            return true;
        }
        
        // Supervisor can view teachers and ajk
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
     * @param User $targetUser
     * @return bool
     */
    private function checkEditPermission($targetUser)
    {
        $currentUserRole = session('role');
        $currentUserId = session('id');
        
        // User can always edit their own profile
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
     * @param User $staffMember
     * @return array
     */
    private function getStaffStatistics($staffMember)
    {
        try {
            // Get activities where this staff is assigned as teacher/instructor
            $staffActivities = Activity::with(['enrollments.trainee', 'sessions'])
                ->where(function($query) use ($staffMember) {
                    $query->where('created_by', $staffMember->id)
                          ->orWhere('instructor_id', $staffMember->id)
                          ->orWhereHas('sessions', function($q) use ($staffMember) {
                              $q->where('teacher_id', $staffMember->id);
                          });
                })
                ->whereIn('activity_status', ['scheduled', 'ongoing', 'completed'])
                ->get();
            
            // Count unique trainees enrolled in activities where this staff is instructor
            $traineeIds = collect();
            foreach ($staffActivities as $activity) {
                $activityTraineeIds = $activity->enrollments
                    ->whereIn('enrollment_status', ['enrolled', 'completed'])
                    ->pluck('trainee_id');
                $traineeIds = $traineeIds->merge($activityTraineeIds);
            }
            $totalTrainees = $traineeIds->unique()->count();
            
            // Count active sessions where this staff member is the teacher
            $activeSessions = 0;
            if (\Schema::hasTable('activity_sessions')) {
                $activeSessions = \DB::table('activity_sessions')
                    ->where('teacher_id', $staffMember->id)
                    ->whereIn('session_status', ['scheduled', 'ongoing'])
                    ->where('scheduled_date', '>=', now()->startOfMonth()) // Current month
                    ->count();
            }

            // Calculate average progress percentage from enrollments (as attendance proxy)
            $avgAttendance = 0;
            if ($staffActivities->isNotEmpty()) {
                $progressRates = $staffActivities->flatMap(function($activity) {
                    return $activity->enrollments->whereIn('enrollment_status', ['enrolled', 'completed'])
                        ->pluck('progress_percentage')
                        ->filter(function($rate) { return !is_null($rate) && $rate > 0; });
                });
                
                $avgAttendance = $progressRates->avg() ?: 0;
            }

            // Calculate service period from first day in staff attendance to now
            $serviceStartDate = null;
            if (\Schema::hasTable('staff_attendances')) {
                $firstAttendance = \DB::table('staff_attendances')
                    ->where('user_id', $staffMember->id)
                    ->orderBy('attendance_date', 'asc')
                    ->first();
                
                if ($firstAttendance) {
                    $serviceStartDate = \Carbon\Carbon::parse($firstAttendance->attendance_date);
                }
            }
            
            // Fallback to user creation date if no attendance records
            if (!$serviceStartDate) {
                $serviceStartDate = \Carbon\Carbon::parse($staffMember->created_at);
            }
            
            $servicePeriod = $serviceStartDate->diffInDays(\Carbon\Carbon::now());
            if ($servicePeriod < 30) {
                $yearsServiceDisplay = $servicePeriod . ' day' . ($servicePeriod != 1 ? 's' : '');
            } elseif ($servicePeriod < 365) {
                $months = floor($servicePeriod / 30);
                $yearsServiceDisplay = $months . ' month' . ($months != 1 ? 's' : '');
            } else {
                $years = floor($servicePeriod / 365);
                $remainingMonths = floor(($servicePeriod % 365) / 30);
                $yearsServiceDisplay = $years . ' year' . ($years != 1 ? 's' : '');
                if ($remainingMonths > 0) {
                    $yearsServiceDisplay .= ', ' . $remainingMonths . ' month' . ($remainingMonths != 1 ? 's' : '');
                }
            }

            return [
                'active_sessions' => $staffActivities->count(), // Total activities assigned to this staff
                'total_trainees' => $totalTrainees,
                'attendance_rate' => round($avgAttendance, 1),
                'years_service' => $yearsServiceDisplay,
                'total_activities' => $staffActivities->count(),
                'active_activity_sessions' => $activeSessions // Keep the original sessions count
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
    public function showSchedule($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            // Find staff member by ID
            $staffMember = User::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('staffs.home')
                    ->with('error', 'You do not have permission to view this schedule.');
            }

            // Get activities created by this staff member
            $activities = [];
            $schedules = [];
            $sessions = [];
            
            if (\Schema::hasTable('activities')) {
                $activities = \DB::table('activities')
                    ->where(function($query) use ($staffMember) {
                        $query->where('created_by', $staffMember->id)
                              ->orWhere('instructor_id', $staffMember->id);
                    })
                    ->whereIn('activity_status', ['scheduled', 'ongoing'])
                    ->get();
            }

            // Get all sessions where this staff member is the teacher (recent and upcoming)
            if (\Schema::hasTable('activity_sessions')) {
                $sessions = \DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->leftJoin('categories', 'activities.category_id', '=', 'categories.id')
                    ->where('activity_sessions.teacher_id', $staffMember->id)
                    ->whereIn('activity_sessions.session_status', ['scheduled', 'ongoing', 'completed'])
                    ->where('activity_sessions.scheduled_date', '>=', now()->subDays(30)) // Show last 30 days
                    ->select(
                        'activity_sessions.*',
                        'activities.activity_name',
                        'activities.category_id',
                        'categories.category_name as category',
                        'activity_sessions.scheduled_date',
                        'activity_sessions.start_time',
                        'activity_sessions.end_time',
                        'activity_sessions.venue'
                    )
                    ->orderBy('activity_sessions.scheduled_date', 'desc')
                    ->orderBy('activity_sessions.start_time', 'desc')
                    ->limit(20) // Limit to 20 recent sessions
                    ->get();
                    
                // Add computed properties for view compatibility
                $sessions = $sessions->map(function($session) {
                    $session->duration_minutes = 60; // Default duration
                    if ($session->start_time && $session->end_time) {
                        $start = \Carbon\Carbon::parse($session->start_time);
                        $end = \Carbon\Carbon::parse($session->end_time);
                        $session->duration_minutes = $end->diffInMinutes($start);
                    }
                    $session->enrolled_count = 0; // Will be calculated from enrollments if needed
                    return $session;
                });
            }

            // Check for recurring schedule table
            if (\Schema::hasTable('activity_schedules')) {
                $schedules = \DB::table('activity_schedules')
                    ->join('activities', 'activity_schedules.activity_id', '=', 'activities.id')
                    ->join('activity_sessions', 'activity_schedules.activity_id', '=', 'activity_sessions.activity_id')
                    ->where('activity_sessions.teacher_id', $staffMember->id)
                    ->where('activity_schedules.schedule_status', 'active')
                    ->select('activity_schedules.*', 'activities.activity_name')
                    ->distinct()
                    ->orderBy('activity_schedules.start_date')
                    ->orderBy('activity_schedules.start_time')
                    ->get();
            }

            // Calculate real schedule statistics based on actual data
            $scheduleStats = [
                'total_hours' => 0,
                'today_sessions' => 0,
                'week_sessions' => 0,
                'month_hours' => 0
            ];
            
            if (isset($sessions) && count($sessions) > 0) {
                // Calculate total hours from sessions
                $totalMinutes = $sessions->sum('duration_minutes');
                $scheduleStats['total_hours'] = round($totalMinutes / 60, 1);
                
                // Count today's sessions
                $scheduleStats['today_sessions'] = $sessions->filter(function($session) {
                    return \Carbon\Carbon::parse($session->scheduled_date)->isToday();
                })->count();
                
                // Count this week's sessions
                $scheduleStats['week_sessions'] = $sessions->filter(function($session) {
                    return \Carbon\Carbon::parse($session->scheduled_date)->isCurrentWeek();
                })->count();
                
                // Calculate monthly hours (approximate)
                $monthlyMinutes = $sessions->filter(function($session) {
                    return \Carbon\Carbon::parse($session->scheduled_date)->isCurrentMonth();
                })->sum('duration_minutes');
                $scheduleStats['month_hours'] = round($monthlyMinutes / 60, 1);
            }

            // Add encrypted ID to staff member object for view links
            $staffMember->encrypted_id = $encrypted_id;
            
            return view('staff.schedule', [
                'staffMember' => $staffMember,
                'activities' => $activities,
                'schedules' => $schedules,
                'sessions' => $sessions,
                'scheduleStats' => $scheduleStats
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff schedule: ' . $e->getMessage(), [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('staffs.home')
                ->with('error', 'Unable to load schedule.');
        }
    }

    /**
     * Show teacher's activities
     *
     * @param string $encrypted_id
     * @return \Illuminate\View\View
     */
    public function showActivities($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            $staffMember = User::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('staffs.home')
                    ->with('error', 'You do not have permission to view these activities.');
            }

            // Get activities created by this staff member with enrollment counts
            $activities = [];
            
            if (\Schema::hasTable('activities')) {
                $activitiesQuery = \DB::table('activities')
                    ->where(function($query) use ($staffMember) {
                        $query->where('created_by', $staffMember->id)
                              ->orWhere('instructor_id', $staffMember->id);
                    })
                    ->whereIn('activity_status', ['scheduled', 'ongoing']);
                
                // Add enrollment counts if table exists
                if (\Schema::hasTable('activity_enrollments')) {
                    // Use a subquery to get enrollment counts to avoid GROUP BY issues
                    $activities = $activitiesQuery
                        ->select('activities.*')
                        ->get()
                        ->map(function($activity) {
                            $enrollmentCount = \DB::table('activity_enrollments')
                                ->where('activity_id', $activity->id)
                                ->whereIn('enrollment_status', ['enrolled', 'active'])
                                ->count();
                            $activity->enrollment_count = $enrollmentCount;
                            
                            // Add description property for view compatibility
                            $activity->description = $activity->activity_description ?? null;
                            
                            // Add category information
                            if ($activity->category_id) {
                                $category = \DB::table('categories')->where('id', $activity->category_id)->first();
                                $activity->category = $category ? $category->category_name : null;
                            } else {
                                $activity->category = null;
                            }
                            
                            // Add missing properties for view compatibility
                            $requiredResources = $activity->required_resources;
                            if (is_string($requiredResources)) {
                                $requiredResources = json_decode($requiredResources, true);
                            }
                            $activity->requires_equipment = !empty($requiredResources) && is_array($requiredResources) && count($requiredResources) > 0;
                            
                            $activity->objectives = $activity->activity_goals ?? $activity->activity_outcomes ?? null;
                            if (is_string($activity->objectives)) {
                                $objectives = json_decode($activity->objectives, true);
                                if (is_array($objectives)) {
                                    $activity->objectives = implode('; ', $objectives);
                                }
                            }
                            
                            return $activity;
                        });
                } else {
                    $activities = $activitiesQuery->get();
                    // Add enrollment_count property for consistency
                    $activities = $activities->map(function($activity) {
                        $activity->enrollment_count = 0;
                        
                        // Add description property for view compatibility
                        $activity->description = $activity->activity_description ?? null;
                        
                        // Add category information
                        if ($activity->category_id) {
                            $category = \DB::table('categories')->where('id', $activity->category_id)->first();
                            $activity->category = $category ? $category->category_name : null;
                        } else {
                            $activity->category = null;
                        }
                        
                        // Add missing properties for view compatibility
                        $requiredResources = $activity->required_resources;
                        if (is_string($requiredResources)) {
                            $requiredResources = json_decode($requiredResources, true);
                        }
                        $activity->requires_equipment = !empty($requiredResources) && is_array($requiredResources) && count($requiredResources) > 0;
                        
                        $activity->objectives = $activity->activity_goals ?? $activity->activity_outcomes ?? null;
                        if (is_string($activity->objectives)) {
                            $objectives = json_decode($activity->objectives, true);
                            if (is_array($objectives)) {
                                $activity->objectives = implode('; ', $objectives);
                            }
                        }
                        
                        return $activity;
                    });
                }
            }

            // Add encrypted ID to staff member object for view links
            $staffMember->encrypted_id = $encrypted_id;
            
            return view('staff.activities', [
                'staffMember' => $staffMember,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff activities: ' . $e->getMessage());
            return redirect()->route('staffs.profile', ['encrypted_id' => $this->generateEncryptedId($id)])
                ->with('error', 'Unable to load activities.');
        }
    }

    /**
     * Show assigned trainees for teacher
     *
     * @param string $encrypted_id
     * @return \Illuminate\View\View
     */
    public function showTrainees($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            $staffMember = User::findOrFail($id);
            
            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('staffs.home')
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
                        ->whereIn('activity_enrollments.enrollment_status', ['enrolled', 'active'])
                        ->select('trainees.*', 'activities.activity_name', 'activity_enrollments.enrollment_date', 'activity_enrollments.enrollment_status')
                        ->distinct()
                        ->get();
                } else {
                    // Fallback: get trainees from same centre
                    $trainees = \DB::table('trainees')
                        ->where('centre_id', $staffMember->centre_id)
                        ->get();
                }
            }

            // Add encrypted ID to staff member object for view links
            $staffMember->encrypted_id = $encrypted_id;
            
            return view('staff.trainees', [
                'staffMember' => $staffMember,
                'trainees' => $trainees
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff trainees: ' . $e->getMessage());
            return redirect()->route('staffs.profile', ['encrypted_id' => $this->generateEncryptedId($id)])
                ->with('error', 'Unable to load assigned trainees.');
        }
    }

    /**
     * Show staff attendance record using IIUM ID
     *
     * @param string $iium_id
     * @return \Illuminate\View\View
     */
    public function showAttendance($encrypted_id)
    {
        try {
            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('staffs.home')->with('error', 'Invalid or expired link.');
            }
            
            // Find staff member by ID
            $staffMember = User::findOrFail($id);
            
            Log::info('Viewing staff attendance', [
                'encrypted_id' => $encrypted_id,
                'staff_id' => $staffMember->id,
                'viewer_id' => session('id')
            ]);

            // Check permission
            if (!$this->checkViewPermission($staffMember)) {
                return redirect()->route('staffs.home')
                    ->with('error', 'You do not have permission to view this attendance record.');
            }

            // Calculate real attendance statistics from database
            $attendanceStats = $this->calculateAttendanceStats($staffMember->id);
            $monthlyStats = $this->calculateMonthlyStats($staffMember->id);
            $weeklyStats = $this->calculateWeeklyStats($staffMember->id);
            
            // Calculate working days for current month
            $currentMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $workingDays = 0;
            
            // Count weekdays (Monday to Friday) in current month
            for ($date = $currentMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                if ($date->isWeekday()) {
                    $workingDays++;
                }
            }
            
            // Get recent attendance records for detailed view
            $recentAttendances = \DB::table('staff_attendances')
                ->where('user_id', $staffMember->id)
                ->where('attendance_date', '>=', now()->subDays(30))
                ->orderBy('attendance_date', 'desc')
                ->orderBy('attendance_time', 'desc')
                ->limit(50)
                ->get();

            // Add encrypted ID to staff member object for view links
            $staffMember->encrypted_id = $encrypted_id;
            
            return view('staff.attendance', [
                'staffMember' => $staffMember,
                'attendanceStats' => $attendanceStats,
                'monthlyStats' => $monthlyStats,
                'weeklyStats' => $weeklyStats,
                'workingDays' => $workingDays,
                'recentAttendances' => $recentAttendances
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing staff attendance: ' . $e->getMessage(), [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->route('staffs.home')
                ->with('error', 'Unable to load attendance record.');
        }
    }

    /**
     * Calculate attendance statistics for a staff member
     *
     * @param int $staffId
     * @return array
     */
    private function calculateAttendanceStats($staffId)
    {
        try {
            $currentMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            // Get all attendance records for current month
            $monthlyAttendances = \DB::table('staff_attendances')
                ->where('user_id', $staffId)
                ->whereBetween('attendance_date', [$currentMonth->toDateString(), $endOfMonth->toDateString()])
                ->get();
                
            // Count working days this month (weekdays only, up to today)
            $workingDaysThisMonth = 0;
            for ($date = $currentMonth->copy(); $date->lte($endOfMonth) && $date->lte(now()); $date->addDay()) {
                if ($date->isWeekday()) {
                    $workingDaysThisMonth++;
                }
            }
            
            // Count unique attendance dates (present days)
            $presentDays = $monthlyAttendances->pluck('attendance_date')->unique()->count();
            
            // Count records by status (using the actual status field from database)
            $lateArrivals = $monthlyAttendances->where('status', 'late')->count();
            
            // Count sick leaves (this status actually exists in the enum)
            $sickLeaves = $monthlyAttendances->where('status', 'sick_leave')->count();
            
            // Calculate attendance rate based on present days vs working days
            $attendanceRate = $workingDaysThisMonth > 0 ? round(($presentDays / $workingDaysThisMonth) * 100, 1) : 0;
            
            return [
                'present_days' => $presentDays,
                'late_arrivals' => $lateArrivals,
                'sick_leaves' => $sickLeaves,
                'attendance_rate' => $attendanceRate
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating attendance stats: ' . $e->getMessage());
            return [
                'present_days' => 0,
                'late_arrivals' => 0,
                'sick_leaves' => 0,
                'attendance_rate' => 0
            ];
        }
    }

    /**
     * Calculate monthly statistics for a staff member
     *
     * @param int $staffId
     * @return array
     */
    private function calculateMonthlyStats($staffId)
    {
        try {
            $currentMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            // Get attendance records for current month
            $monthlyAttendances = \DB::table('staff_attendances')
                ->where('user_id', $staffId)
                ->whereBetween('attendance_date', [$currentMonth->toDateString(), $endOfMonth->toDateString()])
                ->get();
                
            // Count check-ins and check-outs (actual data from attendance_type column)
            $totalCheckins = $monthlyAttendances->where('attendance_type', 'check_in')->count();
            $totalCheckouts = $monthlyAttendances->where('attendance_type', 'check_out')->count();
            
            // Count different leave types (actual statuses from the enum)
            $authorizedLeaves = $monthlyAttendances->where('status', 'authorized_leave')->count();
            $emergencyLeaves = $monthlyAttendances->where('status', 'emergency_leave')->count();
            
            return [
                'total_checkins' => $totalCheckins,
                'total_checkouts' => $totalCheckouts,
                'authorized_leaves' => $authorizedLeaves,
                'emergency_leaves' => $emergencyLeaves
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating monthly stats: ' . $e->getMessage());
            return [
                'total_checkins' => 0,
                'total_checkouts' => 0,
                'authorized_leaves' => 0,
                'emergency_leaves' => 0
            ];
        }
    }

    /**
     * Calculate weekly statistics for a staff member
     *
     * @param int $staffId
     * @return array
     */
    private function calculateWeeklyStats($staffId)
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            
            // Get this week's attendance records
            $weeklyAttendances = \DB::table('staff_attendances')
                ->where('user_id', $staffId)
                ->whereBetween('attendance_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->orderBy('attendance_date')
                ->orderBy('attendance_time')
                ->get();
                
            $totalMinutes = 0;
            
            // Group by date to calculate daily hours
            $dailyRecords = $weeklyAttendances->groupBy('attendance_date');
            
            foreach ($dailyRecords as $date => $records) {
                $checkIn = $records->where('attendance_type', 'check_in')->first();
                $checkOut = $records->where('attendance_type', 'check_out')->first();
                
                if ($checkIn && $checkOut) {
                    $checkInTime = \Carbon\Carbon::parse($date . ' ' . $checkIn->attendance_time);
                    $checkOutTime = \Carbon\Carbon::parse($date . ' ' . $checkOut->attendance_time);
                    
                    $dailyMinutes = $checkOutTime->diffInMinutes($checkInTime);
                    $totalMinutes += $dailyMinutes;
                }
            }
            
            $weeklyHours = round($totalMinutes / 60, 1);
            
            return [
                'hours' => $weeklyHours
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating weekly stats: ' . $e->getMessage());
            return [
                'hours' => 0
            ];
        }
    }
}