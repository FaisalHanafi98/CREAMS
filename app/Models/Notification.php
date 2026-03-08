<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('notifiable_type', 'App\\Models\\User')
                     ->where('notifiable_id', $userId);
    }

    public function getReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getTitleAttribute(): string
    {
        return $this->data['title'] ?? 'Notification';
    }

    public function getContentAttribute(): string
    {
        return $this->data['message'] ?? $this->data['content'] ?? '';
    }

    private function getShortType(): string
    {
        $parts = explode('\\', $this->getAttribute('type') ?? '');
        return strtolower(end($parts));
    }

    public function getIconAttribute(): string
    {
        $t = $this->getShortType();

        if (str_contains($t, 'message')) return 'fas fa-envelope';
        if (str_contains($t, 'activit')) return 'fas fa-calendar-alt';
        if (str_contains($t, 'trainee')) return 'fas fa-user-graduate';
        if (str_contains($t, 'asset')) return 'fas fa-boxes';
        if (str_contains($t, 'system')) return 'fas fa-cog';

        return 'fas fa-bell';
    }

    public function getColorAttribute(): string
    {
        $t = $this->getShortType();

        if (str_contains($t, 'message')) return 'primary';
        if (str_contains($t, 'activit')) return 'success';
        if (str_contains($t, 'trainee')) return 'info';
        if (str_contains($t, 'asset')) return 'warning';
        if (str_contains($t, 'system')) return 'danger';

        return 'secondary';
    }
}