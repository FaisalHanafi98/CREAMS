<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Asset Maintenance Model
 * 
 * This model handles all aspects of asset maintenance including
 * scheduling, tracking, cost management, and compliance monitoring.
 */
class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_maintenance';

    protected $fillable = [
        'asset_id', 'maintenance_type', 'scheduled_date', 'completed_date',
        'performed_by_id', 'vendor_id', 'status', 'priority',
        'description', 'cost', 'parts_cost', 'labor_cost',
        'downtime_start', 'downtime_end', 'next_maintenance_date',
        'certification_required', 'certification_obtained',
        'warranty_work', 'work_order_number', 'notes',
        'preventive_maintenance', 'compliance_check'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
        'downtime_start' => 'datetime',
        'downtime_end' => 'datetime',
        'next_maintenance_date' => 'datetime',
        'cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'certification_required' => 'boolean',
        'certification_obtained' => 'boolean',
        'warranty_work' => 'boolean',
        'preventive_maintenance' => 'boolean',
        'compliance_check' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'total_cost', 'downtime_hours', 'is_overdue', 'days_until_due',
        'status_badge_class', 'priority_badge_class', 'efficiency_score'
    ];

    /**
     * Maintenance type constants
     */
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_CALIBRATION = 'calibration';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_CLEANING = 'cleaning';
    const TYPE_SAFETY_CHECK = 'safety_check';

    /**
     * Status constants
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * Get all maintenance types
     */
    public static function getMaintenanceTypes(): array
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventive Maintenance',
            self::TYPE_CORRECTIVE => 'Corrective Maintenance',
            self::TYPE_EMERGENCY => 'Emergency Repair',
            self::TYPE_INSPECTION => 'Inspection',
            self::TYPE_CALIBRATION => 'Calibration',
            self::TYPE_UPGRADE => 'Upgrade',
            self::TYPE_CLEANING => 'Cleaning',
            self::TYPE_SAFETY_CHECK => 'Safety Check',
        ];
    }

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    /**
     * Get all priorities
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the asset this maintenance belongs to
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who performed the maintenance
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'performed_by_id');
    }

    /**
     * Get the vendor who performed the maintenance
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    /**
     * Scope a query to filter by asset
     */
    public function scopeForAsset($query, int $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    /**
     * Scope a query to filter by maintenance type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('maintenance_type', $type);
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by priority
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to find scheduled maintenance
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope a query to find in-progress maintenance
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope a query to find completed maintenance
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to find overdue maintenance
     */
    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now())
                     ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope a query to find due soon maintenance
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereBetween('scheduled_date', [now(), now()->addDays($days)])
                     ->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope a query to find preventive maintenance
     */
    public function scopePreventive($query)
    {
        return $query->where('preventive_maintenance', true);
    }

    /**
     * Scope a query to find high priority maintenance
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to find maintenance this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('scheduled_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get total maintenance cost
     */
    public function getTotalCostAttribute(): float
    {
        return (float)($this->cost ?? ($this->parts_cost + $this->labor_cost));
    }

    /**
     * Get downtime in hours
     */
    public function getDowntimeHoursAttribute(): ?float
    {
        if ($this->downtime_start && $this->downtime_end) {
            return $this->downtime_start->diffInHours($this->downtime_end, true);
        }
        return null;
    }

    /**
     * Check if maintenance is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->scheduled_date &&
               $this->scheduled_date->isPast() &&
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->scheduled_date) {
            return null;
        }
        
        $days = $this->scheduled_date->diffInDays(now(), false);
        return $this->scheduled_date->isFuture() ? $days : -$days;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'badge-info',
            self::STATUS_IN_PROGRESS => 'badge-warning',
            self::STATUS_ON_HOLD => 'badge-secondary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-secondary',
            self::STATUS_FAILED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get priority badge class for UI
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'badge-light',
            self::PRIORITY_MEDIUM => 'badge-info',
            self::PRIORITY_HIGH => 'badge-warning',
            self::PRIORITY_CRITICAL => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Calculate maintenance efficiency score
     */
    public function getEfficiencyScoreAttribute(): ?float
    {
        if ($this->status !== self::STATUS_COMPLETED || !$this->scheduled_date || !$this->completed_date) {
            return null;
        }

        $scheduledDays = $this->scheduled_date->diffInDays($this->completed_date, false);
        
        // Perfect score if completed on time or early
        if ($scheduledDays <= 0) {
            return 100;
        }
        
        // Reduce score based on delay
        $score = max(0, 100 - ($scheduledDays * 10));
        return round($score, 1);
    }

    /**
     * Get maintenance type label
     */
    public function getMaintenanceTypeLabelAttribute(): string
    {
        $types = self::getMaintenanceTypes();
        return $types[$this->maintenance_type] ?? 'Unknown';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        $priorities = self::getPriorities();
        return $priorities[$this->priority] ?? 'Unknown';
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Start maintenance work
     */
    public function start(int $performedById = null): bool
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->downtime_start = now();
        
        if ($performedById) {
            $this->performed_by_id = $performedById;
        }
        
        $success = $this->save();
        
        if ($success) {
            // Update asset status to maintenance
            $this->asset->update(['status' => Asset::STATUS_MAINTENANCE]);
            
            \Log::info('Maintenance started', [
                'maintenance_id' => $this->id,
                'asset_id' => $this->asset_id,
                'performed_by' => $this->performed_by_id
            ]);
        }
        
        return $success;
    }

    /**
     * Complete maintenance work
     */
    public function complete(array $data = []): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_date = now();
        $this->downtime_end = now();
        
        // Update fields from data array
        if (isset($data['cost'])) {
            $this->cost = $data['cost'];
        }
        if (isset($data['parts_cost'])) {
            $this->parts_cost = $data['parts_cost'];
        }
        if (isset($data['labor_cost'])) {
            $this->labor_cost = $data['labor_cost'];
        }
        if (isset($data['notes'])) {
            $this->notes = $data['notes'];
        }
        if (isset($data['certification_obtained'])) {
            $this->certification_obtained = $data['certification_obtained'];
        }
        
        $success = $this->save();
        
        if ($success) {
            // Update asset status back to available
            $this->asset->update([
                'status' => Asset::STATUS_AVAILABLE,
                'last_maintenance_date' => $this->completed_date
            ]);
            
            // Schedule next maintenance if it's preventive
            if ($this->preventive_maintenance && $this->next_maintenance_date) {
                $this->scheduleNext();
            }
            
            \Log::info('Maintenance completed', [
                'maintenance_id' => $this->id,
                'asset_id' => $this->asset_id,
                'total_cost' => $this->total_cost,
                'downtime_hours' => $this->downtime_hours
            ]);
        }
        
        return $success;
    }

    /**
     * Cancel maintenance
     */
    public function cancel(string $reason): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->notes = $this->notes ? $this->notes . "\nCancelled: " . $reason : "Cancelled: " . $reason;
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Maintenance cancelled', [
                'maintenance_id' => $this->id,
                'asset_id' => $this->asset_id,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Put maintenance on hold
     */
    public function putOnHold(string $reason): bool
    {
        $this->status = self::STATUS_ON_HOLD;
        $this->notes = $this->notes ? $this->notes . "\nOn hold: " . $reason : "On hold: " . $reason;
        
        return $this->save();
    }

    /**
     * Resume maintenance from hold
     */
    public function resume(): bool
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->notes = $this->notes ? $this->notes . "\nResumed at: " . now() : "Resumed at: " . now();
        
        return $this->save();
    }

    /**
     * Reschedule maintenance
     */
    public function reschedule(Carbon $newDate, string $reason): bool
    {
        $oldDate = $this->scheduled_date;
        $this->scheduled_date = $newDate;
        $this->notes = $this->notes ? 
            $this->notes . "\nRescheduled from {$oldDate} to {$newDate}: {$reason}" : 
            "Rescheduled from {$oldDate} to {$newDate}: {$reason}";
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Maintenance rescheduled', [
                'maintenance_id' => $this->id,
                'asset_id' => $this->asset_id,
                'old_date' => $oldDate,
                'new_date' => $newDate,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Schedule next maintenance
     */
    public function scheduleNext(): ?self
    {
        if (!$this->preventive_maintenance || !$this->next_maintenance_date) {
            return null;
        }
        
        return self::create([
            'asset_id' => $this->asset_id,
            'maintenance_type' => $this->maintenance_type,
            'scheduled_date' => $this->next_maintenance_date,
            'status' => self::STATUS_SCHEDULED,
            'priority' => $this->priority,
            'description' => $this->description,
            'preventive_maintenance' => true,
            'certification_required' => $this->certification_required,
        ]);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Get maintenance statistics
     */
    public static function getStatistics(?int $centreId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = self::query();
        
        if ($centreId) {
            $query->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        return [
            'total_maintenance' => $query->count(),
            'completed_count' => $query->completed()->count(),
            'overdue_count' => $query->overdue()->count(),
            'in_progress_count' => $query->inProgress()->count(),
            'total_cost' => $query->completed()->sum('cost'),
            'average_cost' => $query->completed()->avg('cost'),
            'average_downtime' => $query->completed()
                                       ->whereNotNull('downtime_start')
                                       ->whereNotNull('downtime_end')
                                       ->get()
                                       ->avg('downtime_hours'),
            'by_type' => $query->selectRaw('maintenance_type, COUNT(*) as count')
                              ->groupBy('maintenance_type')
                              ->pluck('count', 'maintenance_type')
                              ->toArray(),
            'by_priority' => $query->selectRaw('priority, COUNT(*) as count')
                                  ->groupBy('priority')
                                  ->pluck('count', 'priority')
                                  ->toArray(),
        ];
    }

    /**
     * Get maintenance requiring attention
     */
    public static function getRequiringAttention(?int $centreId = null): array
    {
        $baseQuery = self::query();
        
        if ($centreId) {
            $baseQuery->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        return [
            'overdue' => $baseQuery->overdue()
                                  ->with(['asset', 'performedBy'])
                                  ->orderBy('scheduled_date')
                                  ->get(),
            'due_soon' => $baseQuery->dueSoon()
                                   ->with(['asset', 'performedBy'])
                                   ->orderBy('scheduled_date')
                                   ->get(),
            'high_priority' => $baseQuery->highPriority()
                                        ->scheduled()
                                        ->with(['asset', 'performedBy'])
                                        ->orderBy('scheduled_date')
                                        ->get(),
            'long_running' => $baseQuery->inProgress()
                                       ->where('downtime_start', '<', now()->subDays(3))
                                       ->with(['asset', 'performedBy'])
                                       ->get(),
        ];
    }

    /**
     * Generate maintenance schedule for asset
     */
    public static function generateScheduleForAsset(Asset $asset, int $months = 12): array
    {
        $schedule = [];
        
        if (!$asset->assetType || !$asset->assetType->maintenance_interval) {
            return $schedule;
        }
        
        $startDate = $asset->last_maintenance_date ?? $asset->purchase_date ?? now();
        $interval = $asset->assetType->maintenance_interval;
        
        for ($i = 1; $i <= $months; $i++) {
            $scheduledDate = $startDate->copy()->addDays($interval * $i);
            
            if ($scheduledDate->isFuture()) {
                $schedule[] = [
                    'scheduled_date' => $scheduledDate,
                    'maintenance_type' => self::TYPE_PREVENTIVE,
                    'description' => "Scheduled preventive maintenance for {$asset->name}",
                    'priority' => self::PRIORITY_MEDIUM,
                ];
            }
        }
        
        return $schedule;
    }

    /**
     * Get maintenance calendar data
     */
    public static function getCalendarData(?int $centreId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        $query = self::betweenDates($startDate, $endDate)
                     ->with(['asset', 'performedBy']);
        
        if ($centreId) {
            $query->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        return $query->get()->map(function ($maintenance) {
            return [
                'id' => $maintenance->id,
                'title' => "{$maintenance->asset->name} - {$maintenance->maintenance_type_label}",
                'start' => $maintenance->scheduled_date->toISOString(),
                'end' => $maintenance->completed_date?->toISOString(),
                'backgroundColor' => $this->getStatusColor($maintenance->status),
                'borderColor' => $this->getPriorityColor($maintenance->priority),
                'url' => route('maintenance.show', $maintenance->id),
                'extendedProps' => [
                    'asset_name' => $maintenance->asset->name,
                    'type' => $maintenance->maintenance_type,
                    'status' => $maintenance->status,
                    'priority' => $maintenance->priority,
                    'cost' => $maintenance->total_cost,
                ]
            ];
        })->toArray();
    }

    /**
     * Get status color for calendar
     */
    private static function getStatusColor(string $status): string
    {
        return match($status) {
            self::STATUS_SCHEDULED => '#007bff',
            self::STATUS_IN_PROGRESS => '#ffc107',
            self::STATUS_COMPLETED => '#28a745',
            self::STATUS_CANCELLED => '#6c757d',
            self::STATUS_FAILED => '#dc3545',
            default => '#6c757d',
        };
    }

    /**
     * Get priority color for calendar
     */
    private static function getPriorityColor(string $priority): string
    {
        return match($priority) {
            self::PRIORITY_LOW => '#28a745',
            self::PRIORITY_MEDIUM => '#007bff',
            self::PRIORITY_HIGH => '#ffc107',
            self::PRIORITY_CRITICAL => '#dc3545',
            default => '#6c757d',
        };
    }
}