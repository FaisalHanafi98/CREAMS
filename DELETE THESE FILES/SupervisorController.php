<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Teachers;
use App\Models\Trainees;
use App\Models\Activities;
use App\Models\Centres;
use App\Models\Assets;

class SupervisorController extends Controller
{
    /**
     * Display the supervisor dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('Supervisor.dashboard');
    }
    
    /**
     * Display a listing of users (primarily teachers under this supervisor).
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor accessed users list', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get teachers that belong to the same centre as the supervisor
        $teachers = Teachers::where('centre_id', $centreId)->get();
        
        return view('supervisor.users', [
            'teachers' => $teachers
        ]);
    }
    
    /**
     * Display a listing of trainees.
     *
     * @return \Illuminate\View\View
     */
    public function trainees()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor accessed trainees list', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get trainees for this centre
        $trainees = Trainees::where('centre_id', $centreId)->get();
        
        return view('supervisor.trainees', [
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Display a listing of centers.
     *
     * @return \Illuminate\View\View
     */
    public function centres()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor accessed centres list', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get centres managed by this supervisor
        // In most cases, this will just be their assigned centre
        $centres = Centres::where('centre_id', $centreId)->get();
        
        return view('supervisor.centres', [
            'centres' => $centres
        ]);
    }
    
    /**
     * Display a listing of assets.
     *
     * @return \Illuminate\View\View
     */
    public function assets()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor accessed assets list', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get assets for the supervisor's centre
        $assets = Assets::where('centre_id', $centreId)->get();
        
        return view('supervisor.assets', [
            'assets' => $assets
        ]);
    }
    
    /**
     * Display a listing of reports.
     *
     * @return \Illuminate\View\View
     */
    public function reports()
    {
        $supervisorId = session('id');
        Log::info('Supervisor accessed reports', ['supervisor_id' => $supervisorId]);
        
        // For now, just return a simple view
        return view('supervisor.reports');
    }
    
    /**
     * Display settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $supervisorId = session('id');
        Log::info('Supervisor accessed settings', ['supervisor_id' => $supervisorId]);
        
        // For now, just return a simple view
        return view('supervisor.settings');
    }
    
    /**
     * Display a listing of activities.
     *
     * @return \Illuminate\View\View
     */
    public function activities()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor accessed activities list', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get activities for this centre
        $activities = Activities::where('centre_id', $centreId)->get();
        
