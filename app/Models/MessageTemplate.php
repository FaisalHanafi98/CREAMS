<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $table = 'message_templates';

    protected $fillable = [
        'template_name',
        'template_subject',
        'template_body',
        'template_type',
        'template_variables',
        'is_active',
        'created_by',
        'centre_id'
    ];

    protected $casts = [
        'template_variables' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the centre this template belongs to
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for templates by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * Scope for centre-specific templates
     */
    public function scopeForCentre($query, $centreId)
    {
        return $query->where(function($q) use ($centreId) {
            $q->where('centre_id', $centreId)
              ->orWhereNull('centre_id'); // Global templates
        });
    }

    /**
     * Process template with variables
     */
    public function processTemplate($variables = [])
    {
        $subject = $this->template_subject;
        $body = $this->template_body;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables()
    {
        return $this->template_variables ?? [];
    }

    /**
     * Validate template variables
     */
    public function validateVariables($variables)
    {
        $availableVars = $this->getAvailableVariables();
        $missingVars = [];

        foreach ($availableVars as $var) {
            if (isset($var['required']) && $var['required'] && !isset($variables[$var['name']])) {
                $missingVars[] = $var['name'];
            }
        }

        return [
            'valid' => empty($missingVars),
            'missing' => $missingVars
        ];
    }
}