<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;

// Test route that simulates what user sees
Route::get("/test-dashboard", function() {
    // Simulate being logged in as admin
    session([
        "id" => 1, 
        "role" => "admin", 
        "centre_id" => "01", 
        "name" => "Muhammad Syafiq bin Moh"
    ]);
    
    $dashboardController = new DashboardController();
    $request = new Illuminate\Http\Request();
    
    try {
        $response = $dashboardController->index($request);
        
        if ($response instanceof Illuminate\View\View) {
            $data = $response->getData();
            
            // Return JSON of what the dashboard should show
            return response()->json([
                "status" => "success",
                "data_summary" => [
                    "general_tab_stats" => $data["stats_flat"] ?? "not found",
                    "personal_tab_stats" => $data["personal_stats"] ?? "not found", 
                    "calendar_events_count" => count($data["calendar_events"] ?? []),
                    "recent_activities_count" => count($data["recent_activities_centre"] ?? [])
                ],
                "what_you_should_see" => [
                    "General Tab" => [
                        "Total Users" => ($data["stats_flat"]["total_users"] ?? 0),
                        "Active Trainees" => ($data["stats_flat"]["total_trainees"] ?? 0),
                        "Active Programs" => ($data["stats_flat"]["total_activities"] ?? 0),
                        "Active Centres" => ($data["stats_flat"]["active_centres"] ?? 0)
                    ],
                    "Personal Tab" => [
                        "My Activities" => ($data["personal_stats"]["user_activities"] ?? 0),
                        "Weekly Sessions" => ($data["personal_stats"]["weekly_sessions"] ?? 0),
                        "Completion Rate" => ($data["personal_stats"]["completion_rate"] ?? 0) . "%",
                        "Average Attendance" => ($data["personal_stats"]["avg_attendance"] ?? 0) . "%"
                    ],
                    "My Schedule" => [
                        "Calendar Events" => count($data["calendar_events"] ?? []) . " upcoming sessions",
                        "Calendar Style" => "7-day grid with event dots"
                    ]
                ],
                "css_check" => [
                    "dashboard_widgets_css_exists" => file_exists(public_path("css/dashboard-widgets.css")),
                    "css_file_size" => file_exists(public_path("css/dashboard-widgets.css")) ? 
                        filesize(public_path("css/dashboard-widgets.css")) . " bytes" : "File not found"
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            return response()->json(["status" => "error", "message" => "Dashboard not returning view"]);
        }
    } catch (Exception $e) {
        return response()->json([
            "status" => "error", 
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
});
?>