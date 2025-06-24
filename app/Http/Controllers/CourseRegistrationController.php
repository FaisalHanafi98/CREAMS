<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Courses;
use App\Models\Users;
use App\Models\Trainees;
use App\Models\Centres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CourseRegistrationController extends Controller
{
    /**
     * Available course types
     * 
     * @var array
     */
    protected $courseTypes = [
        'Occupational Therapy',
        'Reading',
        'Speech Therapy',
        'Quranic Class',
        'Independent Living'
    ];

    /**
     * Eligible trainee conditions
     * 
     * @var array
     */
    protected $eligibleConditions = [
        'Cerebral Palsy',
        'Autism Spectrum Disorder (ASD)',
        'Down Syndrome',
        'Hearing Impairment',
        'Visual Impairment',
        'Intellectual Disabilities'
    ];

    /**
     * Display the course registration form
     * 
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        try {
            // Get all courses
            $courses = Courses::with(['teacher', 'participant', 'location'])->get();

            // Get eligible teachers based on activity
            $teachers = Users::where(function($query) {
                $query->whereIn('user_activity_1', $this->courseTypes)
                      ->orWhereIn('user_activity_2', $this->courseTypes);
            })->get();
            
            // Format teacher data for dropdown
            $teachersList = $teachers->mapWithKeys(function($teacher) {
                $name = $teacher->users_name ?? $teacher->user_first_name . ' ' . $teacher->user_last_name;
                return [$teacher->id => $name];
            });

            // Get eligible trainees
            $trainees = Trainees::whereIn('trainee_condition', $this->eligibleConditions)->get();
            
            // Format trainee data for dropdown
            $traineesList = $trainees->mapWithKeys(function($trainee) {
                $name = $trainee->trainee_name ?? $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name;
                return [$trainee->id => $name];
            });

            // Get unique teacher activities
            $teachersActivities = $teachers->pluck('user_activity_1')
                ->merge($teachers->pluck('user_activity_2'))
                ->filter()
                ->unique()
                ->values();

            // Get unique trainee conditions
            $traineeConditions = $trainees->pluck('trainee_condition')
                ->filter()
                ->unique()
                ->values();

            // Get centres
            $centres = Centres::pluck('centre_name', 'centre_id');

            return view('courseregistration', [
                'courses' => $courses,
                'teachers' => $teachersList,
                'trainees' => $traineesList,
                'teachersActivities' => $teachersActivities,
                'traineeConditions' => $traineeConditions,
                'centres' => $centres,
                'courseTypes' => $this->courseTypes,
                'weekdays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading course registration form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Unable to load registration form. Please try again later.');
        }
    }

    /**
     * Process the course registration form submission
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitRegistrationForm(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'course_id' => 'required|string',
                'course_type' => ['required', Rule::in($this->courseTypes)],
                'teacher_id' => 'required|exists:users,id',
                'participant_id' => 'required|exists:trainees,id',
                'course_day' => 'required|string',
                'start_time' => 'required|date_format:H:i',
                'end_time' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->start_time >= $value) {
                            $fail('The end time must be after the start time.');
                        }
                    },
                ],
                'location_id' => 'nullable|exists:centres,centre_id',
            ]);

            // Check if the teacher is eligible to teach the selected course type
            $teacher = Users::findOrFail($validatedData['teacher_id']);
            if ($teacher->user_activity_1 !== $validatedData['course_type'] && $teacher->user_activity_2 !== $validatedData['course_type']) {
                return redirect()->back()->withInput()->with('error', 'Selected teacher is not eligible to teach this course type.');
            }

            // Check if the participant is eligible for the course based on their condition
            $participant = Trainees::findOrFail($validatedData['participant_id']);
            if (!in_array($participant->trainee_condition, $this->eligibleConditions)) {
                return redirect()->back()->withInput()->with('error', 'Selected participant is not eligible for this course.');
            }

            // Check for scheduling conflicts
            $conflicts = $this->checkSchedulingConflicts(
                $validatedData['participant_id'],
                $validatedData['teacher_id'],
                $validatedData['course_day'],
                $validatedData['start_time'],
                $validatedData['end_time']
            );

            if ($conflicts) {
                return redirect()->back()->withInput()->with('error', $conflicts);
            }

            // Begin transaction
            DB::beginTransaction();
            
            // Create the course
            $course = Courses::create($validatedData);

            // Commit transaction
            DB::commit();

            Log::info('Course registration successful', [
                'course_id' => $course->course_id,
                'teacher' => $teacher->users_name ?? $teacher->user_first_name . ' ' . $teacher->user_last_name,
                'participant' => $participant->trainee_name ?? $participant->trainee_first_name . ' ' . $participant->trainee_last_name
            ]);

            return redirect()->route('registration.success')->with('success', 'Course registration successful.');
        } catch (\Exception $e) {
            // Rollback transaction if active
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::error('Error occurred during course registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token'])
            ]);

            return redirect()->back()->withInput()->with('error', 'An error occurred during course registration: ' . $e->getMessage());
        }
    }

    /**
     * Check for scheduling conflicts
     * 
     * @param int $participantId
     * @param int $teacherId
     * @param string $courseDay
     * @param string $startTime
     * @param string $endTime
     * @return string|null Error message or null if no conflicts
     */
    protected function checkSchedulingConflicts($participantId, $teacherId, $courseDay, $startTime, $endTime)
    {
        // Check if the course day is available for the participant and teacher
        $participantDayCount = Courses::where('participant_id', $participantId)
            ->where('course_day', $courseDay)
            ->count();
            
        $teacherDayCount = Courses::where('teacher_id', $teacherId)
            ->where('course_day', $courseDay)
            ->count();
            
        if ($participantDayCount >= 3) {
            return 'Participant has reached the maximum number of activities for the selected day.';
        }
        
        if ($teacherDayCount >= 3) {
            return 'Teacher has reached the maximum number of activities for the selected day.';
        }

        // Check for time overlaps for the participant
        $participantOverlap = Courses::where('participant_id', $participantId)
            ->where('course_day', $courseDay)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->count();
            
        if ($participantOverlap > 0) {
            return 'Participant already has an activity that overlaps with the selected time.';
        }
        
        // Check for time overlaps for the teacher
        $teacherOverlap = Courses::where('teacher_id', $teacherId)
            ->where('course_day', $courseDay)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->count();
            
        if ($teacherOverlap > 0) {
            return 'Teacher already has an activity that overlaps with the selected time.';
        }
        
        return null;
    }
}