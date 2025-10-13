<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Event;
use App\Models\Volunteer;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centre;
use App\Models\Asset;
use App\Models\User;
use App\Models\Notification;

class AJKController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:ajk');
    }

    /**
     * Display the AJK dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');

        Log::info('AJK accessed dashboard', [
            'ajk_id' => $ajkId,
            'centre_id' => $centreId
        ]);

        // Get counts for dashboard cards
        $eventCount = Event::where('centre_id', $centreId)->count();
        $volunteerCount = Volunteer::where('centre_id', $centreId)->count();
        $upcomingEventCount = Event::where('centre_id', $centreId)
            ->where('date', '>=', now())
            ->count();

        // Get upcoming events
        $upcomingEvents = Event::where('centre_id', $centreId)
            ->where('date', '>=', now())
            ->orderBy('date')
            ->take(5)
            ->get();

        // Get recent volunteer applications
        $recentVolunteers = Volunteer::where('centre_id', $centreId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get centre details
        $centre = Centre::find($centreId);

        return view('AJK.dashboard', [
            'eventCount' => $eventCount,
            'volunteerCount' => $volunteerCount,
            'upcomingEventCount' => $upcomingEventCount,
            'upcomingEvents' => $upcomingEvents,
            'recentVolunteers' => $recentVolunteers,
            'centre' => $centre
        ]);
    }

    /**
     * Display a listing of users.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed users list', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // For AJK users, we might show a limited view of users
        // This could include volunteers or other AJK at the same centre
        $volunteers = Volunteer::where('centre_id', $centreId)->get();
        $ajks = User::where('role', 'ajk')
            ->where('centre_id', $centreId)
            ->where('id', '!=', $ajkId)
            ->get();

        return view('ajk.users', [
            'volunteers' => $volunteers,
            'ajks' => $ajks
        ]);
    }

    /**
     * Display a listing of trainees.
     *
     * @return \Illuminate\View\View
     */
    public function trainees()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed trainees list', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get trainees for this centre
        $trainees = Trainee::where('centre_id', $centreId)->get();

        return view('ajk.trainees', [
            'trainees' => $trainees
        ]);
    }

    /**
     * Display a listing of centers.
     *
     * @return \Illuminate\View\View
     */
    public function centres()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed centres list', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get centre information - AJK should only see their own centre
        $centre = Centre::find($centreId);

        // Get event statistics for this centre
        $eventStats = $this->getCentreEventStats($centreId);

        return view('ajk.centres', [
            'centre' => $centre,
            'eventStats' => $eventStats
        ]);
    }

    /**
     * Get event statistics for a specific centre.
     *
     * @param string $centreId
     * @return array
     */
    private function getCentreEventStats($centreId)
    {
        $totalEvents = Event::where('centre_id', $centreId)->count();
        $pastEvents = Event::where('centre_id', $centreId)
            ->where('date', '<', now())
            ->count();
        $upcomingEvents = Event::where('centre_id', $centreId)
            ->where('date', '>=', now())
            ->count();
        $totalVolunteers = Volunteer::where('centre_id', $centreId)->count();
        $approvedVolunteers = Volunteer::where('centre_id', $centreId)
            ->where('status', 'approved')
            ->count();

        return [
            'totalEvents' => $totalEvents,
            'pastEvents' => $pastEvents,
            'upcomingEvents' => $upcomingEvents,
            'totalVolunteers' => $totalVolunteers,
            'approvedVolunteers' => $approvedVolunteers
        ];
    }

    /**
     * Display a listing of assets.
     *
     * @return \Illuminate\View\View
     */
    public function assets()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed assets list', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get assets for this centre
        $assets = Asset::where('centre_id', $centreId)->get();

        // Get asset categories count
        $assetsByType = Asset::where('centre_id', $centreId)
            ->selectRaw('asset_parent, count(*) as count')
            ->groupBy('asset_parent')
            ->get();

        return view('ajk.assets', [
            'assets' => $assets,
            'assetsByType' => $assetsByType
        ]);
    }

    /**
     * Display a listing of reports.
     *
     * @return \Illuminate\View\View
     */
    public function reports()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed reports', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get report data for this centre
        $eventData = $this->getEventReportData($centreId);
        $volunteerData = $this->getVolunteerReportData($centreId);

        return view('ajk.reports', [
            'eventData' => $eventData,
            'volunteerData' => $volunteerData
        ]);
    }

    /**
     * Get event report data for a specific centre.
     *
     * @param string $centreId
     * @return array
     */
    private function getEventReportData($centreId)
    {
        // Get events by month
        $eventsByMonth = Event::where('centre_id', $centreId)
            ->selectRaw('MONTH(date) as month, YEAR(date) as year, COUNT(*) as count')
            ->whereRaw('date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format data for chart
        $formattedData = [];
        foreach ($eventsByMonth as $event) {
            $monthName = date('F', mktime(0, 0, 0, $event->month, 1, $event->year));
            $formattedData[] = [
                'month' => $monthName,
                'count' => $event->count
            ];
        }

        return $formattedData;
    }

    /**
     * Get volunteer report data for a specific centre.
     *
     * @param string $centreId
     * @return array
     */
    private function getVolunteerReportData($centreId)
    {
        // Get volunteers by status
        $volunteersByStatus = Volunteer::where('centre_id', $centreId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        // Get volunteers by month
        $volunteersByMonth = Volunteer::where('centre_id', $centreId)
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format data for charts
        $statusData = [];
        foreach ($volunteersByStatus as $volunteer) {
            $statusData[] = [
                'status' => ucfirst($volunteer->status),
                'count' => $volunteer->count
            ];
        }

        $monthData = [];
        foreach ($volunteersByMonth as $volunteer) {
            $monthName = date('F', mktime(0, 0, 0, $volunteer->month, 1, $volunteer->year));
            $monthData[] = [
                'month' => $monthName,
                'count' => $volunteer->count
            ];
        }

        return [
            'statusData' => $statusData,
            'monthData' => $monthData
        ];
    }

    /**
     * Display settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed settings', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get user settings
        $user = User::find($ajkId);
        $centre = Centre::find($centreId);

        return view('ajk.settings', [
            'user' => $user,
            'centre' => $centre
        ]);
    }

    /**
     * Update user settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $ajkId = session('id');
        Log::info('AJK updating settings', ['ajk_id' => $ajkId]);

        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'password' => 'nullable|min:5|confirmed',
            'notification_preferences' => 'sometimes|array'
        ]);

        // Update user
        $user = User::find($ajkId);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Handle notification preferences if they exist
        if (isset($validated['notification_preferences'])) {
            $user->notification_preferences = $validated['notification_preferences'];
        }

        $user->save();

        return redirect()->route('ajk.settings')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Display a listing of activities.
     *
     * @return \Illuminate\View\View
     */
    public function activities()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK accessed activities list', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get activities for this centre
        $activities = Activity::where('centre_id', $centreId)
            ->orderBy('date', 'desc')
            ->get();

        // Get activity statistics
        $upcomingActivities = Activity::where('centre_id', $centreId)
            ->where('date', '>=', now())
            ->count();

        $pastActivities = Activity::where('centre_id', $centreId)
            ->where('date', '<', now())
            ->count();

        return view('ajk.activities', [
            'activities' => $activities,
            'upcomingActivities' => $upcomingActivities,
            'pastActivities' => $pastActivities
        ]);
    }

    /**
     * Display a list of volunteers.
     *
     * @return \Illuminate\View\View
     */
    public function manageVolunteers()
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK managing volunteers', ['ajk_id' => $ajkId, 'centre_id' => $centreId]);

        // Get volunteers for this centre, grouped by status
        $pendingVolunteers = Volunteer::where('centre_id', $centreId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedVolunteers = Volunteer::where('centre_id', $centreId)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        $rejectedVolunteers = Volunteer::where('centre_id', $centreId)
            ->where('status', 'rejected')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ajk.volunteers', [
            'pendingVolunteers' => $pendingVolunteers,
            'approvedVolunteers' => $approvedVolunteers,
            'rejectedVolunteers' => $rejectedVolunteers
        ]);
    }

    /**
     * View specific volunteer details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewVolunteer($id)
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK viewing volunteer', ['ajk_id' => $ajkId, 'volunteer_id' => $id]);

        // Get volunteer
        $volunteer = Volunteer::findOrFail($id);

        // Check if this AJK has access to this volunteer
        if ($volunteer->centre_id != $centreId) {
            return redirect()->route('ajk.volunteers')
                ->with('error', 'You do not have permission to view this volunteer');
        }

        // Get volunteer's participation history
        $volunteerEvents = [];
        if (method_exists($volunteer, 'events')) {
            $volunteerEvents = $volunteer->events()
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('ajk.volunteer.view', [
            'volunteer' => $volunteer,
            'events' => $volunteerEvents
        ]);
    }

    /**
     * Show the form for editing the specified volunteer.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editVolunteer($id)
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK editing volunteer', ['ajk_id' => $ajkId, 'volunteer_id' => $id]);

        // Get volunteer
        $volunteer = Volunteer::findOrFail($id);

        // Check if this AJK has access to this volunteer
        if ($volunteer->centre_id != $centreId) {
            return redirect()->route('ajk.volunteers')
                ->with('error', 'You do not have permission to edit this volunteer');
        }

        // Get centre events for assignment
        $upcomingEvents = Event::where('centre_id', $centreId)
            ->where('date', '>=', now())
            ->orderBy('date')
            ->get();

        return view('ajk.volunteer.edit', [
            'volunteer' => $volunteer,
            'upcomingEvents' => $upcomingEvents
        ]);
    }

    /**
     * Update the specified volunteer.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateVolunteer(Request $request, $id)
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK updating volunteer', ['ajk_id' => $ajkId, 'volunteer_id' => $id]);

        // Get volunteer
        $volunteer = Volunteer::findOrFail($id);

        // Check if this AJK has access to this volunteer
        if ($volunteer->centre_id != $centreId) {
            return redirect()->route('ajk.volunteers')
                ->with('error', 'You do not have permission to update this volunteer');
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'interest' => 'sometimes|nullable|string|max:255',
            'availability' => 'sometimes|nullable|array',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
            'notes' => 'nullable|string'
        ]);

        // Update volunteer
        $volunteer->update($validated);

        // Handle event assignments if they exist
        if (isset($validated['event_ids']) && method_exists($volunteer, 'events')) {
            $volunteer->events()->sync($validated['event_ids']);
        }

        return redirect()->route('ajk.volunteers')
            ->with('success', 'Volunteer updated successfully');
    }

    /**
     * Change the status of a volunteer.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeVolunteerStatus(Request $request, $id)
    {
        $ajkId = session('id');
        $centreId = session('centre_id');
        Log::info('AJK changing volunteer status', ['ajk_id' => $ajkId, 'volunteer_id' => $id]);

        // Get volunteer
        $volunteer = Volunteer::findOrFail($id);

        // Check if this AJK has access to this volunteer
        if ($volunteer->centre_id != $centreId) {
            return redirect()->route('ajk.volunteers')
                ->with('error', 'You do not have permission to change this volunteer\'s status');
        }

        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'status_notes' => 'nullable|string'
        ]);

        // Get previous status
        $previousStatus = $volunteer->status;

        // Update volunteer status
        $volunteer->status = $validated['status'];

        if (isset($validated['status_notes'])) {
            $volunteer->status_notes = $validated['status_notes'];
        }

        $volunteer->status_updated_at = now();
        $volunteer->status_updated_by = $ajkId;
        $volunteer->save();

        // Create notification for the volunteer if status changed to approved or rejected
        if ($previousStatus != $validated['status'] && in_array($validated['status'], ['approved', 'rejected'])) {
            $this->notifyVolunteerOfStatusChange($volunteer, $validated['status']);
        }

        return redirect()->route('ajk.volunteers')
            ->with('success', 'Volunteer status updated successfully');
    }

    /**
     * Notify volunteer of status change.
     *
     * @param Volunteer $volunteer
     * @param string $status
     * @return void
     */
    private function notifyVolunteerOfStatusChange($volunteer, $status)
    {
        // This would typically send an email to the volunteer
        // For now, just log the action
        Log::info('Volunteer status notification would be sent', [
            'volunteer_id' => $volunteer->id,
            'volunteer_email' => $volunteer->email,
            'status' => $status
        ]);

        // In a real implementation, you might use:
        // Mail::to($volunteer->email)->send(new VolunteerStatusChanged($volunteer, $status));
    }

    /**
     * Display notifications for the AJK.
     *
     * @return \Illuminate\View\View
     */
    public function notifications()
    {
        $ajkId = session('id');
        Log::info('AJK accessed notifications', ['ajk_id' => $ajkId]);

        // Get notifications for this AJK
        $notifications = Notification::where('user_id', $ajkId)
            ->where('user_type', 'App\\Models\\User')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get unread count
        $unreadCount = Notification::where('user_id', $ajkId)
            ->where('user_type', 'App\\Models\\User')
            ->where('read', false)
            ->count();

        return view('ajk.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Mark notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markNotificationsRead(Request $request)
    {
        $ajkId = session('id');
        Log::info('AJK marking notifications as read', ['ajk_id' => $ajkId]);

        // Validate request
        $validated = $request->validate([
            'notification_ids' => 'sometimes|array',
            'notification_ids.*' => 'exists:notifications,id',
            'all' => 'sometimes|boolean'
        ]);

        // Mark specific notifications as read
        if (isset($validated['notification_ids'])) {
            Notification::whereIn('id', $validated['notification_ids'])
                ->where('user_id', $ajkId)
                ->where('user_type', 'App\\Models\\User')
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
        }

        // Mark all notifications as read
        if (isset($validated['all']) && $validated['all']) {
            Notification::where('user_id', $ajkId)
                ->where('user_type', 'App\\Models\\User')
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }
}
