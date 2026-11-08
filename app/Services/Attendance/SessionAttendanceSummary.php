<?php

namespace App\Services\Attendance;

use App\Models\ActivitySession;
use App\Models\Trainee;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SessionAttendanceSummary
{
    public function dashboard(string $centreId, CarbonInterface $selectedDate): array
    {
        $sessions = ActivitySession::query()
            ->whereHas('activity', fn ($query) => $query->where('centre_id', $centreId))
            ->with(['activity', 'teacher', 'enrollments.trainee', 'traineeAttendances.trainee'])
            ->whereBetween('session_date', [$selectedDate->copy()->startOfWeek()->toDateString(), $selectedDate->toDateString()])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get();

        $weekly = ['expected' => 0, 'recorded' => 0, 'attended' => 0];
        $presentToday = collect();
        $todaySessions = collect();
        foreach ($sessions as $session) {
            $roster = $this->roster($session, $centreId);
            $marks = $this->recordedMarks($session, $roster);
            $attended = $marks->whereIn('status', config('attendance.attended_statuses'));
            $session->setRelation('enrollments', $roster);
            $session->setRelation('traineeAttendances', $marks);
            $session->setAttribute('attendance_marked', $roster->isNotEmpty() && $marks->count() === $roster->count());
            $session->setAttribute('can_mark_attendance', $session->session_status !== 'cancelled'
                && (in_array(session('role'), ['admin', 'supervisor'], true)
                    || (session('role') === 'teacher' && (int) $session->instructor_id === (int) session('id'))));

            if ($session->session_date->isSameDay($selectedDate)) {
                $todaySessions->push($session);
                if ($session->session_status !== 'cancelled') {
                    $presentToday = $presentToday->merge($attended->pluck('trainee_id'));
                }
            }

            $end = $session->session_date->copy()->setTimeFromTimeString($session->end_time);
            if ($session->session_status !== 'cancelled' && $end->lte(now())) {
                $weekly['expected'] += $roster->count();
                $weekly['recorded'] += $marks->count();
                $weekly['attended'] += $attended->count();
            }
        }

        return [
            'activitySessions' => $todaySessions,
            'attendanceOverview' => [
                'total_trainees' => Trainee::where('centre_id', $centreId)->count(),
                'present_today' => $presentToday->unique()->count(),
                'total_sessions' => $todaySessions->where('session_status', '!=', 'cancelled')->count(),
                'completed_sessions' => $todaySessions->where('session_status', 'completed')->count(),
            ],
            'trainees' => $todaySessions->flatMap(fn ($session) => $session->enrollments->pluck('trainee'))->unique('id'),
            'analytics' => [
                'weekly_attendance_rate' => $weekly['expected'] > 0 ? round(100.0 * $weekly['attended'] / $weekly['expected'], 2) : null,
                'recording_coverage' => $weekly['expected'] > 0 ? round(100.0 * $weekly['recorded'] / $weekly['expected'], 2) : null,
                'expected' => $weekly['expected'],
                'recorded' => $weekly['recorded'],
                'unmarked' => $weekly['expected'] - $weekly['recorded'],
                'learning_progress_available' => false,
            ],
        ];
    }

    public function recordedMarks(ActivitySession $session, Collection $roster): Collection
    {
        return $session->traineeAttendances
            ->filter(fn ($mark) => $mark->activity_id === $session->activity_id
                && $mark->attendance_date->isSameDay($session->session_date)
                && $roster->contains('trainee_id', $mark->trainee_id))
            ->sortByDesc('id')->unique('trainee_id');
    }

    public function roster(ActivitySession $session, string $centreId): Collection
    {
        return $session->enrollments->filter(fn ($enrollment) => $enrollment->trainee
            && $enrollment->trainee->centre_id === $centreId
            && in_array($enrollment->enrollment_status, config('attendance.eligible_enrollment_statuses'), true)
            && $enrollment->enrollment_date->lte($session->session_date)
            && ($enrollment->enrollment_status !== 'completed'
                || ($enrollment->completion_date && $enrollment->completion_date->gte($session->session_date))))
            ->unique('trainee_id')->values();
    }
}
