<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnhancedAttendanceController extends Controller
{
    /**
     * Display enhanced attendance management page with proper permissions
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'name' => session('name'),
                'centre_id' => session('centre_id')
            ];

            // Date filtering
            $selectedDate = $request->input('date', now()->toDateString());
            
            // Centre-based filtering with proper authorization
            $selectedCentreId = null;
            if (in_array($user->role, ['admin', 'supervisor'])) {
                $selectedCentreId = $request->input('centre_id');
            } else {
                $selectedCentreId = $user->centre_id;
            }
            
            // Activity filtering
            $selectedActivityId = $request->input('activity_id');
            
            // Get sessions for the selected date with optimized eager loading
            $sessionsQuery = ActivitySession::with([
                'activity' => function($q) {
                    $q->select('id', 'activity_name', 'centre_id', 'activity_type');
                },
                'activity.centre:id,centre_name',
                'teacher:id,name',
                'sessionEnrollments' => function($q) {
                    $q->with('trainee:id,trainee_first_name,trainee_last_name,unique_identifier');
                }
            ])
            ->where('session_date', $selectedDate);
            
            // Apply centre filtering
            if ($selectedCentreId) {
                $sessionsQuery->whereHas('activity', function ($q) use ($selectedCentreId) {
                    $q->where('centre_id', $selectedCentreId);
                });
            }
            
            // Apply activity filtering
            if ($selectedActivityId) {
                $sessionsQuery->where('activity_id', $selectedActivityId);
            }
            
            // Teacher role filtering - only see own sessions
            if ($user->role === 'teacher') {
                $sessionsQuery->where('teacher_id', $user->id);
            }
            
            $sessions = $sessionsQuery->get();
            
            // Calculate statistics efficiently using single query
            $stats = $this->calculateAttendanceStats($sessions);
            
            // Get centres for filter dropdown (with proper authorization)
            $centres = collect();
            if ($user->role === 'admin') {
                $centres = \App\Models\Centres::select('id', 'centre_name')->get();
            } elseif ($user->role === 'supervisor') {
                $centres = \App\Models\Centres::where('id', $user->centre_id)
                    ->select('id', 'centre_name')->get();
            }
            
            // Get activities for filter (with centre restrictions)
            $activitiesQuery = Activity::select('id', 'activity_name', 'centre_id')
                ->where('is_active', true);
                
            if ($selectedCentreId) {
                $activitiesQuery->where('centre_id', $selectedCentreId);
            } elseif ($user->role === 'teacher') {
                $activitiesQuery->where('created_by', $user->id);
            } elseif (!in_array($user->role, ['admin'])) {
                $activitiesQuery->where('centre_id', $user->centre_id);
            }
            
            $activities = $activitiesQuery->get();
            
            // Get upcoming birthdays (cached)
            $upcomingBirthdays = $this->getUpcomingBirthdays($selectedCentreId);
            
            return view('attendance.enhanced-index', compact(
                'sessions',
                'stats',
                'centres',
                'activities',
                'selectedDate',
                'selectedCentreId',
                'selectedActivityId',
                'upcomingBirthdays',
                'user'
            ));
            
        } catch (\Exception $e) {
            Log::error('Enhanced attendance index failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to load attendance data: ' . $e->getMessage());
        }
    }
    
    /**
     * Store attendance with enhanced validation and security
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:activity_sessions,id',
            'attendance' => 'required|array|min:1',
            'attendance.*.trainee_id' => 'required|exists:trainees,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.participation_score' => 'nullable|integer|min:0|max:10',
            'attendance.*.progress_notes' => 'nullable|string|max:500'
        ]);
        
        $user = (object) [
            'id' => session('id'),
            'role' => session('role'),
            'centre_id' => session('centre_id')
        ];
        
        $session = ActivitySession::with('activity')->findOrFail($validated['session_id']);
        
        // Enhanced permission verification
        if ($user->role === 'teacher' && $session->teacher_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only mark attendance for your own sessions'
            ], 403);
        }
        
        // Centre isolation check
        if (!in_array($user->role, ['admin']) && $session->activity->centre_id !== $user->centre_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Access denied to this centre\'s sessions'
            ], 403);
        }
        
        // Validate trainees belong to the correct centre
        $traineeIds = collect($validated['attendance'])->pluck('trainee_id');
        $invalidTrainees = Trainee::whereIn('id', $traineeIds)
            ->where('centre_id', '!=', $session->activity->centre_id)
            ->count();
            
        if ($invalidTrainees > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some trainees do not belong to this session\'s centre'
            ], 422);
        }
        
        DB::beginTransaction();
        try {
            $attendanceCount = 0;
            
            foreach ($validated['attendance'] as $record) {
                // Update session enrollment
                $enrollment = SessionEnrollment::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'trainee_id' => $record['trainee_id']
                    ],
                    [
                        'attendance_status' => $record['status'],
                        'participation_score' => $record['participation_score'] ?? null,
                        'progress_notes' => $record['progress_notes'] ?? null,
                        'checked_in_at' => $record['status'] === 'present' ? now() : null
                    ]
                );
                
                // Also update main attendance table for backward compatibility
                Attendance::updateOrCreate(
                    [
                        'trainee_id' => $record['trainee_id'],
                        'activity_id' => $session->activity_id,
                        'session_id' => $session->id,
                        'attendance_date' => $session->session_date
                    ],
                    [
                        'attendance_status' => $record['status'],
                        'recorded_by' => $user->id,
                        'attendance_notes' => $record['progress_notes'] ?? null,
                        'participation_score' => $record['participation_score'] ?? null
                    ]
                );
                
                $attendanceCount++;
            }
            
            // Update session status
            $session->update([
                'attendance_marked' => true,
                'status' => 'completed'
            ]);
            
            // Clear related caches
            $this->clearAttendanceCache($session->session_date, $session->activity->centre_id);
            
            DB::commit();
            
            Log::info('Attendance marked successfully', [
                'session_id' => $session->id,
                'marked_by' => $user->id,
                'attendance_count' => $attendanceCount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Attendance marked successfully for {$attendanceCount} trainees"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Attendance marking failed', [
                'session_id' => $validated['session_id'],
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get attendance statistics for today with caching
     */
    public function getTodayStats()
    {
        $user = (object) [
            'id' => session('id'),
            'role' => session('role'),
            'centre_id' => session('centre_id')
        ];
        
        $cacheKey = "attendance_stats_today_{$user->centre_id}_{$user->role}_{$user->id}";
        
        $stats = Cache::remember($cacheKey, 300, function () use ($user) {
            $today = now()->toDateString();
            
            $sessionsQuery = ActivitySession::with('sessionEnrollments')
                ->where('session_date', $today);
                
            // Apply role-based filtering
            if ($user->role === 'teacher') {
                $sessionsQuery->where('teacher_id', $user->id);
            } elseif (!in_array($user->role, ['admin'])) {
                $sessionsQuery->whereHas('activity', function ($q) use ($user) {
                    $q->where('centre_id', $user->centre_id);
                });
            }
            
            $sessions = $sessionsQuery->get();
            
            return $this->calculateAttendanceStats($sessions);
        });
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Calculate attendance statistics efficiently
     */
    private function calculateAttendanceStats($sessions)
    {
        $stats = [
            'total_sessions' => $sessions->count(),
            'completed_sessions' => 0,
            'total_enrollments' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'unmarked' => 0,
            'attendance_rate' => 0
        ];
        
        foreach ($sessions as $session) {
            if ($session->attendance_marked) {
                $stats['completed_sessions']++;
            }
            
            foreach ($session->sessionEnrollments as $enrollment) {
                $stats['total_enrollments']++;
                
                if ($enrollment->attendance_status) {
                    $stats[$enrollment->attendance_status]++;
                } else {
                    $stats['unmarked']++;
                }
            }
        }
        
        // Calculate attendance rate
        if ($stats['total_enrollments'] > 0) {
            $attended = $stats['present'] + $stats['late'];
            $stats['attendance_rate'] = round(($attended / $stats['total_enrollments']) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Get upcoming birthdays with caching
     */
    private function getUpcomingBirthdays($centreId = null)
    {
        $cacheKey = "upcoming_birthdays_" . ($centreId ?? 'all');
        
        return Cache::remember($cacheKey, 1440, function () use ($centreId) { // 24 hours cache
            $query = Trainee::select('id', 'trainee_first_name', 'trainee_last_name', 'trainee_date_of_birth')
                ->whereNotNull('trainee_date_of_birth')
                ->upcomingBirthdays(7); // Next 7 days
                
            if ($centreId) {
                $query->where('centre_id', $centreId);
            }
            
            return $query->limit(5)->get();
        });
    }
    
    /**
     * Clear attendance-related caches
     */
    private function clearAttendanceCache($date, $centreId)
    {
        $patterns = [
            "attendance_stats_{$date}_{$centreId}",
            "attendance_stats_today_{$centreId}*",
            "session_stats_{$date}*"
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
    
    /**
     * Export attendance data with proper permissions
     */
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:csv,excel,pdf',
            'centre_id' => 'nullable|exists:centres,id'
        ]);
        
        $user = (object) [
            'id' => session('id'),
            'role' => session('role'),
            'centre_id' => session('centre_id')
        ];
        
        // Permission check for data access
        $centreId = $request->centre_id;
        if ($user->role === 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Teachers cannot export attendance data'
            ], 403);
        } elseif (!in_array($user->role, ['admin']) && $centreId !== $user->centre_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Cannot export data from other centres'
            ], 403);
        }
        
        try {
            // Get attendance data with optimized queries
            $attendanceData = $this->getAttendanceForExport(
                $request->date_from,
                $request->date_to,
                $centreId
            );
            
            Log::info('Attendance export requested', [
                'user_id' => $user->id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'format' => $request->format,
                'records_count' => $attendanceData->count()
            ]);
            
            // Generate export based on format
            switch ($request->format) {
                case 'csv':
                    return $this->exportToCsv($attendanceData);
                case 'excel':
                    return $this->exportToExcel($attendanceData);
                case 'pdf':
                    return $this->exportToPdf($attendanceData);
            }
            
        } catch (\Exception $e) {
            Log::error('Attendance export failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get attendance data for export with optimized queries
     */
    private function getAttendanceForExport($dateFrom, $dateTo, $centreId = null)
    {
        $query = SessionEnrollment::with([
            'trainee:id,unique_identifier,trainee_first_name,trainee_last_name,centre_id',
            'session.activity:id,activity_name,centre_id',
            'session.teacher:id,name'
        ])
        ->whereHas('session', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('session_date', [$dateFrom, $dateTo]);
        });
        
        if ($centreId) {
            $query->whereHas('session.activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        return $query->get();
    }
    
    /**
     * Export to CSV format
     */
    private function exportToCsv($data)
    {
        $filename = 'attendance_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date', 'Trainee ID', 'Trainee Name', 'Activity', 'Teacher',
                'Status', 'Participation Score', 'Notes', 'Check-in Time'
            ]);
            
            // CSV data
            foreach ($data as $record) {
                fputcsv($file, [
                    $record->session->session_date,
                    $record->trainee->unique_identifier,
                    $record->trainee->trainee_first_name . ' ' . $record->trainee->trainee_last_name,
                    $record->session->activity->activity_name,
                    $record->session->teacher->name ?? 'N/A',
                    ucfirst($record->attendance_status ?? 'Not Marked'),
                    $record->participation_score ?? 'N/A',
                    $record->progress_notes ?? '',
                    $record->checked_in_at ? $record->checked_in_at->format('H:i:s') : 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export to Excel format (placeholder for now)
     */
    private function exportToExcel($data)
    {
        // For now, return CSV. In production, use PhpSpreadsheet or Laravel Excel
        return $this->exportToCsv($data);
    }
    
    /**
     * Export to PDF format (placeholder for now)
     */
    private function exportToPdf($data)
    {
        return response()->json([
            'success' => false,
            'message' => 'PDF export functionality will be implemented in the next update'
        ]);
    }
}