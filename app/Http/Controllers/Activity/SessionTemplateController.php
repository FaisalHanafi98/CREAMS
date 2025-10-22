<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivitySession;
use App\Models\ActivityScheduleTemplate;
use App\Models\ActivityTemplateApplication;
use App\Models\SessionEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SessionTemplateController extends Controller
{
    /**
     * Get session template data for modification
     */
    public function getTemplateData($sessionId)
    {
        try {
            $session = ActivitySession::with(['activity', 'teacher'])->findOrFail($sessionId);
            
            // Check permissions
            $userRole = session('role');
            $userId = session('id');
            
            if (!in_array($userRole, ['admin', 'supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $templateData = [
                'session_id' => $session->id,
                'session_date' => $session->session_date,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'venue' => $session->venue,
                'room_number' => $session->room_number,
                'session_description' => $session->session_description,
                'activity_name' => $session->activity->activity_name,
                'teacher_name' => $session->teacher->name ?? 'Unassigned',
                'enrolled_count' => $session->sessionEnrollments->count()
            ];

            return response()->json([
                'success' => true,
                'template' => $templateData
            ]);

        } catch (\Exception $e) {
            Log::error('Template data fetch error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading template data'
            ], 500);
        }
    }

    /**
     * Preview template modifications
     */
    public function previewTemplateChanges(Request $request, $sessionId)
    {
        // Get centre_id from session for holiday checking
        $centreId = session('centre_id');

        $validator = Validator::make($request->all(), [
            'session_date' => [
                'required',
                'date',
                new \App\Rules\NoWeekendOrHolidayRule($centreId)
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'venue' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'apply_scope' => 'required|in:this,future,all',
            'preserve_enrollments' => 'boolean',
            'preserve_learning_outcomes' => 'boolean',
            'notify_participants' => 'boolean',
            'modification_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $session = ActivitySession::with(['activity', 'sessionEnrollments'])->findOrFail($sessionId);
            
            // Calculate affected sessions based on scope
            $affectedSessions = $this->getAffectedSessions($session, $request->apply_scope);
            
            // Check for conflicts
            $conflicts = $this->checkTemplateConflicts($affectedSessions, $request->all());
            
            // Generate preview data
            $preview = [
                'modifications' => [
                    'session_date' => $request->session_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'venue' => $request->venue,
                    'room_number' => $request->room_number
                ],
                'scope' => [
                    'apply_to' => $request->apply_scope,
                    'affected_sessions' => $affectedSessions->count(),
                    'total_enrollments' => $affectedSessions->sum(function($s) { return $s->sessionEnrollments->count(); })
                ],
                'preservation' => [
                    'enrollments' => $request->preserve_enrollments,
                    'learning_outcomes' => $request->preserve_learning_outcomes,
                    'notifications' => $request->notify_participants
                ],
                'conflicts' => $conflicts,
                'estimated_duration' => $this->calculateEstimatedDuration($affectedSessions->count())
            ];

            return response()->json([
                'success' => true,
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            Log::error('Template preview error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview'
            ], 500);
        }
    }

    /**
     * Apply template modifications
     */
    public function applyTemplateModifications(Request $request, $sessionId)
    {
        // Get centre_id from session for holiday checking
        $centreId = session('centre_id');

        $validator = Validator::make($request->all(), [
            'session_date' => [
                'required',
                'date',
                new \App\Rules\NoWeekendOrHolidayRule($centreId)
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'venue' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'apply_scope' => 'required|in:this,future,all',
            'preserve_enrollments' => 'boolean',
            'preserve_learning_outcomes' => 'boolean',
            'notify_participants' => 'boolean',
            'modification_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $session = ActivitySession::with(['activity', 'sessionEnrollments'])->findOrFail($sessionId);
            
            // Get affected sessions
            $affectedSessions = $this->getAffectedSessions($session, $request->apply_scope);
            
            // Check for conflicts
            $conflicts = $this->checkTemplateConflicts($affectedSessions, $request->all());
            
            if (!empty($conflicts['critical'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Critical conflicts detected',
                    'conflicts' => $conflicts
                ], 422);
            }

            $modifiedCount = 0;
            $preservationData = [];

            foreach ($affectedSessions as $affectedSession) {
                // Preserve data if requested
                if ($request->preserve_enrollments) {
                    $preservationData[$affectedSession->id]['enrollments'] = $affectedSession->sessionEnrollments->toArray();
                }
                
                if ($request->preserve_learning_outcomes) {
                    $preservationData[$affectedSession->id]['learning_outcomes'] = $affectedSession->learning_outcomes_data;
                }

                // Apply modifications
                $affectedSession->update([
                    'session_date' => $request->session_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'venue' => $request->venue,
                    'room_number' => $request->room_number,
                    'last_modified_by' => session('id'),
                    'modification_notes' => $request->modification_notes
                ]);

                // Restore preserved data
                if ($request->preserve_learning_outcomes && isset($preservationData[$affectedSession->id]['learning_outcomes'])) {
                    $affectedSession->update([
                        'learning_outcomes_data' => $preservationData[$affectedSession->id]['learning_outcomes']
                    ]);
                }

                $modifiedCount++;
            }

            // Send notifications if requested
            if ($request->notify_participants) {
                $this->sendModificationNotifications($affectedSessions, $request->all());
            }

            // Log the modification
            $this->logTemplateModification($session, $request->all(), $modifiedCount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully modified {$modifiedCount} sessions",
                'data' => [
                    'modified_sessions' => $modifiedCount,
                    'scope' => $request->apply_scope,
                    'conflicts_resolved' => count($conflicts['warnings'] ?? [])
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template modification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error applying template modifications'
            ], 500);
        }
    }

    /**
     * Apply template to similar sessions
     */
    public function applyTemplateToSimilar($activityId)
    {
        try {
            DB::beginTransaction();

            $activity = \App\Models\Activity::with(['activitySessions'])->findOrFail($activityId);
            
            // Find a template session (most recent or most common pattern)
            $templateSession = $activity->activitySessions()
                ->orderBy('session_date', 'desc')
                ->first();

            if (!$templateSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'No template session found'
                ]);
            }

            $affectedSessions = $activity->activitySessions()
                ->where('id', '!=', $templateSession->id)
                ->where('session_date', '>=', now())
                ->get();

            $appliedCount = 0;

            foreach ($affectedSessions as $session) {
                $session->update([
                    'start_time' => $templateSession->start_time,
                    'end_time' => $templateSession->end_time,
                    'venue' => $templateSession->venue,
                    'room_number' => $templateSession->room_number,
                    'last_modified_by' => session('id')
                ]);
                $appliedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Template applied to {$appliedCount} sessions",
                'affected_sessions' => $appliedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Apply template to similar error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error applying template to similar sessions'
            ], 500);
        }
    }

    /**
     * Create template from session
     */
    public function createTemplateFromSession(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $session = ActivitySession::with(['activity'])->findOrFail($sessionId);
            
            // Calculate duration and pattern
            $startTime = Carbon::parse($session->start_time);
            $endTime = Carbon::parse($session->end_time);
            $durationMinutes = $startTime->diffInMinutes($endTime);

            // Create template
            $template = ActivityScheduleTemplate::create([
                'template_name' => $request->template_name,
                'description' => $request->description,
                'sessions_per_week' => 1, // Default, can be modified later
                'duration_weeks' => 4, // Default, can be modified later
                'session_length_minutes' => $durationMinutes,
                'days_of_week' => json_encode([Carbon::parse($session->session_date)->dayName]),
                'template_data' => json_encode([
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'venue' => $session->venue,
                    'room_number' => $session->room_number,
                    'learning_outcomes_data' => $session->learning_outcomes_data
                ]),
                'created_by' => session('id')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'template_id' => $template->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create template from session error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating template from session'
            ], 500);
        }
    }

    // Helper Methods

    private function getAffectedSessions($session, $scope)
    {
        $query = ActivitySession::where('activity_id', $session->activity_id);

        switch ($scope) {
            case 'this':
                $query->where('id', $session->id);
                break;
            case 'future':
                $query->where('session_date', '>=', $session->session_date);
                break;
            case 'all':
                // No additional constraints
                break;
        }

        return $query->with(['sessionEnrollments'])->get();
    }

    private function checkTemplateConflicts($sessions, $modificationData)
    {
        $conflicts = [
            'critical' => [],
            'warnings' => []
        ];

        foreach ($sessions as $session) {
            // Check for time conflicts with other sessions
            $timeConflicts = ActivitySession::where('session_date', $modificationData['session_date'])
                ->where('id', '!=', $session->id)
                ->where(function($query) use ($modificationData) {
                    $query->whereBetween('start_time', [$modificationData['start_time'], $modificationData['end_time']])
                          ->orWhereBetween('end_time', [$modificationData['start_time'], $modificationData['end_time']])
                          ->orWhere(function($q) use ($modificationData) {
                              $q->where('start_time', '<=', $modificationData['start_time'])
                                ->where('end_time', '>=', $modificationData['end_time']);
                          });
                })
                ->exists();

            if ($timeConflicts) {
                $conflicts['critical'][] = [
                    'session_id' => $session->id,
                    'type' => 'time_conflict',
                    'message' => 'Time conflict with existing sessions'
                ];
            }

            // Check for venue conflicts
            $venueConflicts = ActivitySession::where('session_date', $modificationData['session_date'])
                ->where('venue', $modificationData['venue'])
                ->where('room_number', $modificationData['room_number'])
                ->where('id', '!=', $session->id)
                ->where(function($query) use ($modificationData) {
                    $query->whereBetween('start_time', [$modificationData['start_time'], $modificationData['end_time']])
                          ->orWhereBetween('end_time', [$modificationData['start_time'], $modificationData['end_time']]);
                })
                ->exists();

            if ($venueConflicts) {
                $conflicts['warnings'][] = [
                    'session_id' => $session->id,
                    'type' => 'venue_conflict',
                    'message' => 'Venue booking conflict detected'
                ];
            }

            // Check if session has enrollments
            if ($session->sessionEnrollments->count() > 0) {
                $conflicts['warnings'][] = [
                    'session_id' => $session->id,
                    'type' => 'enrollment_impact',
                    'message' => 'Session has enrolled participants'
                ];
            }

            // NEW: Check for prerequisite violations
            $prerequisiteViolations = $this->checkPrerequisiteViolations($session, $modificationData);
            if (!empty($prerequisiteViolations)) {
                $conflicts['critical'] = array_merge($conflicts['critical'], $prerequisiteViolations);
            }
        }

        return $conflicts;
    }

    /**
     * Check for prerequisite violations when modifying sessions
     */
    private function checkPrerequisiteViolations($session, $modificationData)
    {
        $violations = [];

        // Load activity with prerequisites
        $activity = \App\Models\Activity::with(['prerequisites.prerequisiteActivity'])->find($session->activity_id);
        
        if (!$activity || !$activity->prerequisites->count()) {
            return $violations;
        }

        // Check each prerequisite
        foreach ($activity->prerequisites as $prerequisite) {
            $prerequisiteActivity = $prerequisite->prerequisiteActivity;
            
            if (!$prerequisiteActivity) continue;

            // Find prerequisite sessions that should be completed before this session
            $prerequisiteSessions = ActivitySession::where('activity_id', $prerequisiteActivity->id)
                ->where('session_date', '<', $modificationData['session_date'] ?? $session->session_date)
                ->with(['sessionEnrollments' => function($query) use ($session) {
                    // Check enrollments for the same trainees enrolled in current session
                    $traineeIds = $session->sessionEnrollments->pluck('trainee_id')->toArray();
                    $query->whereIn('trainee_id', $traineeIds);
                }])
                ->get();

            // Check if prerequisite requirements are met
            $sessionTrainees = $session->sessionEnrollments->pluck('trainee_id')->toArray();
            
            foreach ($sessionTrainees as $traineeId) {
                $completedPrerequisites = $prerequisiteSessions->filter(function($prereqSession) use ($traineeId) {
                    return $prereqSession->sessionEnrollments
                        ->where('trainee_id', $traineeId)
                        ->where('attendance_status', 'present')
                        ->count() > 0;
                })->count();

                $requiredPrerequisites = $prerequisite->minimum_sessions ?? 1;

                if ($completedPrerequisites < $requiredPrerequisites) {
                    $violations[] = [
                        'session_id' => $session->id,
                        'trainee_id' => $traineeId,
                        'type' => 'prerequisite_violation',
                        'message' => "Trainee hasn't completed required prerequisites for {$prerequisiteActivity->activity_name}",
                        'details' => [
                            'completed' => $completedPrerequisites,
                            'required' => $requiredPrerequisites,
                            'prerequisite_activity' => $prerequisiteActivity->activity_name
                        ]
                    ];
                }
            }
        }

        return $violations;
    }

    private function sendModificationNotifications($sessions, $modificationData)
    {
        // Implementation for sending notifications to affected participants
        // This would integrate with the notification system
        Log::info('Template modification notifications sent for ' . $sessions->count() . ' sessions');
    }

    private function logTemplateModification($session, $modificationData, $modifiedCount)
    {
        Log::info('Template modification applied', [
            'user_id' => session('id'),
            'session_id' => $session->id,
            'activity_id' => $session->activity_id,
            'scope' => $modificationData['apply_scope'],
            'modified_count' => $modifiedCount,
            'changes' => [
                'venue' => $modificationData['venue'],
                'time' => $modificationData['start_time'] . '-' . $modificationData['end_time']
            ]
        ]);
    }

    private function calculateEstimatedDuration($sessionCount)
    {
        // Estimate processing time based on number of sessions
        $baseTime = 2; // seconds
        $perSessionTime = 0.5; // seconds per session
        
        return $baseTime + ($sessionCount * $perSessionTime);
    }

    /**
     * Bulk reschedule sessions with conflict detection
     */
    public function bulkReschedule(Request $request)
    {
        // Get centre_id from session for holiday checking
        $centreId = session('centre_id');

        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'exists:activity_sessions,id',
            'new_date' => [
                'required',
                'date',
                'after:today',
                new \App\Rules\NoWeekendOrHolidayRule($centreId)
            ],
            'time_offset_hours' => 'nullable|integer|min:-12|max:12',
            'preserve_enrollments' => 'boolean',
            'preserve_learning_outcomes' => 'boolean',
            'notify_participants' => 'boolean',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $sessions = ActivitySession::with(['sessionEnrollments', 'activity'])
                ->whereIn('id', $request->session_ids)
                ->get();

            $rescheduledCount = 0;
            $conflicts = [];
            $preservationData = [];

            foreach ($sessions as $session) {
                // Calculate new time if offset provided
                $newStartTime = $session->start_time;
                $newEndTime = $session->end_time;
                
                if ($request->time_offset_hours) {
                    $startTime = Carbon::parse($session->start_time)->addHours($request->time_offset_hours);
                    $endTime = Carbon::parse($session->end_time)->addHours($request->time_offset_hours);
                    $newStartTime = $startTime->format('H:i:s');
                    $newEndTime = $endTime->format('H:i:s');
                }

                // Check for conflicts
                $sessionConflicts = $this->checkTemplateConflicts(collect([$session]), [
                    'session_date' => $request->new_date,
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                    'venue' => $session->venue,
                    'room_number' => $session->room_number
                ]);

                if (!empty($sessionConflicts['critical'])) {
                    $conflicts[] = [
                        'session_id' => $session->id,
                        'conflicts' => $sessionConflicts['critical']
                    ];
                    continue;
                }

                // Preserve data if requested
                if ($request->preserve_enrollments) {
                    $preservationData[$session->id]['enrollments'] = $session->sessionEnrollments->toArray();
                }
                
                if ($request->preserve_learning_outcomes) {
                    $preservationData[$session->id]['learning_outcomes'] = $session->learning_outcomes_data;
                }

                // Apply rescheduling
                $session->update([
                    'session_date' => $request->new_date,
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                    'last_modified_by' => session('id'),
                    'modification_notes' => 'Bulk rescheduled: ' . ($request->reason ?? 'No reason provided')
                ]);

                // Restore preserved data
                if ($request->preserve_learning_outcomes && isset($preservationData[$session->id]['learning_outcomes'])) {
                    $session->update([
                        'learning_outcomes_data' => $preservationData[$session->id]['learning_outcomes']
                    ]);
                }

                $rescheduledCount++;
            }

            // Send notifications if requested
            if ($request->notify_participants && $rescheduledCount > 0) {
                $this->sendBulkRescheduleNotifications($sessions->take($rescheduledCount), $request->all());
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Successfully rescheduled {$rescheduledCount} sessions",
                'data' => [
                    'rescheduled_sessions' => $rescheduledCount,
                    'conflicts_detected' => count($conflicts),
                    'new_date' => $request->new_date
                ]
            ];

            if (!empty($conflicts)) {
                $response['conflicts'] = $conflicts;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk reschedule error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk rescheduling'
            ], 500);
        }
    }

    /**
     * Bulk change venue for multiple sessions
     */
    public function bulkChangeVenue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'exists:activity_sessions,id',
            'new_venue' => 'required|string|max:255',
            'new_room_number' => 'nullable|string|max:50',
            'preserve_enrollments' => 'boolean',
            'preserve_learning_outcomes' => 'boolean',
            'notify_participants' => 'boolean',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $sessions = ActivitySession::with(['sessionEnrollments'])
                ->whereIn('id', $request->session_ids)
                ->get();

            $updatedCount = 0;
            $conflicts = [];

            foreach ($sessions as $session) {
                // Check for venue conflicts
                $venueConflicts = ActivitySession::where('session_date', $session->session_date)
                    ->where('venue', $request->new_venue)
                    ->where('room_number', $request->new_room_number)
                    ->where('id', '!=', $session->id)
                    ->where(function($query) use ($session) {
                        $query->whereBetween('start_time', [$session->start_time, $session->end_time])
                              ->orWhereBetween('end_time', [$session->start_time, $session->end_time]);
                    })
                    ->exists();

                if ($venueConflicts) {
                    $conflicts[] = [
                        'session_id' => $session->id,
                        'type' => 'venue_conflict',
                        'message' => 'Venue booking conflict detected'
                    ];
                    continue;
                }

                // Update venue
                $session->update([
                    'venue' => $request->new_venue,
                    'room_number' => $request->new_room_number,
                    'last_modified_by' => session('id'),
                    'modification_notes' => 'Bulk venue change: ' . ($request->reason ?? 'No reason provided')
                ]);

                $updatedCount++;
            }

            // Send notifications if requested
            if ($request->notify_participants && $updatedCount > 0) {
                $this->sendBulkVenueChangeNotifications($sessions->take($updatedCount), $request->all());
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Successfully updated venue for {$updatedCount} sessions",
                'data' => [
                    'updated_sessions' => $updatedCount,
                    'conflicts_detected' => count($conflicts),
                    'new_venue' => $request->new_venue,
                    'new_room_number' => $request->new_room_number
                ]
            ];

            if (!empty($conflicts)) {
                $response['conflicts'] = $conflicts;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk venue change error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk venue change'
            ], 500);
        }
    }

    /**
     * Bulk cancel sessions with proper data preservation
     */
    public function bulkCancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'exists:activity_sessions,id',
            'cancellation_reason' => 'required|string|max:500',
            'preserve_enrollments' => 'boolean',
            'preserve_learning_outcomes' => 'boolean',
            'notify_participants' => 'boolean',
            'offer_alternative' => 'boolean',
            'alternative_session_id' => 'nullable|exists:activity_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $sessions = ActivitySession::with(['sessionEnrollments', 'activity'])
                ->whereIn('id', $request->session_ids)
                ->get();

            $cancelledCount = 0;
            $enrollmentTransfers = 0;

            foreach ($sessions as $session) {
                // Mark session as cancelled
                $session->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->cancellation_reason,
                    'cancelled_by' => session('id'),
                    'cancelled_at' => now(),
                    'last_modified_by' => session('id')
                ]);

                // Handle enrollment preservation/transfer
                if ($request->preserve_enrollments) {
                    if ($request->offer_alternative && $request->alternative_session_id) {
                        // Transfer enrollments to alternative session
                        foreach ($session->sessionEnrollments as $enrollment) {
                            // Check if trainee is already enrolled in alternative session
                            $existingEnrollment = SessionEnrollment::where('session_id', $request->alternative_session_id)
                                ->where('trainee_id', $enrollment->trainee_id)
                                ->first();

                            if (!$existingEnrollment) {
                                SessionEnrollment::create([
                                    'session_id' => $request->alternative_session_id,
                                    'trainee_id' => $enrollment->trainee_id,
                                    'enrollment_date' => now(),
                                    'attendance_status' => 'enrolled',
                                    'transferred_from_session' => $session->id,
                                    'enrollment_notes' => 'Transferred due to session cancellation'
                                ]);
                                $enrollmentTransfers++;
                            }
                        }
                    } else {
                        // Just preserve enrollment data in cancelled session
                        foreach ($session->sessionEnrollments as $enrollment) {
                            $enrollment->update([
                                'attendance_status' => 'cancelled',
                                'cancellation_reason' => $request->cancellation_reason
                            ]);
                        }
                    }
                }

                $cancelledCount++;
            }

            // Send notifications if requested
            if ($request->notify_participants && $cancelledCount > 0) {
                $this->sendBulkCancellationNotifications($sessions, $request->all());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully cancelled {$cancelledCount} sessions",
                'data' => [
                    'cancelled_sessions' => $cancelledCount,
                    'enrollment_transfers' => $enrollmentTransfers,
                    'alternative_offered' => $request->offer_alternative && $request->alternative_session_id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk cancellation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk cancellation'
            ], 500);
        }
    }

    // Helper methods for bulk operations
    private function sendBulkRescheduleNotifications($sessions, $data)
    {
        Log::info('Bulk reschedule notifications sent for ' . $sessions->count() . ' sessions');
    }

    private function sendBulkVenueChangeNotifications($sessions, $data)
    {
        Log::info('Bulk venue change notifications sent for ' . $sessions->count() . ' sessions');
    }

    private function sendBulkCancellationNotifications($sessions, $data)
    {
        Log::info('Bulk cancellation notifications sent for ' . $sessions->count() . ' sessions');
    }
}