<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AssetMovement extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('centre_isolation', function (Builder $builder) {
            $role = session('role');
            $centreId = session('centre_id');

            if (!$role || !$centreId) return;
            if ($role === 'admin') return;

            $builder->whereHas('asset', function ($q) use ($centreId) {
                $q->where('assets.centre_id', $centreId);
            });
        });
    }

    protected $fillable = [
        'asset_id',
        'type',
        'from_user',
        'to_user',
        'from_location',
        'to_location',
        'reason',
        'performed_by',
        'movement_date'
    ];

    protected $casts = [
        'movement_date' => 'datetime'
    ];

    protected $appends = [
        'type_badge_class',
        'formatted_movement_date'
    ];

    /**
     * Movement type constants
     */
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_RETURN = 'return';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_DISPOSAL = 'disposal';

    /**
     * Get all movement types
     */
    public static function getMovementTypes(): array
    {
        return [
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_DISPOSAL => 'Disposal',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the asset this movement belongs to
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who the asset was moved from
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user');
    }

    /**
     * Get the user who the asset was moved to
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user');
    
    }
    /**
     * Get the user who performed the movement
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
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
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by user (from or to)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('from_user', $userId)
              ->orWhere('to_user', $userId);
        });
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to find assignments
     */
    public function scopeAssignments($query)
    {
        return $query->where('type', self::TYPE_ASSIGNMENT);
    }

    /**
     * Scope a query to find returns
     */
    public function scopeReturns($query)
    {
        return $query->where('type', self::TYPE_RETURN);
    }

    /**
     * Scope a query to find transfers
     */
    public function scopeTransfers($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    /**
     * Scope a query to find recent movements
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('movement_date', '>=', now()->subDays($days));
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get type badge class for UI
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ASSIGNMENT => 'badge-primary',
            self::TYPE_RETURN => 'badge-info',
            self::TYPE_TRANSFER => 'badge-warning',
            self::TYPE_MAINTENANCE => 'badge-secondary',
            self::TYPE_DISPOSAL => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get formatted movement date
     */
    public function getFormattedMovementDateAttribute(): string
    {
        return $this->movement_date ? $this->movement_date->format('M d, Y H:i') : '';
    }

    /**
     * Get movement type label
     */
    public function getMovementTypeLabelAttribute(): string
    {
        $types = self::getMovementTypes();
        return $types[$this->type] ?? 'Unknown';
    }

    /**
     * Get movement description for display
     */
    public function getMovementDescriptionAttribute(): string
    {
        switch ($this->type) {
            case self::TYPE_ASSIGNMENT:
                $toUser = $this->toUser ? $this->toUser->name : 'Unknown User';
                $location = $this->to_location ? " at {$this->to_location}" : '';
                return "Assigned to {$toUser}{$location}";
                
            case self::TYPE_RETURN:
                $fromUser = $this->fromUser ? $this->fromUser->name : 'Unknown User';
                return "Returned from {$fromUser}";
                
            case self::TYPE_TRANSFER:
                $from = $this->from_location ?: ($this->fromUser ? $this->fromUser->name : 'Unknown');
                $to = $this->to_location ?: ($this->toUser ? $this->toUser->name : 'Unknown');
                return "Transferred from {$from} to {$to}";
                
            case self::TYPE_MAINTENANCE:
                return "Moved for maintenance";
                
            case self::TYPE_DISPOSAL:
                return "Disposed";
                
            default:
                return $this->reason ?: 'Asset movement';
        }
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Create assignment record
     */
    public static function createAssignment(
        int $assetId, 
        int $toUserId, 
        ?string $location = null, 
        ?string $reason = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'asset_id' => $assetId,
            'type' => self::TYPE_ASSIGNMENT,
            'to_user' => $toUserId,
            'to_location' => $location,
            'reason' => $reason ?: 'Asset assignment',
            'performed_by' => $performedBy ?: session('id'),
            'movement_date' => now()
        ]);
    }

    /**
     * Create return record
     */
    public static function createReturn(
        int $assetId, 
        int $fromUserId, 
        ?string $reason = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'asset_id' => $assetId,
            'type' => self::TYPE_RETURN,
            'from_user' => $fromUserId,
            'reason' => $reason ?: 'Asset return',
            'performed_by' => $performedBy ?: session('id'),
            'movement_date' => now()
        ]);
    }

    /**
     * Create transfer record
     */
    public static function createTransfer(
        int $assetId,
        ?int $fromUserId = null,
        ?int $toUserId = null,
        ?string $fromLocation = null,
        ?string $toLocation = null,
        ?string $reason = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'asset_id' => $assetId,
            'type' => self::TYPE_TRANSFER,
            'from_user' => $fromUserId,
            'to_user' => $toUserId,
            'from_location' => $fromLocation,
            'to_location' => $toLocation,
            'reason' => $reason ?: 'Asset transfer',
            'performed_by' => $performedBy ?: session('id'),
            'movement_date' => now()
        ]);
    }

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
            'assignments' => $query->clone()->assignments()->count(),
            'returns' => $query->clone()->returns()->count(),
            'transfers' => $query->clone()->transfers()->count(),
            'by_type' => $query->selectRaw('type, COUNT(*) as count')
                              ->groupBy('type')
                              ->pluck('count', 'type')
                              ->toArray(),
            'recent_activity' => self::with(['asset', 'fromUser', 'toUser', 'performedBy'])
                                    ->recent(7)
                                    ->orderBy('movement_date', 'desc')
                                    ->limit(10)
                                    ->get()
        ];
    }

    /**
     * Get asset history
     */
    public static function getAssetHistory(int $assetId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forAsset($assetId)
                   ->with(['fromUser', 'toUser', 'performedBy'])
                   ->orderBy('movement_date', 'desc')
                   ->get();
    }
}