<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Users;
use App\Models\Trainee;
use App\Models\Centres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivitySessionController extends Controller
{
    /**
     * Display sessions for an activity
     */
    public function index($activityId)
    {
        try {
            $activity = Activity::findOrFail($activityId);
            
            $sessions = ActivitySession::where('activity_id', $activityId)
                ->with(['teacher', 'activeEnrollments.trainee'])
                ->orderBy('semester', 'desc')
                ->orderBy('class_name')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
                
            $sessionsByClass = $sessions->groupBy('class_name');
            
            return view('activities.sessions.index', compact('activity', 'sessions', 'sessionsByClass'));
            
        } catch (\Exception $e) {
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found.');
        }
    }
    
    /**
     * Show form to create a new session
     */
    public function create($activityId)
    {
        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create sessions.');
        }
        
        try {
            $activity = Activity::findOrFail($activityId);
            
            // Get qualified teachers
            $teachers = Users::where('role', 'teacher')
                ->where(function ($query) use ($activity) {
                    for ($i = 1; $i <= 5; $i++) {
                        $query->orWhere("user_activity_{$i}", $activity->category);
                    }
                })
                ->get();
                
            // Get classes/centres
            $centres = Centres::all();
            
            $currentSemester = date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
            
            return view('activities.sessions.create', 
                compact('activity', 'teachers', 'centres', 'currentSemester'));
                
        } catch (\Exception $e) {
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found.');
        }
    }
    
    /**
     * Store a new session
     */
    public function store(Request $request, $activityId)
    {
        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create sessions.');
        }
        
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_name' => 'required|string|max:50',
            'semester' => 'required|string|max:10',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:100',
            'max_trainees' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string'
        ]);
        
        try {
            // Check for conflicts
            $conflict = $this->checkScheduleConflict(
                $validated['teacher_id'],
                $validated['day_of_week'],
                $validated['start_time'],
                $validated['end_time'],
                $validated['semester']
            );
            
            if ($conflict) {
                return back()->withInput()
                    ->with('error', 'Schedule conflict: Teacher already has a session at this time.');
            }
            
            // Check weekly limit (2 sessions per activity per class)
            $existingCount = ActivitySession::where('activity_id', $activityId)
                ->where('class_name', $validated['class_name'])
                ->where('semester', $validated['semester'])
                ->count();
                
            if ($existingCount >= 2) {
                return back()->withInput()
                    ->with('error', 'Weekly limit reached: Each activity can have maximum 2 sessions per class.');
            }
            
            DB::beginTransaction();
            
            $session = ActivitySession::create([
                'activity_id' => $activityId,
                'teacher_id' => $validated['teacher_id'],
                'class_name' => $validated['class_name'],
                'semester' => $validated['semester'],
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'max_trainees' => $validated['max_trainees'],
                'notes' => $validated['notes'],
                'is_active' => true,
                'created_by' => session('id')
            ]);
            
            DB::commit();
            
            Log::info('Session created', [
                'session_id' => $session->id,
                'activity_id' => $activityId,
                'created_by' => session('name')
            ]);
            
            return redirect()->route('activities.sessions', $activityId)
                ->with('success', 'Session created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create session', [
                'activity_id' => $activityId,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to create session. Please try again.');
        }
    }
    
    /**
     * Show form to edit a session
     */
    public function edit($activityId, $sessionId)
    {
        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to edit sessions.');
        }
        
        try {
            $activity = Activity::findOrFail($activityId);
            $session = ActivitySession::findOrFail($sessionId);
            
            // Verify session belongs to activity
            if ($session->activity_id != $activityId) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'Session not found.');
            }
            
            // Get qualified teachers
            $teachers = Users::where('role', 'teacher')
                ->where(function ($query) use ($activity) {
                    for ($i = 1; $i <= 5; $i++) {
                        $query->orWhere("user_activity_{$i}", $activity->category);
                    }
                })
                ->get();
                
            $centres = Centres::all();
            
            return view('activities.sessions.edit', 
                compact('activity', 'session', 'teachers', 'centres'));
                
        } catch (\Exception $e) {
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'Session not found.');
        }
    }
    
    /**
     * Update a session
     */
    public function update(Request $request, $activityId, $sessionId)
    {
        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to edit sessions.');
        }
        
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_name' => 'required|string|max:50',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:100',
            'max_trainees' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        try {
            $session = ActivitySession::findOrFail($sessionId);
            
            // Check for conflicts (exclude current session)
            $conflict = $this->checkScheduleConflict(
                $validated['teacher_id'],
                $validated['day_of_week'],
                $validated['start_time'],
                $validated['end_time'],
                $session->semester,
                $sessionId
            );
            
            if ($conflict) {
                return back()->withInput()
                    ->with('error', 'Schedule conflict: Teacher already has a session at this time.');
            }
            
            $session->update([
                ...$validated,
                'updated_by' => session('id')
            ]);
            
            Log::info('Session updated', [
                'session_id' => $sessionId,
                'updated_by' => session('name')
            ]);
            
            return redirect()->route('activities.sessions', $activityId)
                ->with('success', 'Session updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to update session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to update session. Please try again.');
        }
    }
    
    /**
     * Delete a session
     */
    public function destroy($activityId, $sessionId)
    {
        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to delete sessions.');
        }
        
        try {
            DB::beginTransaction();
            
            $session = ActivitySession::findOrFail($sessionId);
            
            // Check if session has attendance records
            if ($session->attendance()->exists()) {
                // Soft delete by deactivating
                $session->update([
                    'is_active' => false,
                    'updated_by' => session('id')
                ]);
                
                $message = 'Session deactivated (has attendance records).';
            } else {
                // Hard delete if no attendance
                $session->enrollments()->delete();
                $session->delete();
                
                $message = 'Session deleted successfully!';
            }
            
            DB::commit();
            
            Log::info('Session removed', [
                'session_id' => $sessionId,
                'method' => $session->exists ? 'deactivated' : 'deleted',
                'removed_by' => session('name')
            ]);
            
            return redirect()->route('activities.sessions', $activityId)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to remove session. Please try again.');
        }
    }
    
    /**
     * Enroll trainees in a session
     */
    public function enrollTrainees(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'trainee_ids' => 'required|array',
            'trainee_ids.*' => 'exists:trainees,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $session = ActivitySession::findOrFail($sessionId);
            $enrolled = 0;
            $errors = [];
            
            foreach ($validated['trainee_ids'] as $traineeId) {
                if ($session->canEnroll($traineeId)) {
                    SessionEnrollment::create([
                        'session_id' => $sessionId,
                        'trainee_id' => $traineeId,
                        'enrollment_date' => now(),
                        'status' => 'Active',
                        'enrolled_by' => session('id')
                    ]);
                    $enrolled++;
                } else {
                    $trainee = Trainee::find($traineeId);
                    $errors[] = "{$trainee->name} cannot be enrolled (already enrolled or session full).";
                }
            }
            
            DB::commit();
            
            $message = "{$enrolled} trainees enrolled successfully!";
            if (!empty($errors)) {
                $message .= " Issues: " . implode(' ', $errors);
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to enroll trainees. Please try again.');
        }
    }
    
    /**
     * Check for schedule conflicts
     */
    private function checkScheduleConflict($teacherId, $dayOfWeek, $startTime, $endTime, $semester, $excludeId = null)
    {
        $query = ActivitySession::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('semester', $semester)
            ->where('is_active', true);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->where(function ($subQ) use ($startTime, $endTime) {
                $subQ->where('start_time', '>=', $startTime)
                     ->where('start_time', '<', $endTime);
            })->orWhere(function ($subQ) use ($startTime, $endTime) {
                $subQ->where('end_time', '>', $startTime)
                     ->where('end_time', '<=', $endTime);
            })->orWhere(function ($subQ) use ($startTime, $endTime) {
                $subQ->where('start_time', '<=', $startTime)
                     ->where('end_time', '>=', $endTime);
            });
        })->exists();
    }
}