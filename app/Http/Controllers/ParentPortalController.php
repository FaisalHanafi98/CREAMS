<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Models\Guardian;
use App\Models\SessionEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentPortalController extends Controller
{
    public function login()
    {
        return view('parent.login');
    }
    
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'trainee_id' => 'required'
        ]);
        
        $guardian = Guardian::where('email', $validated['email'])
            ->where('trainee_id', $validated['trainee_id'])
            ->first();
            
        if (!$guardian || !Hash::check($validated['password'], $guardian->password)) {
            return back()->with('error', 'Invalid credentials');
        }
        
        session([
            'guardian_id' => $guardian->id,
            'guardian_name' => $guardian->name,
            'trainee_id' => $guardian->trainee_id
        ]);
        
        return redirect()->route('parent.dashboard');
    }
    
    public function dashboard()
    {
        $traineeId = session('trainee_id');
        $trainee = Trainee::with(['enrollments.session.activity', 'enrollments.attendance'])
            ->findOrFail($traineeId);
            
        $attendanceStats = $this->calculateAttendanceStats($trainee);
        $upcomingActivities = $this->getUpcomingActivities($trainee);
        $progressReports = $this->getProgressReports($trainee);
        
        return view('parent.dashboard', compact('trainee', 'attendanceStats', 'upcomingActivities', 'progressReports'));
    }
    
    public function viewProgress($activityId)
    {
        $traineeId = session('trainee_id');
        
        $enrollment = SessionEnrollment::with(['session.activity', 'attendance'])
            ->where('trainee_id', $traineeId)
            ->whereHas('session', function($q) use ($activityId) {
                $q->where('activity_id', $activityId);
            })
            ->first();
            
        if (!$enrollment) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Activity not found');
        }
        
        return view('parent.progress', compact('enrollment'));
    }
    
    private function calculateAttendanceStats($trainee)
    {
        // Similar to trainee dashboard stats
        return [
            'overall_rate' => 85,
            'this_month' => 90,
            'last_month' => 80
        ];
    }
    
    private function getUpcomingActivities($trainee)
    {
        // Get next week's activities
        return $trainee->enrollments->filter(function($enrollment) {
            return $enrollment->session->is_active;
        });
    }
    
    private function getProgressReports($trainee)
    {
        // Get latest progress notes from teachers
        return collect(); // Placeholder
    }
}