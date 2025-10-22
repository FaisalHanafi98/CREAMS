<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityScheduleTemplate;
use App\Models\ActivityTemplateApplication;
use App\Models\Activity;
use App\Models\ActivitySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ScheduleTemplateController extends Controller
{
    /**
     * Display template library
     */
    public function index()
    {
        try {
            $role = session('role');
            $centreId = session('centre_id');

            $query = ActivityScheduleTemplate::with(['creator', 'centre', 'applications'])
                                            ->where('is_active', true);

            // Non-admin users see only their centre's templates
            if ($role !== 'admin') {
                $query->where('centre_id', $centreId);
            }

            $templates = $query->orderBy('template_name')->paginate(12);
            $templateTypes = ActivityScheduleTemplate::getTemplateTypes();

            return view('activities.templates.index', compact('templates', 'templateTypes'));

        } catch (Exception $e) {
            Log::error('Error loading templates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load templates.');
        }
    }

    /**
     * Show template creation form
     */
    public function create()
    {
        $templateTypes = ActivityScheduleTemplate::getTemplateTypes();
        $dayCombinations = ActivityScheduleTemplate::getCommonDayCombinations();
        
        return view('activities.templates.create', compact('templateTypes', 'dayCombinations'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'sessions_per_week' => 'required|integer|min:1|max:7',
            'duration_weeks' => 'required|integer|min:1|max:52',
            'session_length_minutes' => 'required|integer|min:15|max:480',
            'days_of_week' => 'required|array|min:1',
            'time_slots' => 'required|array|min:1',
            'template_type' => 'required|in:weekly,intensive,flexible,custom'
        ]);

        try {
            DB::beginTransaction();

            $template = ActivityScheduleTemplate::create([
                'template_name' => $request->template_name,
                'description' => $request->description,
                'sessions_per_week' => $request->sessions_per_week,
                'duration_weeks' => $request->duration_weeks,
                'session_length_minutes' => $request->session_length_minutes,
                'days_of_week' => $request->days_of_week,
                'time_slots' => $request->time_slots,
                'template_type' => $request->template_type,
                'created_by' => session('id'),
                'centre_id' => session('centre_id')
            ]);

            DB::commit();

            Log::info('Template created successfully', ['template_id' => $template->id]);

            return redirect()->route('activities.templates.index')
                           ->with('success', 'Template created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create template.');
        }
    }

    /**
     * Apply template to activity
     */
    public function applyTemplate(Request $request)
    {
        // Get centre_id from session for holiday checking
        $centreId = session('centre_id');

        $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'template_id' => 'required|exists:activity_schedule_templates,id',
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
                new \App\Rules\NoWeekendOrHolidayRule($centreId)
            ],
            'customizations' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $template = ActivityScheduleTemplate::findOrFail($request->template_id);
            $activity = Activity::findOrFail($request->activity_id);

            $endDate = \Carbon\Carbon::parse($request->start_date)
                                   ->addWeeks($template->duration_weeks);

            // Create template application
            $application = ActivityTemplateApplication::create([
                'activity_id' => $request->activity_id,
                'template_id' => $request->template_id,
                'start_date' => $request->start_date,
                'end_date' => $endDate,
                'customizations' => $request->customizations ?? [],
                'status' => 'active'
            ]);

            // Generate sessions
            $sessionsCreated = $application->generateSessions();

            DB::commit();

            Log::info('Template applied successfully', [
                'application_id' => $application->id,
                'sessions_created' => $sessionsCreated
            ]);

            return response()->json([
                'success' => true,
                'message' => "Template applied! Generated {$sessionsCreated} sessions.",
                'sessions_created' => $sessionsCreated
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error applying template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply template.'
            ], 500);
        }
    }

    /**
     * Get templates for AJAX
     */
    public function getTemplates(Request $request)
    {
        $centreId = session('centre_id');
        $role = session('role');

        $query = ActivityScheduleTemplate::where('is_active', true);

        if ($role !== 'admin') {
            $query->where('centre_id', $centreId);
        }

        $templates = $query->get()->map(function ($template) {
            return [
                'id' => $template->id,
                'template_name' => $template->template_name,
                'description' => $template->description,
                'sessions_per_week' => $template->sessions_per_week,
                'duration_weeks' => $template->duration_weeks,
                'total_sessions' => $template->total_sessions,
                'template_type' => $template->template_type,
                'days_of_week' => $template->days_of_week,
                'time_slots' => $template->time_slots
            ];
        });

        return response()->json($templates);
    }

    /**
     * Show template details
     */
    public function show($id)
    {
        try {
            $template = ActivityScheduleTemplate::with(['creator', 'centre', 'applications.activity'])
                                                ->findOrFail($id);

            // Check access permissions
            if (session('role') !== 'admin' && $template->centre_id !== session('centre_id')) {
                abort(403, 'Unauthorized access to template.');
            }

            return view('activities.templates.show', compact('template'));

        } catch (Exception $e) {
            Log::error('Error loading template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Template not found.');
        }
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        try {
            $template = ActivityScheduleTemplate::findOrFail($id);

            // Check permissions
            if (session('role') !== 'admin' && $template->centre_id !== session('centre_id')) {
                abort(403, 'Unauthorized access.');
            }

            // Soft delete by marking inactive
            $template->update(['is_active' => false]);

            Log::info('Template deactivated', ['template_id' => $id]);

            return redirect()->back()->with('success', 'Template deactivated successfully.');

        } catch (Exception $e) {
            Log::error('Error deleting template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete template.');
        }
    }
}
