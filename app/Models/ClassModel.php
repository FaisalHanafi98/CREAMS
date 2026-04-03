<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'course_id',
        'teacher_id',
        'centre_id',
        'schedule',
        'location',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'schedule' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Get the course that owns the class
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the teacher that owns the class
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Get the centre that owns the class
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    /**
     * Get the trainees for the class
     */
    public function trainees()
    {
        return $this->belongsToMany(Trainee::class, 'class_trainee');
    }

    /**
     * Get the attendance records for the class
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}