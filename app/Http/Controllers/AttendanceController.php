<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendances;
use App\Models\Trainees;
use App\Models\Activities;
use App\Models\Centres;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance form for a class/activity.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $centreId = $request->input('centre_id');
        $activityId = $request->input('activity_id');
        
        // Get centres for dropdown
        $centres = Centres::all();
        
        // Get activities for dropdown
        $activities = Activities::all();
        
        // Get trainees based on filters
        $traineesQuery = Trainees::query();
        
        // Apply centre filter if provided
        if ($centreId) {
            $traineesQuery->where('centre_id', $centreId);
        } elseif (Auth::user()->role != 'admin') {
            // For non-admin users, only show trainees from their centre
            $traineesQuery->where('centre_id', Auth::user()->centre_id);
        }
        
        // Get trainees
        $trainees = $traineesQuery->orderBy('trainee_first_name')->get();
        
        // Get existing attendance records for this date
        $attendanceRecords = Attendances::where('date', $date)
            ->when($activityId, function($query) use ($activityId) {
                return $query->where('activity_id', $activityId);
            })
            ->get()
            ->keyBy('trainee_id');
        
        // Calculate attendance stats
        $stats = [
            'present_count' => $attendanceRecords->where('status', 'present')->count(),
            'absent_count' => $attendanceRecords->where('status', 'absent')->count(),
            'late_count' => $attendanceRecords->where('status', 'late')->count(),
            'excused_count' => $attendanceRecords->where('status', 'excused')->count()
        ];
        
        return view('attendance.index', [
            'trainees' => $trainees,
            'attendanceRecords' => $attendanceRecords,
            'date' => $date,
            'centres' => $centres,
            'activities' => $activities,
            'stats' => $stats
        ]);
    }
    
    /**
     * Store attendance records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.trainee_id' => 'required|exists:trainees,id',
            'attendance.*.status' => 'required|in:present,absent,excused,late',
        ]);
        
        try {
            $date = $request->input('date');
            $activityId = $request->input('activity_id');
            $attendanceData = $request->input('attendance');
            
            foreach ($attendanceData as $data) {
                Attendances::updateOrCreate(
                    [
                        'trainee_id' => $data['trainee_id'],
                        'date' => $date,
                        'activity_id' => $activityId
                    ],
                    [
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => Auth::id()
                    ]
                );
            }
            
            Log::info('Attendance recorded successfully', [
                'date' => $date,
                'activity_id' => $activityId,
                'count' => count($attendanceData),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('success', 'Attendance has been recorded successfully.');
        } catch (\Exception $e) {
            Log::error('Error recording attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to record attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate attendance report.
     */
    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $centreName = $request->input('centre_name');
        
        // Get centres for dropdown
        $centres = Centres::all();
        
        // Get trainees based on filters
        $traineesQuery = Trainees::query();
        
        // Apply centre filter if provided
        if ($centreName) {
            $traineesQuery->where('centre_name', $centreName);
        } elseif (Auth::user()->role != 'admin') {
            // For non-admin users, only show trainees from their centre
            $traineesQuery->where('centre_name', Auth::user()->centre_name);
        }
        
        // Get trainees
        $trainees = $traineesQuery->orderBy('trainee_first_name')->get();
        
        // Initialize attendance data array
        $attendanceData = [];
        
        // Get attendance data for each trainee
        foreach ($trainees as $trainee) {
            $attendanceData[$trainee->id] = $this->calculateAttendanceRate($trainee->id, $startDate, $endDate);
        }
        
        // Calculate summary stats
        $summaryStats = [
            'present_count' => array_sum(array_column($attendanceData, 'present')),
            'absent_count' => array_sum(array_column($attendanceData, 'absent')),
            'late_count' => array_sum(array_column($attendanceData, 'late')),
            'excused_count' => array_sum(array_column($attendanceData, 'excused'))
        ];
        
        return view('attendance.report', [
            'trainees' => $trainees,
            'attendanceData' => $attendanceData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'centres' => $centres,
            'summaryStats' => $summaryStats
        ]);
    }
    
    /**
     * Show attendance record for a specific trainee.
     */
    public function showTraineeAttendance($id, Request $request)
    {
        $trainee = Trainees::findOrFail($id);
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $calendarMonth = $request->input('month', date('Y-m'));
        
        // Get attendance records for the date range
        $attendanceRecords = Attendances::with(['activity', 'markedBy'])
            ->where('trainee_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
        
        // Calculate attendance rate for the period
        $attendanceRate = $this->calculateAttendanceRate($id, $startDate, $endDate);
        
        // Generate calendar days for the month view
        $calendarDays = $this->generateCalendarDays($calendarMonth, $id);
        
        return view('attendance.trainee', [
            'trainee' => $trainee,
            'attendanceRecords' => $attendanceRecords,
            'attendanceRate' => $attendanceRate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $calendarDays
        ]);
    }
    
    /**
     * Calculate attendance rate for a trainee within a date range.
     * 
     * @param int $traineeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function calculateAttendanceRate($traineeId, $startDate, $endDate)
    {
        // Get attendance records in the range
        $records = Attendances::where('trainee_id', $traineeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Count by status
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $lateCount = $records->where('status', 'late')->count();
        $excusedCount = $records->where('status', 'excused')->count();
        
        $totalCount = $records->count();
        
        // Calculate percentage (considering late as half present)
        if ($totalCount > 0) {
            $percentage = round((($presentCount + ($lateCount * 0.5)) / $totalCount) * 100, 2);
        } else {
            $percentage = 0;
        }
        
        return [
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'total' => $totalCount,
            'percentage' => $percentage
        ];
    }
    
    /**
     * Generate calendar days for a specific month and trainee.
     * 
     * @param string $yearMonth
     * @param int $traineeId
     * @return array
     */
    private function generateCalendarDays($yearMonth, $traineeId)
    {
        // Parse year and month
        list($year, $month) = explode('-', $yearMonth);
        
        // Get first day of the month
        $firstDay = Carbon::createFromDate($year, $month, 1);
        
        // Get days in month
        $daysInMonth = $firstDay->daysInMonth;
        
        // Get the day of week for the first day (0 = Sunday, 6 = Saturday)
        $firstDayOfWeek = $firstDay->dayOfWeek;
        
        // Get attendance records for this month
        $attendanceRecords = Attendances::where('trainee_id', $traineeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->day;
            });
        
        // Initialize calendar array
        $calendar = [];
        
        // Add empty cells for days before the first day of month
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $prevMonth = Carbon::createFromDate($year, $month, 1)->subDays($firstDayOfWeek - $i);
            $calendar[] = [
                'day' => $prevMonth->day,
                'current_month' => false,
                'is_today' => false
            ];
        }
        
        // Add days of the current month
        $today = Carbon::today();
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $isToday = $date->isSameDay($today);
            
            $calendarDay = [
                'day' => $day,
                'current_month' => true,
                'is_today' => $isToday
            ];
            
            // Add attendance status if available
            if (isset($attendanceRecords[$day])) {
                $record = $attendanceRecords[$day];
                $calendarDay['status'] = $record->status;
                $calendarDay['remarks'] = $record->remarks;
            }
            
            $calendar[] = $calendarDay;
        }
        
        // Fill remaining cells with days from next month
        $remainingCells = 42 - count($calendar); // 6 rows x 7 days = 42 cells
        if ($remainingCells > 7) {
            $remainingCells = $remainingCells - 7; // Keep it to 35 cells (5 rows) if possible
        }
        
        for ($i = 1; $i <= $remainingCells; $i++) {
            $calendar[] = [
                'day' => $i,
                'current_month' => false,
                'is_today' => false
            ];
        }
        
        return $calendar;
    }
}