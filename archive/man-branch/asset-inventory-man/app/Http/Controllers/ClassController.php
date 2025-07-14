<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;
use App\Models\Trainees;
use App\Models\Attendances;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index()
    {
        $teacherId = session('id');
        
        $classes = Classes::where('teacher_id', $teacherId)
                 ->orderBy('start_date', 'desc')
                 ->paginate(10);
        
        return view('teacher.classes.index', compact('classes'));
    }
    
    /**
     * Display the class schedule.
     */
    public function schedule()
    {
        $teacherId = session('id');
        
        $classes = Classes::where('teacher_id', $teacherId)
                 ->where('status', 'active')
                 ->get();
        
        // Organize classes by day of week
        $schedule = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => []
        ];
        
        foreach ($classes as $class) {
            $classSchedule = json_decode($class->schedule, true) ?? [];
            
            foreach ($schedule as $day => $classes) {
                if (isset($classSchedule[$day]) && $classSchedule[$day]) {
                    $schedule[$day][] = $class;
                }
            }
        }
        
        return view('teacher.classes.schedule', compact('schedule'));
    }
    
    /**
     * Display the specified class.
     */
    public function show($id)
    {
        $teacherId = session('id');
        
        $class = Classes::where('id', $id)
               ->where('teacher_id', $teacherId)
               ->firstOrFail();
        
        $trainees = $class->trainees;
        
        $recentAttendance = Attendances::where('class_id', $id)
                          ->orderBy('date', 'desc')
                          ->limit(10)
                          ->get()
                          ->groupBy('date');
        
        return view('teacher.classes.show', compact('class', 'trainees', 'recentAttendance'));
    }
    
    /**
     * Update attendance for a class.
     */
    public function updateAttendance(Request $request, $id)
    {
        $teacherId = session('id');
        
        // Verify teacher has access to this class
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Validate request
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,excused,late'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Delete existing attendance records for this date
            Attendances::where('class_id', $id)
                     ->where('date', $validated['date'])
                     ->delete();
            
            // Create new attendance records
            foreach ($validated['attendance'] as $traineeId => $status) {
                $attendance = new Attendances();
                $attendance->trainee_id = $traineeId;
                $attendance->class_id = $id;
                $attendance->date = $validated['date'];
                $attendance->status = $status;
                $attendance->marked_by = $teacherId;
                $attendance->save();
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Attendance updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating attendance', [
                'teacher_id' => $teacherId,
                'class_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                   ->with('error', 'An error occurred while updating attendance')
                   ->withInput();
        }
    }
}