<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CentreAuditLog extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

    protected $table = 'centre_audit_logs';

    protected $fillable = [
        'centre_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'notes',
        'ip_address'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the centre that this audit log belongs to
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action description
     */
    public function getActionDescriptionAttribute()
    {
        $descriptions = [
            'created' => 'Centre was created',
            'updated' => 'Centre details were updated',
            'activated' => 'Centre was activated',
            'deactivated' => 'Centre was deactivated',
            'deleted' => 'Centre was deleted'
        ];

        return $descriptions[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get changes summary for display
     */
    public function getChangesSummaryAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return null;
        }

        $changes = [];
        $oldValues = $this->old_values;
        $newValues = $this->new_values;

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }
}