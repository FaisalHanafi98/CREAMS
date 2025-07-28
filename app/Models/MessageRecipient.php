<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageRecipient extends Model
{
    use HasFactory;

    protected $table = 'message_recipients';

    protected $fillable = [
        'message_id',
        'recipient_type',
        'recipient_id',
        'recipient_type_specific',
        'is_read',
        'read_at',
        'delivered_at',
        'is_deleted',
        'deleted_at',
        'delivery_metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'deleted_at' => 'datetime',
        'delivery_metadata' => 'array'
    ];

    /**
     * Get the message that this recipient belongs to
     */
    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    /**
     * Get the recipient model (polymorphic relationship)
     */
    public function recipient()
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for non-deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope for delivered messages
     */
    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered($metadata = null)
    {
        $this->update([
            'delivered_at' => now(),
            'delivery_metadata' => $metadata
        ]);
    }

    /**
     * Soft delete the message for this recipient
     */
    public function softDelete()
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now()
        ]);
    }
}