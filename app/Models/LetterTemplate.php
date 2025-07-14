<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'header_image',
        'footer_image',
        'header_content',
        'footer_content',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the user who last updated this template.
     */
    public function updater()
    {
        return $this->belongsTo(Users::class, 'updated_by');
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
     * Get the header image URL.
     */
    public function getHeaderImageUrlAttribute()
    {
        if ($this->header_image) {
            return asset('storage/' . $this->header_image);
        }
        return null;
    }

    /**
     * Get the footer image URL.
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