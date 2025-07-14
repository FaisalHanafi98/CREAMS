<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'letter_date',
        'recipient_name',
        'recipient_address',
        'subject',
        'content',
        'template_id',
        'generated_by',
        'generated_by_name',
        'generated_by_position',
        'pdf_path',
        'metadata'
    ];

    protected $casts = [
        'letter_date' => 'date',
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($letter) {
            // Generate reference number if not provided
            if (empty($letter->reference_number)) {
                $letter->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /**
     * Generate a unique reference number for the letter.
     */
    public static function generateReferenceNumber()
    {
        $prefix = 'LTR';
        $year = date('Y');
        $month = date('m');
        
        // Get the last letter number for this month
        $lastLetter = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)
                          ->latest()
                          ->first();
        
        $sequence = $lastLetter ? (intval(substr($lastLetter->reference_number, -4)) + 1) : 1;
        
        return sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Get the template used for this letter.
     */
    public function template()
    {
        return $this->belongsTo(LetterTemplate::class);
    }

    /**
     * Get the user who generated this letter.
     */
    public function generator()
    {
        return $this->belongsTo(Users::class, 'generated_by');
    }

    /**
     * Get the PDF URL if it exists.
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_path && Storage::exists('public/' . $this->pdf_path)) {
            return Storage::url($this->pdf_path);
        }
        return null;
    }

    /**
     * Check if the PDF file exists.
     */
    public function hasPdf()
    {
        return $this->pdf_path && Storage::exists('public/' . $this->pdf_path);
    }

    /**
     * Get formatted letter date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->letter_date->format('d M Y');
    }

    /**
     * Get truncated subject for display.
     */
    public function getTruncatedSubjectAttribute()
    {
        return \Str::limit($this->subject, 50);
    }

    /**
     * Get truncated content for display.
     */
    public function getTruncatedContentAttribute()
    {
        return \Str::limit(strip_tags($this->content), 100);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('letter_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by generator.
     */
    public function scopeByGenerator($query, $userId)
    {
        return $query->where('generated_by', $userId);
    }

    /**
     * Scope to search by recipient or subject.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('recipient_name', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%")
              ->orWhere('reference_number', 'like', "%{$term}%");
        });
    }
}