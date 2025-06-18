<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\ActivityAttendance;
use App\Models\Users;
use App\Models\Trainee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities
     */
    public function index()
    {
        $role = session('role');
        $userId = session('id');

        // Role-based activity filtering
        $query = Activity::with(['sessions', 'creator']);

        if ($role === 'teacher') {
            // Teachers see only activities they teach
            $query->whereHas('sessions', function ($q) use ($userId) {
                $q->where('teacher_id', $userId);
            });
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get statistics
        $stats = [
            'total' => Activity::count(),
            'active' => Activity::where('is_active', true)->count(),
            'rehabilitation' => Activity::whereIn('category', ['Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 'Sensory Integration'])->count(),
            'academic' => Activity::whereIn('category', ['Mathematics', 'Literacy', 'Science', 'Computer Skills'])->count()
        ];

        return view('activities.index', compact('activities', 'stats', 'role'));
    }

    /**
     * Show the form for creating a new activity
     */
    public function create()
    {
        $role = session('role');
        
        // Only admin and supervisor can create activities
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create activities.');
        }

        $categories = $this->getActivityCategories();
        
        return view('activities.create', compact('categories'));
    }

    /**
     * Store a newly created activity
     */
    public function store(Request $request)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create activities.');
        }

        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_code' => 'required|string|max:20|unique:activities',
            'description' => 'required|string',
            'category' => 'required|string',
            'objectives' => 'nullable|string',
            'materials_needed' => 'nullable|string',
            'age_group' => 'required|string',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $activity = Activity::create([
                'activity_name' => $validated['activity_name'],
                'activity_code' => strtoupper($validated['activity_code']),
                'description' => $validated['description'],
                'category' => $validated['category'],
                'objectives' => $validated['objectives'],
                'materials_needed' => $validated['materials_needed'],
                'age_group' => $validated['age_group'],
                'difficulty_level' => $validated['difficulty_level'],
                'is_active' => $request->has('is_active'),
                'created_by' => session('id')
            ]);

            DB::commit();

            return redirect()->route('activities.show', $activity->id)
                ->with('success', 'Activity created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating activity: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the activity.');
        }
    }

    /**
     * Display the specified activity
     */
    public function show($id)
    {
        $activity = Activity::with(['sessions.teacher', 'sessions.enrollments.trainee', 'creator'])
            ->findOrFail($id);

        $role = session('role');
        $userId = session('id');

        // Check access for teachers
        if ($role === 'teacher') {
            $hasAccess = $activity->sessions->contains('teacher_id', $userId);
            if (!$hasAccess) {
                return redirect()->route('activities.index')
                    ->with('error', 'You do not have access to this activity.');
            }
        }

        // Get activity statistics
        $stats = [
            'totalSessions' => $activity->sessions->count(),
            'activeSessions' => $activity->sessions->where('status', 'active')->count(),
            'totalEnrollments' => SessionEnrollment::whereIn('session_id', $activity->sessions->pluck('id'))->count(),
            'averageAttendance' => $this->calculateAverageAttendance($activity)
        ];

        return view('activities.show', compact('activity', 'stats', 'role'));
    }

    /**
     * Show the form for editing the activity
     */
    public function edit($id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to edit activities.');
        }

        $activity = Activity::findOrFail($id);
        $categories = $this->getActivityCategories();
        
        return view('activities.edit', compact('activity', 'categories'));
    }

    /**
     * Update the specified activity
     */
    public function update(Request $request, $id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to update activities.');
        }

        $activity = Activity::findOrFail($id);

        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_code' => 'required|string|max:20|unique:activities,activity_code,' . $id,
            'description' => 'required|string',
            'category' => 'required|string',
            'objectives' => 'nullable|string',
            'materials_needed' => 'nullable|string',
            'age_group' => 'required|string',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'is_active' => 'boolean'
        ]);

        try {
            $activity->update([
                'activity_name' => $validated['activity_name'],
                'activity_code' => strtoupper($validated['activity_code']),
                'description' => $validated['description'],
                'category' => $validated['category'],
                'objectives' => $validated['objectives'],
                'materials_needed' => $validated['materials_needed'],
                'age_group' => $validated['age_group'],
                'difficulty_level' => $validated['difficulty_level'],
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('activities.show', $activity->id)
                ->with('success', 'Activity updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating activity: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the activity.');
        }
    }

    /**
     * Remove the specified activity
     */
    public function destroy($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('activities.index')
                ->with('error', 'Only administrators can delete activities.');
        }

        try {
            $activity = Activity::findOrFail($id);
            
            // Check if there are active sessions
            if ($activity->sessions()->where('status', 'active')->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete activity with active sessions.');
            }

            $activity->delete();

            return redirect()->route('activities.index')
                ->with('success', 'Activity deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting activity: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the activity.');
        }
    }

    /**
     * Show sessions for an activity
     */
    public function sessions($id)
    {
        $activity = Activity::findOrFail($id);
        $role = session('role');
        
        $sessions = ActivitySession::where('activity_id', $id)
            ->with(['teacher', 'enrollments.trainee'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        $teachers = Users::where('role', 'teacher')->where('status', 'active')->get();

        return view('activities.sessions', compact('activity', 'sessions', 'teachers', 'role'));
    }

    /**
     * Create a new session
     */
    public function createSession(Request $request, $id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.sessions', $id)
                ->with('error', 'You do not have permission to create sessions.');
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|in:15,20,30,45',
            'location' => 'required|string|max:255',
            'max_capacity' => 'required|integer|min:1|max:50'
        ]);

        try {
            DB::beginTransaction();

            // Check for teacher conflicts
            $endTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time'])
                ->addMinutes($validated['duration']);

            $conflict = ActivitySession::where('teacher_id', $validated['teacher_id'])
                ->where('date', $validated['date'])
                ->where(function ($query) use ($validated, $endTime) {
                    $query->whereBetween('start_time', [$validated['start_time'], $endTime->format('H:i')])
                        ->orWhere(function ($q) use ($validated, $endTime) {
                            $q->where('start_time', '<=', $validated['start_time'])
                              ->whereRaw("ADDTIME(start_time, SEC_TO_TIME(duration * 60)) > ?", [$validated['start_time']]);
                        });
                })
                ->exists();

            if ($conflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Teacher has a scheduling conflict at this time.');
            }

            $session = ActivitySession::create([
                'activity_id' => $id,
                'teacher_id' => $validated['teacher_id'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'duration' => $validated['duration'],
                'location' => $validated['location'],
                'max_capacity' => $validated['max_capacity'],
                'status' => 'active'
            ]);

            DB::commit();

            return redirect()->route('activities.sessions', $id)
                ->with('success', 'Session scheduled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating session: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while scheduling the session.');
        }
    }

    /**
     * Manage enrollments for a session
     */
    public function manageEnrollments($activityId, $sessionId)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'You do not have permission to manage enrollments.');
        }

        $session = ActivitySession::with(['activity', 'enrollments.trainee'])
            ->findOrFail($sessionId);

        // Check if teacher has access
        if ($role === 'teacher' && $session->teacher_id != session('id')) {
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'You can only manage enrollments for your own sessions.');
        }

        // Get eligible trainees
        $enrolledTraineeIds = $session->enrollments->pluck('trainee_id');
        $eligibleTrainees = Trainee::whereNotIn('id', $enrolledTraineeIds)
            ->where('status', 'active')
            ->get();

        return view('activities.enrollments', compact('session', 'eligibleTrainees'));
    }

    /**
     * Enroll trainees in a session
     */
    public function enrollTrainees(Request $request, $activityId, $sessionId)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'You do not have permission to enroll trainees.');
        }

        $session = ActivitySession::findOrFail($sessionId);

        // Check capacity
        $currentEnrollments = $session->enrollments()->count();
        $availableSpots = $session->max_capacity - $currentEnrollments;

        $validated = $request->validate([
            'trainee_ids' => 'required|array|max:' . $availableSpots,
            'trainee_ids.*' => 'exists:trainees,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['trainee_ids'] as $traineeId) {
                SessionEnrollment::create([
                    'session_id' => $sessionId,
                    'trainee_id' => $traineeId,
                    'enrolled_by' => session('id'),
                    'enrollment_date' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('activities.manageEnrollments', [$activityId, $sessionId])
                ->with('success', count($validated['trainee_ids']) . ' trainee(s) enrolled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error enrolling trainees: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while enrolling trainees.');
        }
    }

    /**
     * Show attendance marking form
     */
    public function markAttendance($activityId, $sessionId)
    {
        $session = ActivitySession::with(['activity', 'enrollments.trainee'])
            ->findOrFail($sessionId);

        $role = session('role');
        $userId = session('id');

        // Only teacher of the session or admin/supervisor can mark attendance
        if ($role === 'teacher' && $session->teacher_id != $userId) {
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'You can only mark attendance for your own sessions.');
        }

        // Check if attendance already marked for today
        $attendanceExists = ActivityAttendance::where('session_id', $sessionId)
            ->whereDate('attendance_date', Carbon::today())
            ->exists();

        return view('activities.attendance', compact('session', 'attendanceExists'));
    }

    /**
     * Store attendance records
     */
    public function storeAttendance(Request $request, $activityId, $sessionId)
    {
        $session = ActivitySession::findOrFail($sessionId);
        
        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
            'notes' => 'array',
            'notes.*' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['attendance'] as $traineeId => $status) {
                ActivityAttendance::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'trainee_id' => $traineeId,
                        'attendance_date' => $validated['attendance_date']
                    ],
                    [
                        'status' => $status,
                        'notes' => $validated['notes'][$traineeId] ?? null,
                        'marked_by' => session('id')
                    ]
                );
            }

            DB::commit();

            return redirect()->route('activities.sessions', $activityId)
                ->with('success', 'Attendance marked successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking attendance: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while marking attendance.');
        }
    }

    /**
     * Get activity categories
     */
    private function getActivityCategories()
    {
        return [
            'Rehabilitation' => [
                'Physical Therapy',
                'Occupational Therapy',
                'Speech & Language Therapy',
                'Sensory Integration',
                'Social Skills Training',
                'Daily Living Skills'
            ],
            'Academic' => [
                'Basic Mathematics',
                'Language & Literacy',
                'Science Exploration',
                'Art & Creativity',
                'Music Therapy',
                'Computer Skills'
            ]
        ];
    }

    /**
     * Calculate average attendance for an activity
     */
    private function calculateAverageAttendance($activity)
    {
        $sessionIds = $activity->sessions->pluck('id');
        
        $total = ActivityAttendance::whereIn('session_id', $sessionIds)->count();
        $present = ActivityAttendance::whereIn('session_id', $sessionIds)
            ->where('status', 'present')
            ->count();

        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }
}