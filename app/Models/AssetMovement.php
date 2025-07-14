<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Asset Movement Model
 * 
 * This model tracks all asset movements between locations,
 * providing a comprehensive audit trail for asset location history.
 */
class AssetMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'from_location_id', 'to_location_id',
        'moved_by_id', 'movement_reason', 'movement_date',
        'movement_type', 'approved_by_id', 'approval_date',
        'status', 'notes', 'estimated_return_date',
        'actual_return_date', 'condition_before', 'condition_after'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'approval_date' => 'datetime',
        'estimated_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
    ];

    protected $appends = [
        'movement_duration', 'is_return_overdue', 'movement_type_label',
        'status_badge_class', 'distance_traveled'
    ];

    /**
     * Movement type constants
     */
    const TYPE_TRANSFER = 'transfer';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_RETURN = 'return';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_LOAN = 'loan';
    const TYPE_DISPOSAL = 'disposal';
    const TYPE_AUDIT = 'audit';
    const TYPE_EMERGENCY = 'emergency';

    /**
     * Movement status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    /**
     * Get all movement types
     */
    public static function getMovementTypes(): array
    {
        return [
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_LOAN => 'Loan',
            self::TYPE_DISPOSAL => 'Disposal',
            self::TYPE_AUDIT => 'Audit Check',
            self::TYPE_EMERGENCY => 'Emergency Move',
        ];
    }

    /**
     * Get all movement statuses
     */
    public static function getMovementStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the asset that was moved
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the source location
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'from_location_id');
    }

    /**
     * Get the destination location
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'to_location_id');
    }

    /**
     * Get the user who moved the asset
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'moved_by_id');
    }

    /**
     * Get the user who approved the movement
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'approved_by_id');
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
     * Scope a query to filter by movement type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to find movements in current month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('movement_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Scope a query to find pending movements
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to find completed movements
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to find overdue returns
     */
    public function scopeOverdueReturns($query)
    {
        return $query->where('movement_type', self::TYPE_LOAN)
                     ->whereNotNull('estimated_return_date')
                     ->where('estimated_return_date', '<', now())
                     ->whereNull('actual_return_date');
    }

    /**
     * Scope a query to find movements involving a specific location
     */
    public function scopeInvolvingLocation($query, int $locationId)
    {
        return $query->where(function ($q) use ($locationId) {
            $q->where('from_location_id', $locationId)
              ->orWhere('to_location_id', $locationId);
        });
    }

    /**
     * Scope a query to find movements by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('moved_by_id', $userId);
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get movement duration in hours
     */
    public function getMovementDurationAttribute(): ?float
    {
        if (!$this->movement_date) {
            return null;
        }

        $endDate = $this->actual_return_date ?? now();
        return $this->movement_date->diffInHours($endDate, true);
    }

    /**
     * Check if return is overdue
     */
    public function getIsReturnOverdueAttribute(): bool
    {
        return $this->movement_type === self::TYPE_LOAN &&
               $this->estimated_return_date &&
               $this->estimated_return_date->isPast() &&
               is_null($this->actual_return_date);
    }

    /**
     * Get movement type label
     */
    public function getMovementTypeLabelAttribute(): string
    {
        $types = self::getMovementTypes();
        return $types[$this->movement_type] ?? 'Unknown';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_IN_TRANSIT => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-secondary',
            self::STATUS_FAILED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get estimated distance traveled (placeholder for future GPS integration)
     */
    public function getDistanceTraveledAttribute(): ?float
    {
        // Placeholder for future implementation with GPS coordinates
        return null;
    }

    /**
     * Get formatted movement details
     */
    public function getMovementSummaryAttribute(): string
    {
        $from = $this->fromLocation ? $this->fromLocation->name : 'Unknown';
        $to = $this->toLocation ? $this->toLocation->name : 'Unknown';
        
        return "From: {$from} → To: {$to}";
    }

    /**
     * Get days since movement
     */
    public function getDaysSinceMovementAttribute(): int
    {
        return $this->movement_date ? $this->movement_date->diffInDays(now()) : 0;
    }

    /**
     * Check if movement requires approval
     */
    public function getRequiresApprovalAttribute(): bool
    {
        // High-value assets or certain movement types might require approval
        return in_array($this->movement_type, [
            self::TYPE_DISPOSAL,
            self::TYPE_TRANSFER,
            self::TYPE_EMERGENCY
        ]);
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Mark movement as completed
     */
    public function markCompleted(string $notes = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->actual_return_date = now();
        
        if ($notes) {
            $this->notes = $this->notes ? $this->notes . "\n" . $notes : $notes;
        }
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset movement completed', [
                'movement_id' => $this->id,
                'asset_id' => $this->asset_id,
                'from_location' => $this->fromLocation?->name,
                'to_location' => $this->toLocation?->name
            ]);
        }
        
        return $success;
    }

    /**
     * Cancel movement
     */
    public function cancel(string $reason): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->notes = $this->notes ? $this->notes . "\nCancelled: " . $reason : "Cancelled: " . $reason;
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset movement cancelled', [
                'movement_id' => $this->id,
                'asset_id' => $this->asset_id,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Approve movement
     */
    public function approve(int $approvedById, string $notes = null): bool
    {
        $this->approved_by_id = $approvedById;
        $this->approval_date = now();
        $this->status = self::STATUS_IN_TRANSIT;
        
        if ($notes) {
            $this->notes = $this->notes ? $this->notes . "\nApproval notes: " . $notes : $notes;
        }
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset movement approved', [
                'movement_id' => $this->id,
                'asset_id' => $this->asset_id,
                'approved_by' => $approvedById
            ]);
        }
        
        return $success;
    }

    /**
     * Create return movement
     */
    public function createReturnMovement(string $reason = 'Return from loan'): self
    {
        return self::create([
            'asset_id' => $this->asset_id,
            'from_location_id' => $this->to_location_id,
            'to_location_id' => $this->from_location_id,
            'moved_by_id' => auth()->id() ?? session('id'),
            'movement_reason' => $reason,
            'movement_date' => now(),
            'movement_type' => self::TYPE_RETURN,
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Get movement statistics
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
            'total_movements' => $query->count(),
            'by_type' => $query->selectRaw('movement_type, COUNT(*) as count')
                              ->groupBy('movement_type')
                              ->pluck('count', 'movement_type')
                              ->toArray(),
            'by_status' => $query->selectRaw('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->pluck('count', 'status')
                                ->toArray(),
            'pending_count' => $query->where('status', self::STATUS_PENDING)->count(),
            'overdue_returns' => $query->overdueReturns()->count(),
            'movements_this_week' => self::whereBetween('movement_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
    }

    /**
     * Get recent movements
     */
    public static function getRecent(int $limit = 10, ?int $centreId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::with(['asset', 'fromLocation', 'toLocation', 'movedBy'])
                     ->latest('movement_date');
        
        if ($centreId) {
            $query->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Get movements requiring attention
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
            'pending_approval' => $baseQuery->pending()
                                           ->where('requires_approval', true)
                                           ->with(['asset', 'fromLocation', 'toLocation', 'movedBy'])
                                           ->get(),
            'overdue_returns' => $baseQuery->overdueReturns()
                                          ->with(['asset', 'fromLocation', 'toLocation', 'movedBy'])
                                          ->get(),
            'in_transit_long' => $baseQuery->where('status', self::STATUS_IN_TRANSIT)
                                          ->where('movement_date', '<', now()->subDays(7))
                                          ->with(['asset', 'fromLocation', 'toLocation', 'movedBy'])
                                          ->get(),
        ];
    }

    /**
     * Generate movement report
     */
    public static function generateReport(?int $centreId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        $query = self::betweenDates($startDate, $endDate);
        
        if ($centreId) {
            $query->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        $movements = $query->with(['asset', 'fromLocation', 'toLocation', 'movedBy'])->get();
        
        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_movements' => $movements->count(),
                'unique_assets' => $movements->pluck('asset_id')->unique()->count(),
                'active_users' => $movements->pluck('moved_by_id')->unique()->count(),
            ],
            'by_type' => $movements->groupBy('movement_type')->map->count(),
            'by_status' => $movements->groupBy('status')->map->count(),
            'busiest_locations' => $movements->flatMap(function ($movement) {
                return [$movement->from_location_id, $movement->to_location_id];
            })->filter()->countBy()->sort()->reverse()->take(10),
            'most_moved_assets' => $movements->groupBy('asset_id')->map->count()->sort()->reverse()->take(10),
        ];
    }
}