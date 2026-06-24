<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AssetMovement extends Model
{
    use HasFactory, SoftDeletes;

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

    // Actual DB columns: asset_id, from_location_id, to_location_id,
    // moved_by_user_id, movement_date, reason, notes, timestamps, softDeletes
    protected $fillable = [
        'asset_id',
        'from_location_id',
        'to_location_id',
        'moved_by_user_id',
        'movement_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    protected $appends = [
        'formatted_movement_date',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by_user_id');
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    public function scopeForAsset($query, int $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('moved_by_user_id', $userId);
    }

    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('movement_date', '>=', now()->subDays($days));
    }

    // =============================================
    // ACCESSORS
    // =============================================

    public function getFormattedMovementDateAttribute(): string
    {
        return $this->movement_date ? $this->movement_date->format('M d, Y H:i') : '';
    }

    // =============================================
    // STATIC HELPERS — signatures preserved for call-site compatibility
    // =============================================

    /**
     * Log an asset assignment. $toUserId and $location are encoded in
     * reason/notes since the DB tracks location FKs, not user FKs.
     */
    public static function createAssignment(
        int $assetId,
        int $toUserId,
        ?string $location = null,
        ?string $reason = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'asset_id'         => $assetId,
            'moved_by_user_id' => $performedBy ?: session('id'),
            'reason'           => ($reason ?: 'Asset assignment') . " to user #{$toUserId}",
            'notes'            => $location ? "Location: {$location}" : null,
            'movement_date'    => now(),
        ]);
    }

    /**
     * Log an asset return.
     */
    public static function createReturn(
        int $assetId,
        int $fromUserId,
        ?string $reason = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'asset_id'         => $assetId,
            'moved_by_user_id' => $performedBy ?: session('id'),
            'reason'           => ($reason ?: 'Asset return') . " from user #{$fromUserId}",
            'movement_date'    => now(),
        ]);
    }

    /**
     * Log an asset transfer between locations or users.
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
        $locationNote = implode(' → ', array_filter([$fromLocation, $toLocation])) ?: null;

        return self::create([
            'asset_id'         => $assetId,
            'moved_by_user_id' => $performedBy ?: session('id'),
            'reason'           => $reason ?: 'Asset transfer',
            'notes'            => $locationNote,
            'movement_date'    => now(),
        ]);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

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
            'total_movements'  => $query->count(),
            'recent_activity'  => self::with(['asset', 'performedBy'])
                                      ->recent(7)
                                      ->orderBy('movement_date', 'desc')
                                      ->limit(10)
                                      ->get(),
        ];
    }

    public static function getAssetHistory(int $assetId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forAsset($assetId)
                   ->with(['performedBy'])
                   ->orderBy('movement_date', 'desc')
                   ->get();
    }
}
