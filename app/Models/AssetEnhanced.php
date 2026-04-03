<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Enhanced Asset Model
 *
 * This model provides comprehensive asset management capabilities
 * including tracking, maintenance, depreciation, and movement history.
 */
class AssetEnhanced extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

    protected $table = 'assets';

    protected $fillable = [
        'asset_tag',
        'asset_name',
        'asset_description',
        'type_id',
        'centre_id',
        'location_id',
        'assigned_to_user',
        'manufacturer',
        'model_number',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'status',
        'warranty_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval',
        'image_path',
        'qr_code',
        'rfid_tag',
        'barcode',
        'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'maintenance_interval' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    // Status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in-use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DISPOSED = 'disposed';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Under Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the asset type
     */
    public function assetParent(): BelongsTo
    {
        return $this->belongsTo(AssetParent::class, 'type_id');
    }

    /**
     * Get the centre this asset belongs to
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the location of this asset
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Get the user this asset is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user');
    }

    /**
     * Get the maintenance records for this asset
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    /**
     * Get the movement history for this asset
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'asset_id');
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get available assets
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope to get assets in use
     */
    public function scopeInUse($query)
    {
        return $query->where('status', self::STATUS_IN_USE);
    }

    /**
     * Scope to get assets needing maintenance
     */
    public function scopeNeedingMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now())
            ->whereNotNull('next_maintenance_date');
    }

    /**
     * Scope to search assets
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('asset_tag', 'LIKE', "%{$search}%")
                ->orWhere('brand', 'LIKE', "%{$search}%")
                ->orWhere('model', 'LIKE', "%{$search}%")
                ->orWhere('serial_number', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope to filter by centre
     */
    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope to filter by asset type
     */
    public function scopeOfType($query, $assetParentId)
    {
        return $query->where('type_id', $assetParentId);
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get the asset's image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/default-asset.png');
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute(): string
    {
        return 'RM ' . number_format($this->purchase_price, 2);
    }

    /**
     * Get formatted current value
     */
    public function getFormattedCurrentValueAttribute(): string
    {
        return 'RM ' . number_format($this->current_value, 2);
    }

    /**
     * Get asset age in days
     */
    public function getAgeInDaysAttribute(): int
    {
        if (!$this->purchase_date) {
            return 0;
        }
        return $this->purchase_date->diffInDays(now());
    }

    /**
     * Get asset age in years
     */
    public function getAgeInYearsAttribute(): float
    {
        return round($this->age_in_days / 365, 1);
    }

    /**
     * Check if warranty is still valid
     */
    public function getIsUnderWarrantyAttribute(): bool
    {
        return $this->warranty_date && $this->warranty_date->isFuture();
    }

    /**
     * Get days until warranty expires
     */
    public function getWarrantyDaysRemainingAttribute(): int
    {
        if (!$this->warranty_date || $this->warranty_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->warranty_date);
    }

    /**
     * Check if maintenance is due
     */
    public function getIsMaintenanceDueAttribute(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'badge-success',
            self::STATUS_IN_USE => 'badge-primary',
            self::STATUS_MAINTENANCE => 'badge-warning',
            self::STATUS_RETIRED => 'badge-secondary',
            self::STATUS_DISPOSED => 'badge-dark',
            default => 'badge-light',
        };
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrlAttribute(): string
    {
        if ($this->qr_code) {
            return asset('storage/qr_codes/' . $this->qr_code);
        }
        return '';
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Assign this asset to a user
     */
    public function assignTo($userId, $notes = null): bool
    {
        $this->assigned_to_user = $userId;
        $this->status = self::STATUS_IN_USE;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Assigned: " . $notes;
        }

        // Log the movement
        $this->logMovement('assignment', "Assigned to user ID: {$userId}", $notes);

        return $this->save();
    }

    /**
     * Return this asset (make it available)
     */
    public function returnAsset($notes = null): bool
    {
        $previousAssignee = $this->assigned_to_user;
        $this->assigned_to_user = null;
        $this->status = self::STATUS_AVAILABLE;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Returned: " . $notes;
        }

        // Log the movement
        $this->logMovement('return', "Returned from user ID: {$previousAssignee}", $notes);

        return $this->save();
    }

    /**
     * Mark asset for maintenance
     */
    public function markForMaintenance($notes = null): bool
    {
        $this->status = self::STATUS_MAINTENANCE;
        $this->last_maintenance_date = now();
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Maintenance: " . $notes;
        }

        // Calculate next maintenance date if interval is set
        if ($this->maintenance_interval) {
            $this->next_maintenance_date = now()->addDays($this->maintenance_interval);
        }

        // Log maintenance record
        $this->maintenanceRecords()->create([
            'maintenance_type' => 'scheduled',
            'description' => $notes ?? 'Routine maintenance',
            'scheduled_date' => now(),
            'status' => 'in_progress',
            'performed_by' => session('id'),
        ]);

        return $this->save();
    }

    /**
     * Complete maintenance
     */
    public function completeMaintenance($notes = null, $cost = null): bool
    {
        $this->status = self::STATUS_AVAILABLE;
        $this->last_maintenance_date = now();
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Maintenance completed: " . $notes;
        }

        // Update the latest maintenance record
        $latestMaintenance = $this->maintenanceRecords()
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($latestMaintenance) {
            $latestMaintenance->update([
                'completed_date' => now(),
                'status' => 'completed',
                'notes' => $notes,
                'cost' => $cost,
            ]);
        }

        return $this->save();
    }

    /**
     * Move asset to different location
     */
    public function moveTo($locationId, $notes = null): bool
    {
        $previousLocation = $this->location_id;
        $this->location_id = $locationId;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Moved: " . $notes;
        }

        // Log the movement
        $this->logMovement('relocation', "Moved from location {$previousLocation} to {$locationId}", $notes);

        return $this->save();
    }

    /**
     * Retire this asset
     */
    public function retire($reason = null): bool
    {
        $this->status = self::STATUS_RETIRED;
        $this->assigned_to_user = null;
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Retired: " . $reason;
        }

        // Log the retirement
        $this->logMovement('retirement', 'Asset retired', $reason);

        return $this->save();
    }

    /**
     * Update current value (depreciation)
     */
    public function updateCurrentValue(): bool
    {
        if ($this->assetParent && $this->purchase_price && $this->purchase_date) {
            $this->current_value = $this->assetParent->calculateDepreciatedValue(
                $this->purchase_price,
                $this->purchase_date
            );
            return $this->save();
        }
        return false;
    }

    /**
     * Log asset movement
     */
    private function logMovement($type, $description, $notes = null): void
    {
        $this->movements()->create([
            'movement_type' => $type,
            'description' => $description,
            'from_location_id' => $this->getOriginal('location_id'),
            'to_location_id' => $this->location_id,
            'from_user_id' => $this->getOriginal('assigned_to_user'),
            'to_user_id' => $this->assigned_to_user,
            'performed_by' => session('id'),
            'movement_date' => now(),
            'notes' => $notes,
        ]);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Generate unique asset code
     */
    public static function generateAssetCode($prefix = 'AST'): string
    {
        do {
            $code = $prefix . '-' . strtoupper(uniqid());
        } while (self::where('asset_tag', $code)->exists());

        return $code;
    }

    /**
     * Get assets requiring attention
     */
    public static function getRequiringAttention(): array
    {
        return [
            'maintenance_due' => self::needingMaintenance()->with(['assetParent', 'centre'])->get(),
            'warranty_expiring' => self::whereNotNull('warranty_date')
                ->where('warranty_date', '<=', now()->addDays(30))
                ->where('warranty_date', '>', now())
                ->with(['assetParent', 'centre'])
                ->get(),
            'unassigned_valuable' => self::available()
                ->where('purchase_price', '>', 1000)
                ->whereNull('assigned_to_user')
                ->with(['assetParent', 'centre'])
                ->get(),
        ];
    }

    /**
     * Get statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'available' => self::available()->count(),
            'in_use' => self::inUse()->count(),
            'maintenance' => self::status(self::STATUS_MAINTENANCE)->count(),
            'retired' => self::status(self::STATUS_RETIRED)->count(),
            'total_value' => self::sum('current_value'),
            'maintenance_due' => self::needingMaintenance()->count(),
            'warranty_expiring' => self::whereNotNull('warranty_date')
                ->where('warranty_date', '<=', now()->addDays(30))
                ->where('warranty_date', '>', now())
                ->count(),
        ];
    }
}
