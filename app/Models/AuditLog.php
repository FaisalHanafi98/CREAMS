<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_role',
        'action',
        'table',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    
    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    
    /**
     * Get formatted changes as HTML.
     */
    public function getFormattedChangesAttribute()
    {
        $html = '';
        
        if ($this->action === 'create') {
            $html .= '<strong>Created new ' . rtrim($this->table, 's') . '</strong><br>';
            foreach ($this->new_values as $key => $value) {
                $html .= '<span class="text-muted">' . ucfirst(str_replace('_', ' ', $key)) . ':</span> ' . $value . '<br>';
            }
        } 
        else if ($this->action === 'update') {
            $html .= '<strong>Updated ' . rtrim($this->table, 's') . ' information</strong><br>';
            foreach ($this->new_values as $key => $value) {
                $old = $this->old_values[$key] ?? 'empty';
                $html .= '<span class="text-muted">' . ucfirst(str_replace('_', ' ', $key)) . ':</span> ' . 
                         '<span class="text-danger">' . $old . '</span> → ' .
                         '<span class="text-success">' . $value . '</span><br>';
            }
        } 
        else if ($this->action === 'delete') {
            $html .= '<strong>Deleted ' . rtrim($this->table, 's') . '</strong><br>';
            $html .= 'User information: <br>';
            foreach ($this->new_values as $key => $value) {
                $html .= '<span class="text-muted">' . ucfirst(str_replace('_', ' ', $key)) . ':</span> ' . $value . '<br>';
            }
        } 
        else if ($this->action === 'password_reset') {
            $html .= '<strong>Password reset</strong><br>';
        } 
        else if ($this->action === 'status_change') {
            $html .= '<strong>Status changed</strong><br>';
            $newStatus = $this->new_values['status'] ?? '';
            $oldStatus = $this->old_values['status'] ?? '';
            $html .= '<span class="text-muted">Status:</span> ' . 
                     '<span class="text-danger">' . $oldStatus . '</span> → ' .
                     '<span class="text-success">' . $newStatus . '</span><br>';
        }
        
        return $html;
    }
    
    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimestampAttribute()
    {
        return $this->created_at->format('M d, Y H:i:s');
    }
}