<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_id';
    
    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'course_type',
        'teacher_id',
        'participant_id',
        'course_day',
        'start_time',
        'end_time',
        'location_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the teacher associated with this course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    /**
     * Get the participant (trainee) associated with this course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participant()
    {
        return $this->belongsTo(Trainees::class, 'participant_id');
    }

    /**
     * Get the location (centre) associated with this course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Centres::class, 'location_id');
    }
    
    /**
     * Get courses by course type.
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByType($type)
    {
        return self::where('course_type', $type)->get();
    }
    
    /**
     * Get courses by teacher ID.
     *
     * @param string $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByTeacher($teacherId)
    {
        return self::where('teacher_id', $teacherId)->get();
    }
    
    /**
     * Get courses by location (centre) ID.
     *
     * @param string $locationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByLocation($locationId)
    {
        return self::where('location_id', $locationId)->get();
    }
}