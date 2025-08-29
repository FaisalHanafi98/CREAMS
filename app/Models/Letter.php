<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_id',
        'letter_name',
        'letter_date',
        'letter_title',
        'letter_description',
        'letter_subject',
        'letter_content',
        'letter_type',
        'recipient_id',
        'recipient_name',
        'recipient_email',
        'recipient_address',
        'recipient_type',
        'recipient_organization',
        'purpose',
        'priority_level',
        'template_id',
        'letter_status',
        'is_archived',
        'archived_at',
        'archived_by',
        'sent_date',
        'sent_at',
        'is_sent',
        'delivery_method',
        'delivery_notes',
        'letter_file_path',
        'letter_data',
        'generation_metadata',
        'generated_file_type',
        'generated_at',
        'file_size_bytes',
        'pdf_filename',
        'pdf_path',
        'pdf_file_size',
        'centre_id',
        'created_by',
        'generated_by'
    ];

    protected $casts = [
        'letter_date' => 'date',
        'sent_date' => 'date',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
        'archived_at' => 'datetime',
        'is_archived' => 'boolean',
        'is_sent' => 'boolean',
        'letter_data' => 'array',
        'generation_metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($letter) {
            // Generate letter ID if not provided
            if (empty($letter->letter_id)) {
                $letter->letter_id = self::generateReferenceNumber();
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
        $maxAttempts = 100;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // Get the highest sequence number for this month
                $lastSequence = self::where('letter_id', 'like', "{$prefix}/{$year}/{$month}/%")
                    ->orderByRaw('CAST(SUBSTRING_INDEX(letter_id, "/", -1) AS UNSIGNED) DESC')
                    ->value('letter_id');
                
                if ($lastSequence) {
                    $sequence = intval(substr($lastSequence, -4)) + 1;
                } else {
                    $sequence = 1;
                }
                
                $reference = sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $sequence);
                
                // Check if this reference already exists
                if (!self::where('letter_id', $reference)->exists()) {
                    return $reference;
                }
                
                // If it exists, try with next sequence number
                $sequence++;
                
            } catch (\Exception $e) {
                // If database query fails, use timestamp-based sequence
                $sequence = intval(substr(time(), -4));
                $reference = sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $sequence);
                
                if (!self::where('letter_id', $reference)->exists()) {
                    return $reference;
                }
            }
        }
        
        // Fallback: use timestamp + random if all attempts fail
        $timestamp = time();
        $random = rand(10, 99);
        return sprintf('%s/%s/%s/%s%s', $prefix, $year, $month, substr($timestamp, -2), $random);
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
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who created this letter.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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
              ->orWhere('letter_id', 'like', "%{$term}%")
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