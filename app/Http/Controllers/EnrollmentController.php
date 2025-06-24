<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Trainee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function showAvailableActivities()
    {
        $role = session('role');
        
        // For trainees showing their available activities
        if ($role === 'trainee') {
            $trainee = Trainee::where('user_id', session('id'))->first();
            if (!$trainee) {
                return redirect()->route('dashboard')
                    ->with('error', 'Trainee profile not found');
            }
            
            // Get activities suitable for trainee's age
            $age = \Carbon\Carbon::parse($trainee->trainee_date_of_birth)->age;
            $ageGroup = $this->determineAgeGroup($age);
            
            $activities = Activity::with(['sessions' => function($query) {
                $query->where('is_active', true)
                      ->where('current_enrollment', '<', DB::raw('max_capacity'));
            }])
            ->where('is_active', true)
            ->where(function($query) use ($ageGroup) {
                $query->where('age_group', $ageGroup)
                      ->orWhere('age_group', 'All Ages');
            })
            ->get();
            
            // Filter out already enrolled activities
            $enrolledSessionIds = SessionEnrollment::where('trainee_id', $trainee->id)
                ->where('status', 'Active')
                ->pluck('session_id');
                
            $activities = $activities->filter(function($activity) use ($enrolledSessionIds) {
                return !$activity->sessions->pluck('id')->intersect($enrolledSessionIds)->count();
            });
            
            return view('enrollment.available-activities', compact('activities', 'trainee'));
        }
        
        // For staff enrolling trainees
        return view('enrollment.search-trainee');
    }
    
    public function enrollTrainee(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'trainee_id' => 'required|exists:trainees,id',
            'parent_consent' => 'boolean',
            'special_requirements' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            $session = ActivitySession::findOrFail($sessionId);
            
            // Check if session has capacity
            if ($session->current_enrollment >= $session->max_capacity) {
                return back()->with('error', 'Session is full');
            }
            
            // Check if trainee already enrolled
            $existing = SessionEnrollment::where('session_id', $sessionId)
                ->where('trainee_id', $validated['trainee_id'])
                ->first();
                
            if ($existing) {
                return back()->with('error', 'Trainee already enrolled in this session');
            }
            
            // Create enrollment
            $enrollment = SessionEnrollment::create([
                'session_id' => $sessionId,
                'trainee_id' => $validated['trainee_id'],
                'enrollment_date' => now(),
                'status' => 'Active',
                'parent_consent' => $validated['parent_consent'] ?? false,
                'special_requirements' => $validated['special_requirements']
            ]);
            
            // Update session enrollment count
            $session->increment('current_enrollment');
            
            DB::commit();
            
            Log::info('Trainee enrolled successfully', [
                'enrollment_id' => $enrollment->id,
                'trainee_id' => $validated['trainee_id'],
                'session_id' => $sessionId
            ]);
            
            // Send notification to parent
            $this->notifyParent($enrollment);
            
            return redirect()->back()
                ->with('success', 'Trainee enrolled successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment failed', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to enroll trainee');
        }
    }
    
    public function dropEnrollment(Request $request, $enrollmentId)
    {
        $enrollment = SessionEnrollment::findOrFail($enrollmentId);
        
        // Check permission
        $role = session('role');
        if ($role === 'trainee') {
            $trainee = Trainee::where('user_id', session('id'))->first();
            if (!$trainee || $enrollment->trainee_id !== $trainee->id) {
                return redirect()->back()
                    ->with('error', 'Unauthorized');
            }
        } elseif (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->back()
                ->with('error', 'Unauthorized');
        }
        
        try {
            DB::beginTransaction();
            
            // Update enrollment status
            $enrollment->update([
                'status' => 'Dropped',
                'dropped_at' => now(),
                'dropped_reason' => $request->reason
            ]);
            
            // Update session enrollment count
            $enrollment->session->decrement('current_enrollment');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Successfully dropped from activity');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to drop enrollment');
        }
    }
    
    private function determineAgeGroup($age)
    {
        if ($age >= 3 && $age <= 6) return '3-6';
        if ($age >= 7 && $age <= 12) return '7-12';
        if ($age >= 13 && $age <= 18) return '13-18';
        return 'All Ages';
    }
    
    private function notifyParent($enrollment)
    {
        // Send email/SMS notification to parent
        // Implementation depends on notification system
    }
}