        return view('supervisor.activities', [
            'activities' => $activities
        ]);
    }
    
    /**
     * Display a list of teachers.
     *
     * @return \Illuminate\View\View
     */
    public function manageTeachers()
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor managing teachers', ['supervisor_id' => $supervisorId, 'centre_id' => $centreId]);
        
        // Get teachers for this centre
        $teachers = Teachers::where('centre_id', $centreId)->get();
        
        return view('supervisor.teachers', [
            'teachers' => $teachers
        ]);
    }
    
    /**
     * View specific teacher details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewTeacher($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor viewing teacher', ['supervisor_id' => $supervisorId, 'teacher_id' => $id]);
        
        // Get teacher
        $teacher = Teachers::findOrFail($id);
        
        // Check if this supervisor has access to this teacher
        if ($teacher->centre_id != $centreId) {
            Log::warning('Supervisor attempted to view teacher from different centre', [
                'supervisor_id' => $supervisorId,
                'supervisor_centre_id' => $centreId,
                'teacher_id' => $id,
                'teacher_centre_id' => $teacher->centre_id
            ]);
            
            return redirect()->route('supervisor.teachers')
                ->with('error', 'You do not have permission to view this teacher');
        }
        
        return view('supervisor.teacher.view', [
            'teacher' => $teacher
        ]);
    }
    
    /**
     * Show the form for editing the specified teacher.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editTeacher($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor editing teacher', ['supervisor_id' => $supervisorId, 'teacher_id' => $id]);
        
        // Get teacher
        $teacher = Teachers::findOrFail($id);
        
        // Check if this supervisor has access to this teacher
        if ($teacher->centre_id != $centreId) {
            return redirect()->route('supervisor.teachers')
                ->with('error', 'You do not have permission to edit this teacher');
        }
        
        return view('supervisor.teacher.edit', [
            'teacher' => $teacher
        ]);
    }
    
    /**
     * Update the specified teacher.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTeacher(Request $request, $id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor updating teacher', ['supervisor_id' => $supervisorId, 'teacher_id' => $id]);
        
        // Get teacher
        $teacher = Teachers::findOrFail($id);
        
        // Check if this supervisor has access to this teacher
        if ($teacher->centre_id != $centreId) {
            return redirect()->route('supervisor.teachers')
                ->with('error', 'You do not have permission to update this teacher');
        }
        
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'position' => 'sometimes|nullable|string|max:100',
            // Add more validation rules as needed
        ]);
        
        // Update teacher
        $teacher->update($validated);
        
        return redirect()->route('supervisor.teachers')
            ->with('success', 'Teacher updated successfully');
    }
    
    /**
     * Change the status of a teacher.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeTeacherStatus(Request $request, $id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor changing teacher status', ['supervisor_id' => $supervisorId, 'teacher_id' => $id]);
        
        // Get teacher
        $teacher = Teachers::findOrFail($id);
        
        // Check if this supervisor has access to this teacher
        if ($teacher->centre_id != $centreId) {
            return redirect()->route('supervisor.teachers')
                ->with('error', 'You do not have permission to change this teacher\'s status');
        }
        
        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        
        // Update teacher status
        $teacher->status = $validated['status'];
        $teacher->save();
        
        return redirect()->route('supervisor.teachers')
            ->with('success', 'Teacher status updated successfully');
    }
    
    /**
     * View specific trainee details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewTrainee($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor viewing trainee', ['supervisor_id' => $supervisorId, 'trainee_id' => $id]);
        
        // Get trainee
        $trainee = Trainees::findOrFail($id);
        
        // Check if this supervisor has access to this trainee
        if ($trainee->centre_id != $centreId) {
            return redirect()->route('supervisor.trainees')
                ->with('error', 'You do not have permission to view this trainee');
        }
        
        return view('supervisor.trainee.view', [
            'trainee' => $trainee
        ]);
    }
    
    /**
     * Show the form for editing the specified trainee.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editTrainee($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor editing trainee', ['supervisor_id' => $supervisorId, 'trainee_id' => $id]);
        
        // Get trainee
        $trainee = Trainees::findOrFail($id);
        
        // Check if this supervisor has access to this trainee
        if ($trainee->centre_id != $centreId) {
            return redirect()->route('supervisor.trainees')
                ->with('error', 'You do not have permission to edit this trainee');
        }
        
        return view('supervisor.trainee.edit', [
            'trainee' => $trainee
        ]);
    }
    
    /**
     * Update the specified trainee.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTrainee(Request $request, $id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor updating trainee', ['supervisor_id' => $supervisorId, 'trainee_id' => $id]);
        
        // Get trainee
        $trainee = Trainees::findOrFail($id);
        
        // Check if this supervisor has access to this trainee
        if ($trainee->centre_id != $centreId) {
            return redirect()->route('supervisor.trainees')
                ->with('error', 'You do not have permission to update this trainee');
        }
        
        // Validate request
        $validated = $request->validate([
            'trainee_first_name' => 'sometimes|required|string|max:255',
            'trainee_last_name' => 'sometimes|required|string|max:255',
            'trainee_email' => 'sometimes|required|email|max:255',
            'trainee_phone_number' => 'sometimes|nullable|string|max:20',
            'trainee_condition' => 'sometimes|nullable|string|max:255',
            // Add more validation rules as needed
        ]);
        
        // Update trainee
        $trainee->update($validated);
        
        return redirect()->route('supervisor.trainees')
            ->with('success', 'Trainee updated successfully');
    }
    
    /**
     * View specific activity details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewActivity($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor viewing activity', ['supervisor_id' => $supervisorId, 'activity_id' => $id]);
        
        // Get activity
        $activity = Activities::findOrFail($id);
        
        // Check if this supervisor has access to this activity
        if ($activity->centre_id != $centreId) {
            return redirect()->route('supervisor.activities')
                ->with('error', 'You do not have permission to view this activity');
        }
        
        return view('supervisor.activity.view', [
            'activity' => $activity
        ]);
    }
    
    /**
     * Show the form for editing the specified activity.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editActivity($id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor editing activity', ['supervisor_id' => $supervisorId, 'activity_id' => $id]);
        
        // Get activity
        $activity = Activities::findOrFail($id);
        
        // Check if this supervisor has access to this activity
        if ($activity->centre_id != $centreId) {
            return redirect()->route('supervisor.activities')
                ->with('error', 'You do not have permission to edit this activity');
        }
        
        return view('supervisor.activity.edit', [
            'activity' => $activity
        ]);
    }
    
    /**
     * Update the specified activity.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateActivity(Request $request, $id)
    {
        $supervisorId = session('id');
        $centreId = session('centre_id');
        Log::info('Supervisor updating activity', ['supervisor_id' => $supervisorId, 'activity_id' => $id]);
        
        // Get activity
        $activity = Activities::findOrFail($id);
        
        // Check if this supervisor has access to this activity
        if ($activity->centre_id != $centreId) {
            return redirect()->route('supervisor.activities')
                ->with('error', 'You do not have permission to update this activity');
        }
        
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'date' => 'sometimes|nullable|date',
            'teacher_id' => 'sometimes|nullable|exists:teachers,id',
            // Add more validation rules as needed
        ]);
        
        // Update activity
        $activity->update($validated);
        
        return redirect()->route('supervisor.activities')
            ->with('success', 'Activity updated successfully');
    }
    
    /**
     * Display notifications for the supervisor.
     *
     * @return \Illuminate\View\View
     */
    public function notifications()
    {
        $supervisorId = session('id');
        Log::info('Supervisor accessed notifications', ['supervisor_id' => $supervisorId]);
        
        // Get notifications for this supervisor
        // This would depend on your notification model
        $notifications = []; // Replace with actual notification retrieval
        
        return view('supervisor.notifications', [
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Mark notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markNotificationsRead(Request $request)
    {
        $supervisorId = session('id');
        Log::info('Supervisor marking notifications as read', ['supervisor_id' => $supervisorId]);
        
        // Mark notifications as read logic would go here
        // This would depend on your notification model
        
        return redirect()->back()->with('success', 'Notifications marked as read');
    }
}