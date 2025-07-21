<?php

namespace App\Services;

use App\Models\Trainee;
use App\Models\TraineeAuditLog;
use App\Models\TraineeDocument;
use App\Models\TraineeProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TraineeService
{
    /**
     * Generate unique identifier for trainee
     */
    public function generateUniqueIdentifier()
    {
        $year = date('Y');
        $centre_id = session('centre_id');
        
        // Get the latest trainee number for this year and centre
        $lastTrainee = Trainee::where('centre_id', $centre_id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($lastTrainee && preg_match('/TRN' . $year . sprintf('%03d', $centre_id) . '(\d{4})/', $lastTrainee->unique_identifier, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }
        
        return 'TRN' . $year . sprintf('%03d', $centre_id) . sprintf('%04d', $nextNumber);
    }

    /**
     * Check for potential duplicate trainees
     */
    public function checkForDuplicates($name, $dateOfBirth, $guardianPhone, $excludeId = null)
    {
        $query = Trainee::where('name', 'LIKE', "%{$name}%")
            ->where('date_of_birth', $dateOfBirth);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Check for similar names or exact guardian phone
        $potentialDuplicates = $query->orWhere(function($q) use ($guardianPhone) {
            $q->whereRaw("JSON_EXTRACT(guardian_info, '$.guardian_phone') = ?", [$guardianPhone]);
        })->get();
        
        return $potentialDuplicates;
    }

    /**
     * Get trainee statistics for a centre
     */
    public function getStatistics($centreId)
    {
        $cacheKey = "trainee_stats_{$centreId}";
        
        return Cache::remember($cacheKey, 300, function() use ($centreId) {
            $baseQuery = Trainee::where('centre_id', $centreId);
            
            return [
                'total' => $baseQuery->count(),
                'active' => $baseQuery->where('status', 'active')->count(),
                'inactive' => $baseQuery->where('status', 'inactive')->count(),
                'graduated' => $baseQuery->where('status', 'graduated')->count(),
                'transferred' => $baseQuery->where('status', 'transferred')->count(),
                'new_this_month' => $baseQuery->whereMonth('admission_date', now()->month)
                    ->whereYear('admission_date', now()->year)->count(),
                'birthdays_this_month' => $baseQuery->whereMonth('date_of_birth', now()->month)->count(),
                'documents_expiring' => TraineeDocument::whereHas('trainee', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })->expiringSoon()->count(),
                'progress_assessments_due' => $this->getProgressAssessmentsDue($centreId),
                'conditions' => $this->getConditionStats($centreId),
                'age_distribution' => $this->getAgeDistribution($centreId)
            ];
        });
    }

    /**
     * Get condition statistics
     */
    private function getConditionStats($centreId)
    {
        return Trainee::where('centre_id', $centreId)
            ->where('status', 'active')
            ->select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
            ->pluck('count', 'condition')
            ->toArray();
    }

    /**
     * Get age distribution
     */
    private function getAgeDistribution($centreId)
    {
        $trainees = Trainee::where('centre_id', $centreId)
            ->where('status', 'active')
            ->selectRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age')
            ->pluck('age');
            
        $distribution = [
            '0-5' => 0,
            '6-12' => 0,
            '13-18' => 0,
            '19-25' => 0,
            '26+' => 0
        ];
        
        foreach ($trainees as $age) {
            if ($age <= 5) $distribution['0-5']++;
            elseif ($age <= 12) $distribution['6-12']++;
            elseif ($age <= 18) $distribution['13-18']++;
            elseif ($age <= 25) $distribution['19-25']++;
            else $distribution['26+']++;
        }
        
        return $distribution;
    }

    /**
     * Get trainees with progress assessments due
     */
    private function getProgressAssessmentsDue($centreId)
    {
        // Consider assessment due if no progress recorded in last 3 months
        $threeMonthsAgo = now()->subMonths(3);
        
        return Trainee::where('centre_id', $centreId)
            ->where('status', 'active')
            ->whereDoesntHave('progress', function($q) use ($threeMonthsAgo) {
                $q->where('assessment_date', '>', $threeMonthsAgo);
            })
            ->count();
    }

    /**
     * Search trainees with advanced filters
     */
    public function searchTrainees($filters, $centreId)
    {
        $query = Trainee::with(['documents', 'progress'])
            ->where('centre_id', $centreId);

        // Text search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('unique_identifier', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhereRaw("JSON_EXTRACT(guardian_info, '$.guardian_name') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(guardian_info, '$.guardian_phone') LIKE ?", ["%{$search}%"]);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Condition filter
        if (!empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        // Age range filter
        if (!empty($filters['age_from'])) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [$filters['age_from']]);
        }

        if (!empty($filters['age_to'])) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [$filters['age_to']]);
        }

        // Date range filters
        if (!empty($filters['admission_from'])) {
            $query->where('admission_date', '>=', $filters['admission_from']);
        }

        if (!empty($filters['admission_to'])) {
            $query->where('admission_date', '<=', $filters['admission_to']);
        }

        // Tag filter
        if (!empty($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : explode(',', $filters['tags']);
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', trim($tag));
            }
        }

        // Progress filter
        if (!empty($filters['progress_status'])) {
            switch ($filters['progress_status']) {
                case 'excellent':
                    $query->whereHas('progress', function($q) {
                        $q->havingRaw('AVG(current_score) >= 80');
                    });
                    break;
                case 'needs_assessment':
                    $query->whereDoesntHave('progress', function($q) {
                        $q->where('assessment_date', '>', now()->subMonths(3));
                    });
                    break;
            }
        }

        return $query;
    }

    /**
     * Create trainee with full validation and audit trail
     */
    public function createTrainee($data)
    {
        DB::beginTransaction();
        try {
            // Check for duplicates
            $duplicates = $this->checkForDuplicates(
                $data['name'], 
                $data['date_of_birth'], 
                $data['guardian_phone']
            );

            if ($duplicates->isNotEmpty()) {
                throw new \Exception('Potential duplicate trainee detected. Please verify the information.');
            }

            // Generate unique identifier
            $data['unique_identifier'] = $this->generateUniqueIdentifier();
            $data['centre_id'] = session('centre_id');
            $data['status'] = 'active';
            $data['last_updated_by'] = session('id');

            // Create trainee
            $trainee = Trainee::create($data);

            // Log creation
            TraineeAuditLog::logAction(
                $trainee->id,
                'created',
                null,
                $trainee->toArray(),
                'Trainee record created'
            );

            // Clear cache
            Cache::forget("trainee_stats_{$trainee->centre_id}");

            DB::commit();
            return $trainee;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update trainee with audit trail
     */
    public function updateTrainee($trainee, $data)
    {
        DB::beginTransaction();
        try {
            $oldValues = $trainee->toArray();
            
            // Update last_updated_by
            $data['last_updated_by'] = session('id');
            
            $trainee->update($data);
            
            // Log update
            TraineeAuditLog::logAction(
                $trainee->id,
                'updated',
                $oldValues,
                $trainee->fresh()->toArray(),
                'Trainee record updated'
            );

            // Clear cache
            Cache::forget("trainee_stats_{$trainee->centre_id}");

            DB::commit();
            return $trainee;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete trainee with audit trail
     */
    public function deleteTrainee($trainee)
    {
        DB::beginTransaction();
        try {
            $traineeData = $trainee->toArray();
            
            // Log deletion before actual deletion
            TraineeAuditLog::logAction(
                $trainee->id,
                'deleted',
                $traineeData,
                null,
                'Trainee record deleted'
            );

            // Delete associated files
            if ($trainee->avatar) {
                Storage::disk('public')->delete($trainee->avatar);
            }

            $trainee->delete();

            // Clear cache
            Cache::forget("trainee_stats_{$trainee->centre_id}");

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Bulk update trainees
     */
    public function bulkUpdate($traineeIds, $action, $data = [])
    {
        DB::beginTransaction();
        try {
            $updated = 0;
            
            foreach ($traineeIds as $id) {
                $trainee = Trainee::find($id);
                if (!$trainee) continue;

                $oldValues = $trainee->toArray();
                
                switch ($action) {
                    case 'activate':
                        $trainee->update(['status' => 'active', 'last_updated_by' => session('id')]);
                        break;
                    case 'deactivate':
                        $trainee->update(['status' => 'inactive', 'last_updated_by' => session('id')]);
                        break;
                    case 'graduate':
                        $trainee->update([
                            'status' => 'graduated', 
                            'graduation_date' => now()->toDateString(),
                            'last_updated_by' => session('id')
                        ]);
                        break;
                    case 'transfer':
                        $trainee->update(['status' => 'transferred', 'last_updated_by' => session('id')]);
                        break;
                    case 'update_tags':
                        $trainee->update(['tags' => $data['tags'], 'last_updated_by' => session('id')]);
                        break;
                }

                // Log bulk action
                TraineeAuditLog::logAction(
                    $trainee->id,
                    'bulk_' . $action,
                    $oldValues,
                    $trainee->fresh()->toArray(),
                    "Bulk action: {$action}"
                );

                $updated++;
            }

            // Clear cache
            Cache::forget("trainee_stats_" . session('centre_id'));

            DB::commit();
            return $updated;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Generate trainee report data
     */
    public function generateReportData($filters = [])
    {
        $query = $this->searchTrainees($filters, session('centre_id'));
        
        return $query->with([
            'documents' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'progress' => function($q) {
                $q->orderBy('assessment_date', 'desc');
            }
        ])->get();
    }

    /**
     * Get upcoming birthdays
     */
    public function getUpcomingBirthdays($days = 30)
    {
        $startDate = now();
        $endDate = now()->addDays($days);
        
        return Trainee::where('centre_id', session('centre_id'))
            ->where('status', 'active')
            ->whereRaw("
                DATE_ADD(
                    DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(date_of_birth), '-', DAY(date_of_birth))),
                    INTERVAL IF(
                        DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(date_of_birth), '-', DAY(date_of_birth))) < CURDATE(),
                        1,
                        0
                    ) YEAR
                ) BETWEEN ? AND ?
            ", [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByRaw("
                DATE_ADD(
                    DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(date_of_birth), '-', DAY(date_of_birth))),
                    INTERVAL IF(
                        DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(date_of_birth), '-', DAY(date_of_birth))) < CURDATE(),
                        1,
                        0
                    ) YEAR
                )
            ")
            ->get();
    }
}