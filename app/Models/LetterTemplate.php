<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'template_content',
        'template_type',
        'template_variables',
        'template_description',
        'header_image_path',
        'footer_image_path',
        'header_text',
        'footer_text',
        'centre_id',
        'usage_count',
        'last_used_at',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'template_variables' => 'array',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer'
    ];

    /**
     * Get the user who created this template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * Get letters that used this template.
     */
    public function letters()
    {
        return $this->hasMany(Letter::class, 'template_id');
    }

    /**
     * Get the currently active template.
     */
    public static function getActive()
    {
        return self::where('is_active', true)->latest()->first();
    }



    /**
     * Get header content from template variables.
     */
    public function getHeaderContentAttribute()
    {
        $variables = $this->template_variables;
        return is_array($variables) ? ($variables['header_content'] ?? '') : '';
    }

    /**
     * Get footer content from template variables.
     */
    public function getFooterContentAttribute()
    {
        $variables = $this->template_variables;
        return is_array($variables) ? ($variables['footer_content'] ?? '') : '';
    }

    /**
     * Get header image path from template variables.
     */
    public function getHeaderImageAttribute()
    {
        $variables = $this->template_variables;
        return is_array($variables) ? ($variables['header_image'] ?? null) : null;
    }

    /**
     * Get footer image path from template variables.
     */
    public function getFooterImageAttribute()
    {
        $variables = $this->template_variables;
        return is_array($variables) ? ($variables['footer_image'] ?? null) : null;
    }

    /**
     * Get header image URL for display.
     */
    public function getHeaderImageUrlAttribute()
    {
        if ($this->header_image) {
            return asset('storage/' . $this->header_image);
        }
        return null;
    }

    /**
     * Get footer image URL for display.
     */
    public function getFooterImageUrlAttribute()
    {
        if ($this->footer_image) {
            return asset('storage/' . $this->footer_image);
        }
        return null;
    }

    /**
     * Deactivate all templates and activate this one.
     */
    public function activate()
    {
        // Deactivate all other templates
        self::where('is_active', true)->update(['is_active' => false]);
        
        // Activate this template
        $this->is_active = true;
        $this->save();
    }
}