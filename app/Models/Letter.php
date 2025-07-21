<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_reference',
        'letter_date',
        'letter_subject',
        'letter_content',
        'letter_type',
        'recipient_id',
        'recipient_type',
        'template_id',
        'letter_status',
        'sent_date',
        'letter_file_path',
        'letter_data',
        'created_by'
    ];

    protected $casts = [
        'letter_date' => 'date',
        'sent_date' => 'date',
        'letter_data' => 'array'    // Updated to match database
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($letter) {
            // Generate reference number if not provided
            if (empty($letter->letter_reference)) {
                $letter->letter_reference = self::generateReferenceNumber();
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
        
        try {
            // Get the last letter number for this month
            $lastLetter = self::whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->latest()
                              ->first();
            
            $sequence = $lastLetter ? (intval(substr($lastLetter->letter_reference, -4)) + 1) : 1;
            
        } catch (\Exception $e) {
            // If database query fails, use random sequence
            $sequence = rand(1000, 9999);
        }
        
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
        return $this->belongsTo(Users::class, 'created_by');
    }
    
    /**
     * Get the user who created this letter.
     */
    public function createdBy()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the PDF URL if it exists.
     */
    public function getPdfUrlAttribute()
    {
        if ($this->letter_file_path && Storage::exists('public/' . $this->letter_file_path)) {
            return Storage::url($this->letter_file_path);
        }
        return null;
    }

    /**
     * Check if the PDF file exists.
     */
    public function hasPdf()
    {
        if (!$this->letter_file_path) {
            return false;
        }
        
        // Check both public directory and storage
        $publicPath = public_path('letters/' . basename($this->letter_file_path));
        $storagePath = Storage::exists('public/' . $this->letter_file_path);
        
        return file_exists($publicPath) || $storagePath;
    }
    
    /**
     * Get the actual PDF file path
     */
    public function getPdfPath()
    {
        if (!$this->letter_file_path) {
            return null;
        }
        
        $publicPath = public_path('letters/' . basename($this->letter_file_path));
        if (file_exists($publicPath)) {
            return $publicPath;
        }
        
        if (Storage::exists('public/' . $this->letter_file_path)) {
            return Storage::path('public/' . $this->letter_file_path);
        }
        
        return null;
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
        return \Str::limit($this->letter_subject, 50);
    }

    /**
     * Get truncated content for display.
     */
    public function getTruncatedContentAttribute()
    {
        return \Str::limit(strip_tags($this->letter_content), 100);
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
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to search by recipient or subject.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('letter_subject', 'like', "%{$term}%")
              ->orWhere('letter_reference', 'like', "%{$term}%")
              ->orWhere('letter_content', 'like', "%{$term}%")
              ->orWhereJsonContains('letter_data->recipient_name', $term)
              ->orWhereJsonContains('letter_data->generated_by_name', $term);
        });
    }
    
    /**
     * Get recipient name from letter data
     */
    public function getRecipientNameAttribute()
    {
        return $this->letter_data['recipient_name'] ?? 'Unknown';
    }
    
    /**
     * Get generated by name from letter data
     */
    public function getGeneratedByNameAttribute()
    {
        return $this->letter_data['generated_by_name'] ?? 'Unknown';
    }
    
    /**
     * Get generated by position from letter data
     */
    public function getGeneratedByPositionAttribute()
    {
        return $this->letter_data['generated_by_position'] ?? 'Unknown';
    }
    
    /**
     * Get recipient address from letter data
     */
    public function getRecipientAddressAttribute()
    {
        return $this->letter_data['recipient_address'] ?? '';
    }
}