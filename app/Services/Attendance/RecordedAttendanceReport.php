<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\Centre;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class RecordedAttendanceReport
{
    public function marks(string $centreId, string $startDate, string $endDate): Builder
    {
        return Attendance::query()
            ->select(['id', 'trainee_id', 'activity_id', 'session_id', 'attendance_date', 'status'])
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereHas('session', fn ($query) => $query->where('session_status', '!=', 'cancelled')
                ->whereColumn('activity_occurrences.activity_id', 'trainee_attendances.activity_id')
                ->whereColumn('activity_occurrences.session_date', 'trainee_attendances.attendance_date'))
            ->whereHas('session.activity', fn ($query) => $query->where('centre_id', $centreId))
            ->whereHas('trainee', fn ($query) => $query->where('centre_id', $centreId))
            ->with(['trainee:id,trainee_id,trainee_first_name,trainee_last_name', 'session:id,activity_id,session_name', 'session.activity:id,activity_name'])
            ->orderByDesc('attendance_date')->orderByDesc('id');
    }

    public function download(array $filters)
    {
        $centreId = $filters['centre_id'] ?? null;
        if (!$centreId) {
            throw ValidationException::withMessages(['centre_id' => 'Select a centre before downloading.']);
        }

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $query = $this->marks($centreId, $startDate, $endDate);
        $safeCentre = preg_replace('/[^A-Za-z0-9_-]/', '-', $centreId);
        $filename = "attendance-{$safeCentre}-{$startDate}-{$endDate}";

        if ($filters['format'] === 'pdf') {
            $marks = $query->limit(2001)->get();
            if ($marks->count() > 2000) {
                throw ValidationException::withMessages(['end_date' => 'Narrow the dates or download CSV for all recorded marks.']);
            }
            $centre = Centre::where('centre_id', $centreId)->firstOrFail();

            return Pdf::loadView('centre.attendance.report-pdf', compact('marks', 'centre', 'startDate', 'endDate'))
                ->setOption('isRemoteEnabled', false)
                ->setPaper('a4', 'landscape')->download($filename.'.pdf');
        }

        return response()->streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['Date', 'Trainee ID', 'Trainee', 'Activity', 'Session', 'Status'], ',', '"', '');
            foreach ($query->lazy(500) as $mark) {
                $cells = [
                    $mark->attendance_date->toDateString(),
                    $mark->trainee?->trainee_id,
                    $mark->trainee?->name,
                    $mark->session?->activity?->activity_name,
                    $mark->session?->session_name,
                    ucfirst($mark->status),
                ];
                fputcsv($stream, array_map($this->spreadsheetText(...), $cells), ',', '"', '');
            }
            fclose($stream);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function spreadsheetText(?string $value): string
    {
        $text = $value ?? '';

        return preg_match('/^(?:[\t\r\n]|\s*[=+\-@＝＋－＠])/u', $text) ? "'".$text : $text;
    }
}
