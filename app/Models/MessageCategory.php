<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageCategory extends Model
{
    use HasFactory;

    protected $table = 'message_categories';

    protected $fillable = [
        'category_name',
        'category_description',
        'category_color',
        'category_icon',
        'is_system',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get messages in this category
     */
    public function messages()
    {
        return $this->hasMany(Messages::class, 'message_category_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for system categories
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user-created categories
     */
    public function scopeUserCreated($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('category_name');
    }

    /**
     * Get category display attributes
     */
    public function getDisplayAttributesAttribute()
    {
        return [
            'name' => $this->category_name,
            'color' => $this->category_color ?? '#007bff',
            'icon' => $this->category_icon ?? 'fa-folder',
            'style' => 'background-color: ' . ($this->category_color ?? '#007bff') . '; color: white;'
        ];
    }

    /**
     * Get message count for this category
     */
    public function getMessageCountAttribute()
    {
        return $this->messages()->count();
    }

    /**
     * Get unread message count for this category
     */
    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->whereHas('recipients', function($q) {
                $q->where('is_read', false);
            })
            ->count();
    }
}