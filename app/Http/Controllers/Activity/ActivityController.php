<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Activities;
use App\Models\ActivitySessions;
use App\Models\ActivityAttendances;
use App\Models\Users;
use Illuminate\Http\Request;
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
        $activities = Activities::with(['creator', 'activeSessions'])
            ->withCount('activeSessions')
            ->latest()
            ->get();

        $categories = $this->getCategories();
        $stats = $this->getActivityStats();

        return view('activities.index', compact('activities', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new activity
     */
    public function create()
    {
        $this->authorizeActivity('create', Activities::class);

        $categories = $this->getCategories();
        return view('activities.create', compact('categories'));
    }

    /**
     * Store a newly created activity
     */
    public function store(Request $request)
    {
        $this->authorizeActivity('create', Activities::class);

        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_code' => 'required|string|max:50|unique:activities',
            'category' => 'required|string',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'materials_needed' => 'nullable|string',
            'age_group' => 'required|in:3-6,7-12,13-18,All Ages',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced'
        ]);

        try {
            DB::beginTransaction();

            $activity = Activities::create([
                ...$validated,
                'created_by' => session('id')
            ]);

            DB::commit();

            Log::info('Activity created', [
                'activity_id' => $activity->id,
                'created_by' => session('name')
            ]);

            return redirect()->route('activities.index')
                ->with('success', 'Activity created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create activity', [
                'error' => $e->getMessage(),
                'user' => session('name')
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create activity. Please try again.');
        }
    }

    /**
     * Display the specified activity
     */
    public function show($id)
    {
        $activity = Activities::with(['sessions.teacher', 'sessions.attendance'])
            ->findOrFail($id);

        $stats = $this->getActivityDetailStats($activity);

        return view('activities.show', compact('activity', 'stats'));
    }

    /**
     * Show the form for editing the specified activity
     */
    public function edit($id)
    {
        $activity = Activities::findOrFail($id);
        $this->authorizeActivity('update', $activity);

        $categories = $this->getCategories();
        return view('activities.edit', compact('activity', 'categories'));
    }

    /**
     * Update the specified activity
     */
    public function update(Request $request, $id)
    {
        $activity = Activities::findOrFail($id);
        $this->authorizeActivity('update', $activity);

        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_code' => 'required|string|max:50|unique:activities,activity_code,' . $id,
            'category' => 'required|string',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'materials_needed' => 'nullable|string',
            'age_group' => 'required|in:3-6,7-12,13-18,All Ages',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced'
        ]);

        try {
            $activity->update($validated);

            Log::info('Activity updated', [
                'activity_id' => $activity->id,
                'updated_by' => session('name')
            ]);

            return redirect()->route('activities.show', $id)
                ->with('success', 'Activity updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update activity', [
                'error' => $e->getMessage(),
                'activity_id' => $id
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update activity. Please try again.');
        }
    }

    /**
     * Remove the specified activity (soft delete)
     */
    public function destroy($id)
    {
        $activity = Activities::findOrFail($id);
        $this->authorizeActivity('delete', $activity);

        try {
            $activity->update(['is_active' => false]);
            $activity->sessions()->update(['is_active' => false]);

            Log::info('Activity deactivated', [
                'activity_id' => $id,
                'deactivated_by' => session('name')
            ]);

            return redirect()->route('activities.index')
                ->with('success', 'Activity deactivated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to deactivate activity', [
                'error' => $e->getMessage(),
                'activity_id' => $id
            ]);

            return back()->with('error', 'Failed to deactivate activity.');
        }
    }

    /**
     * Manage activity sessions
     */
    public function sessions($id)
    {
        $activity = Activities::findOrFail($id);
        $sessions = $activity->sessions()->with('teacher')->get();

        return view('activities.sessions', compact('activity', 'sessions'));
    }

    /**
     * Mark attendance for a session
     */
    public function markAttendance($sessionId)
    {
        $session = ActivitySessions::with(['activity', 'teacher'])
            ->findOrFail($sessionId);

        if (!$this->canMarkAttendance($session)) {
            return redirect()->route('activities.index')
                ->with('error', 'You are not authorized to mark attendance for this session.');
        }

        $date = request('date', Carbon::now()->format('Y-m-d'));
        $attendanceRecords = ActivityAttendances::where('session_id', $sessionId)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('trainee_id');

        return view('activities.attendance', compact('session', 'date', 'attendanceRecords'));
    }

    /**
     * Store attendance records
     */
    public function storeAttendance(Request $request, $sessionId)
    {
        $session = ActivitySessions::findOrFail($sessionId);

        if (!$this->canMarkAttendance($session)) {
            return redirect()->route('activities.index')
                ->with('error', 'You are not authorized to mark attendance for this session.');
        }

        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.trainee_id' => 'required|exists:trainees,id',
            'attendance.*.status' => 'required|in:Present,Absent,Excused,Late',
            'attendance.*.notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['attendance'] as $record) {
                ActivityAttendances::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'trainee_id' => $record['trainee_id'],
                        'attendance_date' => $validated['attendance_date']
                    ],
                    [
                        'status' => $record['status'],
                        'notes' => $record['notes'] ?? null,
                        'marked_by' => session('id')
                    ]
                );
            }

            DB::commit();

            return redirect()->route('activities.sessions', $session->activity_id)
                ->with('success', 'Attendance marked successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark attendance', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);

            return back()->with('error', 'Failed to mark attendance. Please try again.');
        }
    }

    /**
     * Get activity categories based on staff specializations
     */
    private function getCategories()
    {
        return Users::distinct()
            ->whereNotNull('user_activity_1')
            ->pluck('user_activity_1')
            ->sort()
            ->values();
    }

    /**
     * Get activity statistics
     */
    private function getActivityStats()
    {
        return [
            'total_activities' => Activities::count(),
            'active_activities' => Activities::where('is_active', true)->count(),
            'total_sessions' => ActivitySessions::where('is_active', true)->count(),
            'todays_sessions' => ActivitySessions::where('day_of_week', Carbon::now()->format('l'))
                ->where('is_active', true)
                ->count()
        ];
    }

    /**
     * Get detailed statistics for a specific activity
     */
    private function getActivityDetailStats($activity)
    {
        $sessions = $activity->sessions;

        return [
            'total_sessions' => $sessions->count(),
            'active_sessions' => $sessions->where('is_active', true)->count(),
            'total_enrollment' => $sessions->sum('current_enrollment'),
            'total_capacity' => $sessions->sum('max_capacity'),
            'average_attendance' => $this->calculateAverageAttendance($sessions)
        ];
    }

    /**
     * Calculate average attendance for sessions
     */
    private function calculateAverageAttendance($sessions)
    {
        $totalAttendance = 0;
        $totalRecords = 0;

        foreach ($sessions as $session) {
            $attendance = $session->attendance;
            if ($attendance->count() > 0) {
                $totalAttendance += $attendance->where('status', 'Present')->count();
                $totalRecords += $attendance->count();
            }
        }

        return $totalRecords > 0 ? round(($totalAttendance / $totalRecords) * 100, 2) : 0;
    }

    /**
     * Check if user can mark attendance
     */
    private function canMarkAttendance($session)
    {
        $role = session('role');
        $userId = session('id');

        return in_array($role, ['admin', 'supervisor']) ||
               ($role === 'teacher' && $session->teacher_id == $userId);
    }

    /**
     * Custom authorization helper to avoid conflict with Laravel's built-in authorize()
     */
    private function authorizeActivity($action, $model)
    {
        $role = session('role');

        if (in_array($action, ['create', 'update', 'delete']) && !in_array($role, ['admin', 'supervisor'])) {
            abort(403, 'Unauthorized action.');
        }
    }
}
