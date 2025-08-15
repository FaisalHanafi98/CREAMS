<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Main Controllers
use App\Http\Controllers\MainController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Profile\UserProfileController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\Staff\StaffsHomeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\StaffAttendanceController;

// User Management Controllers
use App\Http\Controllers\UserController;
use App\Http\Controllers\Trainee\TraineeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Staff\SupervisorController;
use App\Http\Controllers\Staff\TeacherController;
use App\Http\Controllers\Staff\AJKController;
use App\Http\Controllers\Staff\StaffController;

// Trainee Management Controllers
use App\Http\Controllers\Trainee\TraineeHomeController;
use App\Http\Controllers\Trainee\TraineeProfileController;
use App\Http\Controllers\Trainee\TraineeRegistrationController;
use App\Http\Controllers\Trainee\TraineeManagementController;
// Legacy enhanced controller - functionality moved to main TraineeController

// Activity and Resource Controllers
use App\Http\Controllers\Activity\ActivityController;
use App\Http\Controllers\Activity\ScheduleTemplateController;
use App\Http\Controllers\Centre\CentreController;
use App\Http\Controllers\Centre\AssetController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EventController;

// Report and Admin Controllers
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

// Notification and Communication Controllers
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Profile\LetterController as ProfileLetterController;
use App\Http\Controllers\Profile\LetterTemplateController;
use App\Http\Controllers\Letters\ModernLetterController;

// Auth Controllers
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Home page with role-based redirection
Route::get('/', function () {
    if (session('id') && session('role')) {
        $role = session('role');
        return redirect()->route("{$role}.dashboard");
    }
    return view('home');
})->name('home');

// Public information pages
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/volunteer', [VolunteerController::class, 'index'])->name('volunteer');
Route::post('/volunteer/submit', [VolunteerController::class, 'submit'])->name('volunteer.submit');

// Debug route for testing authentication
Route::get('/debug/session', function() {
    return response()->json([
        'session_data' => session()->all(),
        'is_authenticated' => \App\Services\SessionManager::check(),
        'user_role' => session('role'),
        'user_id' => session('id'),
        'user_name' => session('name')
    ]);
});

// Admin routes for volunteer management  
Route::middleware(['enhanced.auth'])->group(function () {
    Route::get('/admin/volunteers', [VolunteerController::class, 'adminIndex'])->name('admin.volunteers.index');
    Route::get('/volunteer/applications', [VolunteerController::class, 'getApplications'])->name('volunteer.applications');
    Route::get('/volunteer/applications/{id}', [VolunteerController::class, 'show'])->name('volunteer.applications.show');
    Route::post('/volunteer/applications/{id}/approve', [VolunteerController::class, 'approve'])->name('volunteer.applications.approve');
    Route::post('/volunteer/applications/{id}/reject', [VolunteerController::class, 'reject'])->name('volunteer.applications.reject');
    Route::post('/volunteer/applications/{id}/status', [VolunteerController::class, 'updateStatus'])->name('volunteer.applications.status');
    Route::get('/volunteer/centres', [VolunteerController::class, 'getCentres'])->name('volunteer.centres');
});
Route::get('/trademark', function () {
    return view('trademarks');
})->name('trademark');

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Standard login routes
    Route::get('/auth/login', [MainController::class, 'login'])->name('auth.loginpage');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/auth/check', [MainController::class, 'check'])->name('auth.check');
    
    // Enhanced login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration routes - legacy auth/register now redirects to staffs/register
    Route::get('/auth/register', function() { return redirect()->route('staffs.register'); });
    Route::get('/registration', [MainController::class, 'registration'])->name('registration');
    Route::get('/staffs/register', [MainController::class, 'registration'])->name('staffs.register');
    Route::post('/auth/save', [MainController::class, 'save'])->name('auth.save');

    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'submitForgotPasswordForm'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('password.update');
});

// Logout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::post('/logout', [MainController::class, 'logout'])->name('logout.post');
    
    // Enhanced logout route (alternative endpoint)
    Route::post('/logout-enhanced', [LoginController::class, 'logout'])->name('logout.enhanced');
});

// Enhanced authentication API routes
Route::middleware(['web'])->group(function () {
    Route::get('/auth/check-status', [LoginController::class, 'checkAuth'])->name('auth.check-status');
    Route::post('/auth/extend-session', [LoginController::class, 'extendSession'])->name('auth.extend');
    Route::post('/auth/refresh-session', [LoginController::class, 'refreshSession'])->name('auth.refresh');
});

/*
|--------------------------------------------------------------------------
| COMMON AUTHENTICATED ROUTES (Available to All Logged-in User)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'validate.params'])->group(function () {
    // Modern Dashboard - Main route
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('dashboard');
    
    // New Modern Dashboard Template
    Route::get('/dashboard/modern-new', [App\Http\Controllers\Dashboard\DashboardController::class, 'modernNew'])->name('dashboard.modern-new');
    
    // Enhanced Dashboard with Improved UX
    Route::get('/dashboard/enhanced', [App\Http\Controllers\Dashboard\DashboardController::class, 'enhanced'])->name('dashboard.enhanced');
    
    // Optimized Dashboard Routes
    // Optimized Dashboard System Routes with Enhanced Middleware
    Route::prefix('dashboard')->name('dashboard.')->middleware(['throttle:dashboard'])->group(function () {
        // Main dashboard with caching - Note: This route is accessed via the dashboard redirect above
        Route::get('/main', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])
            ->name('optimized')
            ->middleware('cache.headers:public;max_age=300;etag');
            
        // Additional dashboard features (temporarily disabled - can be re-enabled later)
        // Dashboard API endpoints for real-time features
        Route::get('/updates', [App\Http\Controllers\Dashboard\DashboardController::class, 'getUpdates'])->name('updates');
        Route::post('/refresh-stats', [App\Http\Controllers\Dashboard\DashboardController::class, 'refreshStats'])->name('refresh-stats');
        Route::post('/refresh-widget', [App\Http\Controllers\Dashboard\DashboardController::class, 'refreshWidget'])->name('refresh-widget');
        Route::get('/widget/{widget}', [App\Http\Controllers\Dashboard\DashboardController::class, 'getWidget'])->name('widget');
    });
    
    // Profile management
    // Profile management routes
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
    Route::get('/profile/home', [UserProfileController::class, 'showProfile'])->name('profile.home');
    Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    
    // Template management routes
    Route::post('/profile/templates/save', [UserProfileController::class, 'saveTemplate'])->name('profile.templates.save');
    Route::get('/profile/templates/load', [UserProfileController::class, 'loadTemplates'])->name('profile.templates.load');
    Route::post('/profile/templates/load-single', [UserProfileController::class, 'loadTemplate'])->name('profile.templates.load-single');
    Route::get('/profile/letters/archive', [UserProfileController::class, 'getLetterArchive'])->name('profile.letters.archive');
    
    // Letter template routes for profile (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/profile/letter-template', [LetterTemplateController::class, 'store'])->name('profile.letter.store');
        Route::post('/profile/letter-generate', [LetterTemplateController::class, 'generate'])->name('profile.letter.generate');
        Route::post('/profile/letter-preview', [LetterTemplateController::class, 'preview'])->name('profile.letter.preview');
        Route::get('/profile/letter-new-reference', [LetterTemplateController::class, 'newReference'])->name('profile.letter.newReference');
        Route::get('/profile/letter-download/{id}', [LetterTemplateController::class, 'downloadLetter'])->name('profile.letter.download');
    });

    // Profile Letter Generation Routes (All authenticated users)
    Route::post('/letters/generate', [LetterController::class, 'generate'])->name('letters.generate');
    Route::post('/profile/letters/generate', [LetterTemplateController::class, 'generate'])->name('profile.letters.generate');
    Route::get('/profile/letters/{letter}/preview', [LetterTemplateController::class, 'viewLetter'])->name('profile.letters.preview');
    Route::get('/profile/letters/{letter}/download', [LetterTemplateController::class, 'downloadLetter'])->name('profile.letters.download');


    // Activity Management
    Route::prefix('activities')->name('activities.')->middleware(['centre.access:activity'])->group(function () {
        Route::get('/', function() { return redirect()->route('activities.home'); }); // Legacy redirect
        Route::get('/home', [ActivityController::class, 'index'])->name('home'); // New structure
        Route::get('/modern-home', [ActivityController::class, 'modernHome'])->name('modern-home'); // Modern activities homepage
        Route::get('/categories', [ActivityController::class, 'categories'])->name('categories');
        Route::get('/categories/{categorySlug}', [ActivityController::class, 'categoryShow'])->name('categories.show');
        Route::get('/schedule', [ActivityController::class, 'scheduleIndex'])->name('schedule');
        
        // Template routes
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [ScheduleTemplateController::class, 'index'])->name('index');
            Route::get('/get-templates', [ScheduleTemplateController::class, 'getTemplates'])->name('get');
            Route::get('/{id}', [ScheduleTemplateController::class, 'show'])->name('show');
            
            // Admin and Supervisor can create/manage templates
            Route::middleware(['role:admin,supervisor'])->group(function () {
                Route::get('/create', [ScheduleTemplateController::class, 'create'])->name('create');
                Route::post('/', [ScheduleTemplateController::class, 'store'])->name('store');
                Route::post('/apply', [ScheduleTemplateController::class, 'applyTemplate'])->name('apply');
                Route::delete('/{id}', [ScheduleTemplateController::class, 'destroy'])->name('destroy');
            });
        });
        
        
        // Admin only routes - Activity creation and management
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/create', [ActivityController::class, 'create'])->name('create');
            Route::post('/', [ActivityController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ActivityController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ActivityController::class, 'update'])->name('update');
            Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/sessions', [ActivityController::class, 'sessions'])->name('sessions');
            Route::post('/{id}/sessions', [ActivityController::class, 'createSession'])->name('sessions.create');
        });
        
        // Learning Outcomes routes (Teacher, Admin, Supervisor)
        Route::prefix('learning-outcomes')->name('learning-outcomes.')->middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/', [App\Http\Controllers\LearningOutcomeController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\LearningOutcomeController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\LearningOutcomeController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\LearningOutcomeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\LearningOutcomeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\LearningOutcomeController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\LearningOutcomeController::class, 'destroy'])->name('destroy');
            Route::post('/update-order', [App\Http\Controllers\LearningOutcomeController::class, 'updateOrder'])->name('update-order');
        });
        
        // Session-Level Learning Outcomes Management (Teacher, Admin, Supervisor)
        Route::prefix('sessions')->name('sessions.')->middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/{sessionId}/learning-outcomes', [App\Http\Controllers\Activity\SessionLearningOutcomeController::class, 'index'])->name('learning-outcomes.index');
            Route::post('/{sessionId}/learning-outcomes', [App\Http\Controllers\Activity\SessionLearningOutcomeController::class, 'store'])->name('learning-outcomes.store');
            Route::post('/{sessionId}/learning-outcomes/progress', [App\Http\Controllers\Activity\SessionLearningOutcomeController::class, 'updateTraineeProgress'])->name('learning-outcomes.update-progress');
            Route::get('/{sessionId}/learning-outcomes/analytics', [App\Http\Controllers\Activity\SessionLearningOutcomeController::class, 'getSessionAnalytics'])->name('learning-outcomes.analytics');
            Route::get('/{sessionId}/learning-outcomes/available', [App\Http\Controllers\Activity\SessionLearningOutcomeController::class, 'getAvailableOutcomes'])->name('learning-outcomes.available');
            
            // Template Modification Routes (Admin, Supervisor only)
            Route::middleware(['role:admin,supervisor'])->group(function () {
                Route::get('/{sessionId}/template-data', [App\Http\Controllers\Activity\SessionTemplateController::class, 'getTemplateData'])->name('template-data');
                Route::post('/{sessionId}/template-preview', [App\Http\Controllers\Activity\SessionTemplateController::class, 'previewTemplateChanges'])->name('template-preview');
                Route::post('/{sessionId}/template-modify', [App\Http\Controllers\Activity\SessionTemplateController::class, 'applyTemplateModifications'])->name('template-modify');
                Route::post('/{sessionId}/create-template', [App\Http\Controllers\Activity\SessionTemplateController::class, 'createTemplateFromSession'])->name('create-template');
            });
        });
        
        // Template Application Routes (Admin, Supervisor only)
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::post('/{activityId}/apply-template-similar', [App\Http\Controllers\Activity\SessionTemplateController::class, 'applyTemplateToSimilar'])->name('apply-template-similar');
            
            // Bulk Session Operations
            Route::post('/bulk/reschedule', [App\Http\Controllers\Activity\SessionTemplateController::class, 'bulkReschedule'])->name('bulk-reschedule');
            Route::post('/bulk/change-venue', [App\Http\Controllers\Activity\SessionTemplateController::class, 'bulkChangeVenue'])->name('bulk-change-venue');
            Route::post('/bulk/cancel', [App\Http\Controllers\Activity\SessionTemplateController::class, 'bulkCancel'])->name('bulk-cancel');
        });
        
        Route::get('/{id}', [ActivityController::class, 'show'])->name('show');
        
        // Scheduling routes (Teacher, Admin, Supervisor)
        Route::middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/{id}/schedule', [ActivityController::class, 'schedule'])->name('activity.schedule');
            Route::post('/{id}/schedule', [ActivityController::class, 'storeSchedule'])->name('schedule.store');
            Route::get('/{id}/enroll', [ActivityController::class, 'enrollmentForm'])->name('enroll');
            Route::post('/{id}/enroll', [ActivityController::class, 'enrollTrainees'])->name('enroll.submit');
        });
        
        // Teacher routes
        Route::middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'markAttendance'])->name('activities.attendance');
            Route::post('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'storeAttendance'])->name('activities.attendance.store');
            Route::get('/{activityId}/sessions/{sessionId}/enrollments', [ActivityController::class, 'manageEnrollments'])->name('enrollments');
            Route::post('/{activityId}/sessions/{sessionId}/enroll', [ActivityController::class, 'enrollTrainees'])->name('enroll.legacy');
            Route::post('/{activityId}/sessions/{sessionId}/enrollments/add', [ActivityController::class, 'addEnrollment'])->name('enrollments.add');
            Route::delete('/{activityId}/sessions/{sessionId}/enrollments/{traineeId}', [ActivityController::class, 'removeEnrollment'])->name('enrollments.remove');
        });
        
        // Enhanced Schedule routes
        Route::get('/schedule', [ActivityController::class, 'scheduleIndex'])->name('schedule');
        Route::get('/schedule/personal', [ActivityController::class, 'personalSchedule'])->name('schedule.personal');
        Route::get('/schedule/staff/{encryptedId}', [ActivityController::class, 'staffSchedule'])->name('schedule.staff');
        Route::get('/schedule/trainee/{encryptedId}', [ActivityController::class, 'traineeSchedule'])->name('schedule.trainee');
        Route::get('/schedule/weekly', [ActivityController::class, 'weeklySchedule'])->name('schedule.weekly');
        Route::get('/schedule/calendar-data', [ActivityController::class, 'getCalendarData'])->name('schedule.calendar-data');
        Route::get('/schedule/teacher/{teacherId}', [ActivityController::class, 'teacherSchedule'])->name('schedule.teacher');
    });

    // Enhanced Attendance Management (Activity-Centre Integration)
    Route::prefix('centre/attendance')->name('centre.enhanced-attendance.')
         ->middleware(['role:admin,supervisor,teacher'])->group(function () {
        Route::get('/', [App\Http\Controllers\Centre\EnhancedAttendanceController::class, 'index'])->name('index');
        Route::get('/analytics', [App\Http\Controllers\Centre\EnhancedAttendanceController::class, 'analytics'])->name('analytics');
        Route::get('/export', [App\Http\Controllers\Centre\EnhancedAttendanceController::class, 'export'])->name('export');
        Route::get('/session/{sessionId}/mark', [App\Http\Controllers\Centre\EnhancedAttendanceController::class, 'markActivityAttendance'])->name('mark-session');
        Route::post('/session/{sessionId}/store', [App\Http\Controllers\Centre\EnhancedAttendanceController::class, 'storeActivityAttendance'])->name('store-session');
    });

    // NEW ENHANCED ACTIVITY MANAGEMENT SYSTEM - COMMENTED OUT FOR NOW
    /*
    Route::prefix('new-activities')->name('new-activities.')->middleware(['enhanced.auth'])->group(function () {
        // Main dashboard (role-based views)
        Route::get('/', [NewActivityController::class, 'index'])->name('index');
        
        // Admin and Supervisor routes
        Route::middleware(['enhanced.role:admin,supervisor'])->group(function () {
            Route::post('/', [NewActivityController::class, 'store'])->name('store');
            Route::post('/{id}/schedule-session', [NewActivityController::class, 'scheduleSession'])->name('schedule-session');
            Route::post('/{id}/enroll-trainee', [NewActivityController::class, 'enrollTrainee'])->name('enroll-trainee');
        });
        
        // Teacher, Admin, Supervisor routes
        Route::middleware(['enhanced.role:teacher,admin,supervisor'])->group(function () {
            Route::post('/sessions/{id}/attendance', [NewActivityController::class, 'markAttendance'])->name('mark-attendance');
        });
        
        // Show activity details (all authenticated users)
        Route::get('/{id}', [NewActivityController::class, 'show'])->name('show');
    });
    */

    // Individual Education Plans (IEP) Management (Teacher, Admin, Supervisor)
    Route::prefix('iep')->name('iep.')->middleware(['role:teacher,admin,supervisor'])->group(function () {
        Route::get('/', [App\Http\Controllers\IepController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\IepController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\IepController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\IepController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\IepController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\IepController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\IepController::class, 'destroy'])->name('destroy');
        
        // IEP Goals Management
        Route::post('/{iepId}/goals', [App\Http\Controllers\IepController::class, 'storeGoal'])->name('goals.store');
        Route::put('/goals/{goalId}/progress', [App\Http\Controllers\IepController::class, 'updateGoalProgress'])->name('goals.update-progress');
    });

    // ENHANCED ASSET MANAGEMENT SYSTEM - Removed duplicate, using main assets route group below


    // Activity Routes moved to main Activity Management section above
    
    // Legacy rehabilitation routes (redirect to new structure)
    Route::prefix('rehabilitation')->name('rehabilitation.')->group(function () {
        Route::get('/categories', function() { return redirect()->route('activities.categories'); });
        Route::get('/categories/{categorySlug}', function($categorySlug) { return redirect()->route('activities.categories.show', $categorySlug); });
    });

    // Staffs Management Routes (updated structure)
    Route::prefix('staffs')->name('staffs.')->group(function () {
        Route::get('/home', [StaffsHomeController::class, 'index'])->name('home');
        Route::get('/profile/{encrypted_id}', [StaffController::class, 'viewProfile'])->name('profile');
        Route::get('/edit/{encrypted_id}', [StaffController::class, 'editProfile'])->name('edit');
        Route::put('/update/{encrypted_id}', [StaffController::class, 'updateProfile'])->name('update');
        Route::get('/schedule/{encrypted_id}', [StaffController::class, 'showSchedule'])->name('schedule');
        Route::get('/attendance/{encrypted_id}', [StaffController::class, 'showAttendance'])->name('attendance');
        Route::get('/activities/{encrypted_id}', [StaffController::class, 'showActivities'])->name('activities');
        Route::get('/trainees/{encrypted_id}', [StaffController::class, 'showTrainees'])->name('trainees');
    });
    
    // Legacy teachershome route (redirect to new structure)
    Route::get('/teachershome', function() { return redirect()->route('staffs.home'); })->name('teachershome');
    Route::get('/updateuser/{id}', [StaffsHomeController::class, 'updateuserpage'])->name('updateuser');
    Route::post('/updateuser/{id}', [StaffsHomeController::class, 'updateuser'])->name('updateuser.post');
    
    // Legacy staff routes (redirect to new encrypted structure)
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/view/{id}', function($id) { return redirect()->route('staffs.profile', ['encrypted_id' => app('App\Traits\HandlesEncryptedIds')->generateEncryptedId($id)]); })->name('view');
        Route::get('/edit/{id}', function($id) { return redirect()->route('staffs.edit', ['encrypted_id' => app('App\Traits\HandlesEncryptedIds')->generateEncryptedId($id)]); })->name('edit');
    });
    
    // Handle plain ID staff edit routes (redirect to encrypted)
    Route::get('/staffs/edit/{id}', function($id) {
        // Check if the ID is numeric (plain ID) vs encrypted
        if (is_numeric($id)) {
            // Generate encrypted ID using the trait
            $handler = new class {
                use \App\Traits\HandlesEncryptedIds;
                public function encrypt($id) {
                    return $this->generateEncryptedId($id);
                }
            };
            $encryptedId = $handler->encrypt($id);
            return redirect()->route('staffs.edit', ['encrypted_id' => $encryptedId]);
        }
        // If it's already encrypted, continue normally
        return app(\App\Http\Controllers\Staff\StaffController::class)->editProfile($id);
    })->middleware(['auth'])->name('staffs.edit.plain');

    // Legacy centres redirect
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->middleware(['centre.access:centre']);
    
    // General centre assets route
    Route::get('/centre/assets', [AssetController::class, 'index'])->name('centre.assets');
    Route::post('/centre/assets', [AssetController::class, 'store'])->name('centre.assets.store');

    // Staff Daily Attendance Management (must be BEFORE generic {id} routes)
    Route::prefix('centres/attendance')->name('centres.attendance.')->middleware(['enhanced.auth'])->group(function () {
        Route::get('/', [StaffAttendanceController::class, 'index'])->name('index');
        Route::post('/mark', [StaffAttendanceController::class, 'markAttendance'])->name('mark');
        Route::get('/user/{encryptedUserId}', [StaffAttendanceController::class, 'getUserAttendance'])->name('user');
        Route::get('/status/{encryptedUserId}', [StaffAttendanceController::class, 'getTodayStatus'])->name('status');
    });

    // Centre
    Route::prefix('centres')->name('centres.')->middleware(['centre.access:centre'])->group(function () {
        Route::get('/home', [CentreController::class, 'index'])->name('index');
        Route::get('/create', [CentreController::class, 'create'])->name('create');
        Route::post('/', [CentreController::class, 'store'])->name('store');
        Route::get('/{id}', [CentreController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CentreController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CentreController::class, 'update'])->name('update');
        Route::delete('/{id}', [CentreController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/assets', [CentreController::class, 'assets'])->name('assets');
        
        // Enhanced centre management routes
        Route::get('/{id}/metrics', [CentreController::class, 'getMetrics'])->name('metrics');
        Route::post('/{id}/statistics/refresh', [CentreController::class, 'refreshStatistics'])->name('statistics.refresh');
    });

    // Asset
    Route::prefix('assets')->name('assets.')->middleware(['centre.access:asset'])->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/reports', [AssetController::class, 'reports'])->name('reports');
        Route::get('/reports/data', [AssetController::class, 'getReportData'])->name('reports.data');
        Route::get('/reports/export', [AssetController::class, 'exportReports'])->name('reports.export');
        Route::get('/maintenance', [AssetController::class, 'maintenance'])->name('maintenance');
        Route::get('/maintenance/filter', [AssetController::class, 'filterMaintenance'])->name('maintenance.filter');
        Route::post('/maintenance/schedule', [AssetController::class, 'scheduleMaintenance'])->name('maintenance.schedule');
        Route::post('/maintenance/{id}/complete', [AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
        Route::post('/maintenance/{id}/reschedule', [AssetController::class, 'rescheduleMaintenance'])->name('maintenance.reschedule');
        Route::get('/movements', [AssetController::class, 'movements'])->name('movements');
        Route::get('/movements/filter', [AssetController::class, 'filterMovements'])->name('movements.filter');
        Route::post('/movements/record', [AssetController::class, 'recordMovement'])->name('movements.record');
        Route::post('/movements/{id}/approve', [AssetController::class, 'approveMovement'])->name('movements.approve');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
        
        // Asset rental/usage routes
        Route::post('/{id}/rent', [AssetController::class, 'rentAsset'])->name('rent');
        Route::post('/{id}/return', [AssetController::class, 'returnAsset'])->name('return');
    });

    // Message
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });

    // Notification
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'getUnread'])->name('unread');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Attendance Management  
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Activity\AttendanceController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Activity\AttendanceController::class, 'store'])->name('store');
        Route::get('/report', [App\Http\Controllers\Activity\AttendanceController::class, 'report'])->name('report');
        Route::get('/trainee/{id}', function($id) { 
            return view('attendance.trainee', compact('id')); 
        })->name('trainee');
    });

    // Enhanced Attendance Management
    Route::prefix('enhanced-attendance')->name('enhanced-attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Activity\AttendanceController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Activity\AttendanceController::class, 'store'])->name('store');
        Route::get('/stats/today', [App\Http\Controllers\Activity\AttendanceController::class, 'getTodayStats'])->name('stats.today');
        Route::get('/session/{id}/form', [App\Http\Controllers\Activity\AttendanceController::class, 'getAttendanceForm'])->name('session.form');
        Route::post('/export', [App\Http\Controllers\Activity\AttendanceController::class, 'export'])->name('export');
    });

    
    // Legacy staff-attendance routes (backward compatibility redirects)
    Route::prefix('staff-attendance')->middleware(['enhanced.auth'])->group(function () {
        Route::get('/', function() { return redirect()->route('centres.attendance.index'); });
        Route::post('/mark', function() { return redirect()->route('centres.attendance.index'); });
        Route::get('/user/{userId}', function($userId) { return redirect()->route('centres.attendance.user', encrypt($userId)); });
        Route::get('/status/{userId}', function($userId) { return redirect()->route('centres.attendance.status', encrypt($userId)); });
    });


    // New Letter Management System - Complete Rewrite with Modern Architecture
    Route::prefix('letters')->name('letters.')->group(function () {
        Route::get('/', [ModernLetterController::class, 'dashboard'])->name('dashboard');
        Route::get('/index', [LetterTemplateController::class, 'index'])->name('index');
        Route::post('/generate', [ModernLetterController::class, 'generate'])->name('generate');
        Route::get('/{id}/show', [ModernLetterController::class, 'show'])->name('show');
        Route::get('/{id}/download', [ModernLetterController::class, 'download'])->name('download');
    });
    
    // Legacy Letter Routes (for backward compatibility)
    Route::get('/letters-old', [LetterController::class, 'index'])->name('letters.old.index');
    Route::get('/letters-old/create', [LetterController::class, 'create'])->name('letters.old.create');
    
    // Letter Archive with inline HTML (Direct Fix)
    Route::get('/letters-archive', function() {
        $letters = \App\Models\Letter::where(function($query) {
            if (session('role') !== 'admin') {
                $query->where('created_by', session('id'));
            }
        })->orderBy('created_at', 'desc')->limit(50)->get();
        
        return response('<!DOCTYPE html>
<html>
<head>
    <title>Letter Archive - CREAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                    <h2><i class="fas fa-archive"></i> Letter Archive</h2>
                    <div>
                        <a href="' . route('profile') . '#letters-tab" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Generate New Letter
                        </a>
                        <a href="' . route('dashboard') . '" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Reference</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-user"></i> Recipient</th>
                                <th><i class="fas fa-tag"></i> Subject</th>
                                <th><i class="fas fa-user-tie"></i> Generated By</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>' . 
                        collect($letters)->map(function($letter) {
                            $letterData = is_array($letter->letter_data) ? $letter->letter_data : json_decode($letter->letter_data, true);
                            return '<tr>
                                <td><code>' . $letter->letter_reference . '</code></td>
                                <td>' . \Carbon\Carbon::parse($letter->letter_date)->format('d M Y') . '</td>
                                <td>' . ($letterData['recipient_name'] ?? 'Unknown') . '</td>
                                <td>' . \Str::limit($letter->letter_subject, 50) . '</td>
                                <td>' . ($letterData['generated_by_name'] ?? 'Unknown') . '</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="' . route('profile.letter.download', $letter->id) . '" 
                                           class="btn btn-sm btn-primary" 
                                           target="_blank" 
                                           title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>';
                        })->join('') . 
                        '</tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Total Letter:</strong> ' . count($letters) . ' 
                        | <strong>User::</strong> ' . session('name') . ' 
                        | <strong>Role:</strong> ' . ucfirst(session('role')) . '
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>');
    })->name('letters.archive');
});

/*
|--------------------------------------------------------------------------
| TRAINEE MODULE ROUTES
|--------------------------------------------------------------------------
*/

// Consolidated Trainee Management Routes - moved to avoid conflicts with /trainees/home

// Enhanced Trainee Management Routes - Functionality moved to main trainee routes
// Legacy routes disabled - use main /trainees/* routes instead

// Legacy trainee routes (backward compatibility) - MUST come before dynamic routes
Route::middleware(['auth', 'centre.access:trainee'])->group(function () {
    Route::get('/traineeshome', function() { return redirect()->route('trainees.home'); });
    
    // Trainee Profile Routes - specific routes first to avoid conflicts
    Route::get('/traineeprofile/{encrypted_id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{encrypted_id}/edit', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::put('/traineeprofile/{encrypted_id}', [TraineeProfileController::class, 'update'])->name('traineeprofile.update');
    Route::put('/traineeprofile/{encrypted_id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile');
    Route::post('/traineeprofile/{encrypted_id}/progress', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.progress');
    Route::post('/traineeprofile/{encrypted_id}/attendance', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.attendance');
    Route::post('/traineeprofile/{encrypted_id}/activity', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/traineeprofile/{encrypted_id}/download', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');
    Route::delete('/traineeprofile/{encrypted_id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    
    Route::get('/traineesregistrationpage', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::post('/traineesregistrationstore', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
    Route::post('/validateEmail', [TraineeRegistrationController::class, 'validateEmail'])->name('validateEmail');
});

// Trainee Management Routes (updated structure) - AFTER legacy routes to avoid conflicts
Route::middleware(['auth', 'centre.access:trainee'])->prefix('trainees')->name('trainees.')->group(function () {
    Route::get('/home', [TraineeHomeController::class, 'index'])->name('home');
    Route::get('/index', [TraineeHomeController::class, 'index'])->name('index'); // Alternative route
    Route::get('/create', [TraineeRegistrationController::class, 'index'])->name('create');
    Route::get('/register', [TraineeRegistrationController::class, 'index'])->name('register');
    Route::post('/', [TraineeRegistrationController::class, 'store'])->name('store');
    Route::get('/schedule/{encrypted_id}', [TraineeHomeController::class, 'schedule'])->name('schedule'); // Schedule route with encrypted ID
    Route::get('/attendance/{encrypted_id}', [TraineeHomeController::class, 'attendance'])->name('attendance'); // Attendance route with encrypted ID
    Route::post('/attendance/{encrypted_id}/mark', [TraineeHomeController::class, 'markAttendance'])->name('attendance.mark'); // Mark attendance route
    
    // Academic Progress Routes
    Route::get('/progress/{encrypted_id}', [App\Http\Controllers\Trainee\TraineeProgressController::class, 'show'])->name('progress.show');
    Route::get('/progress/{encrypted_id}/schedule', [App\Http\Controllers\Trainee\TraineeProgressController::class, 'weeklySchedule'])->name('progress.schedule');
    
    Route::get('/{encrypted_id}/edit', [TraineeProfileController::class, 'edit'])->name('edit'); // Edit route with encrypted ID
    Route::put('/{encrypted_id}', [TraineeProfileController::class, 'update'])->name('update'); // Update route with encrypted ID
    Route::get('/{encrypted_id}', [TraineeHomeController::class, 'show'])->name('show'); // Show route with encrypted ID (must be last)
    // Note: Legacy profile routes use /traineeprofile/{id} for backward compatibility
});

// Legacy asset route
Route::middleware(['auth'])->group(function () {
    // Asset routes moved to main assets prefix group below
    Route::get('/assetmanagementpage', [AssetController::class, 'index'])->name('assetmanagementpage');
    Route::get('/schedulehomepage', function () { 
        $courses = collect([]); 
        return view('activities.schedulehome', compact('courses')); 
    })->name('schedulehomepage');
    Route::get('/aboutus', function () { return view('aboutus'); })->name('aboutus');
});

/*
|--------------------------------------------------------------------------
| ROLE-BASED DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Admin notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');

    // Centre management
    Route::prefix('centres')->name('admin.centres.')->group(function () {
        Route::get('/', [CentreController::class, 'index'])->name('index');
        Route::get('/create', [CentreController::class, 'create'])->name('create');
        Route::post('/', [CentreController::class, 'store'])->name('store');
        Route::get('/{id}', [CentreController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CentreController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CentreController::class, 'update'])->name('update');
        Route::delete('/{id}', [CentreController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/assets', [CentreController::class, 'assets'])->name('assets');
    });

    // Asset management
    Route::prefix('assets')->name('admin.assets.')->middleware(['centre.access:asset'])->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/reports', [AssetController::class, 'reports'])->name('reports');
        Route::get('/reports/data', [AssetController::class, 'getReportData'])->name('reports.data');
        Route::get('/reports/export', [AssetController::class, 'exportReports'])->name('reports.export');
        Route::get('/maintenance', [AssetController::class, 'maintenance'])->name('maintenance');
        Route::get('/maintenance/filter', [AssetController::class, 'filterMaintenance'])->name('maintenance.filter');
        Route::post('/maintenance/schedule', [AssetController::class, 'scheduleMaintenance'])->name('maintenance.schedule');
        Route::post('/maintenance/{id}/complete', [AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
        Route::post('/maintenance/{id}/reschedule', [AssetController::class, 'rescheduleMaintenance'])->name('maintenance.reschedule');
        Route::get('/movements', [AssetController::class, 'movements'])->name('movements');
        Route::get('/movements/filter', [AssetController::class, 'filterMovements'])->name('movements.filter');
        Route::post('/movements/record', [AssetController::class, 'recordMovement'])->name('movements.record');
        Route::post('/movements/{id}/approve', [AssetController::class, 'approveMovement'])->name('movements.approve');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
    });

    // Letter Management Routes (Admin Only)
    // Letter template management routes (admin only)
    Route::prefix('admin/letter-templates')->name('admin.letter-templates.')->group(function () {
        Route::get('/', [LetterTemplateController::class, 'index'])->name('index');
        Route::post('/update', [LetterTemplateController::class, 'store'])->name('update');
    });
    
    // Legacy letter routes for backward compatibility
    Route::prefix('admin/letters')->name('admin.letters.')->group(function () {
        Route::get('/', [LetterController::class, 'index'])->name('index');
        Route::post('/template', [LetterController::class, 'updateTemplate'])->name('template');
        Route::post('/generate', [LetterController::class, 'generateLetter'])->name('generate');
        Route::post('/preview', [LetterController::class, 'preview'])->name('preview');
        Route::get('/history', [LetterController::class, 'history'])->name('history');
        Route::get('/download/{id}', [LetterController::class, 'download'])->name('download');
        Route::get('/new-reference', [LetterController::class, 'newReference'])->name('newReference');
        Route::delete('/{id}', [LetterController::class, 'destroy'])->name('destroy');
    });

    // Redirect routes to common routes
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('admin.centres');
    Route::get('/assets', function() { return redirect()->route('assets'); })->name('admin.assets');
    Route::get('/activities', function() { return redirect()->route('activities.home'); })->name('admin.activities');
    Route::get('/trainees', function() { return redirect()->route('trainees.home'); })->name('admin.trainees');
    Route::get('/users', function() { return redirect()->route('staffs.home'); })->name('admin.users');
});

// Supervisor Routes
Route::prefix('supervisor')->middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('supervisor.notifications');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('supervisor.centres');
    Route::get('/activities', function() { return redirect()->route('activities.home'); })->name('supervisor.activities');
    Route::get('/trainees', function() { return redirect()->route('trainees.home'); })->name('supervisor.trainees');
    Route::get('/users', function() { return redirect()->route('staffs.home'); })->name('supervisor.users');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('teacher.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('teacher.notifications');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('teacher.centres');
    Route::get('/activities', function() { return redirect()->route('activities.home'); })->name('teacher.activities');
    Route::get('/trainees', function() { return redirect()->route('trainees.home'); })->name('teacher.trainees');
    Route::get('/schedule', [ClassController::class, 'schedule'])->name('teacher.schedule');
});

// AJK Routes
Route::prefix('ajk')->middleware(['auth', 'role:ajk'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('ajk.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('ajk.notifications');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('ajk.centres');
    Route::get('/activities', function() { return redirect()->route('activities.home'); })->name('ajk.activities');
    Route::get('/trainees', function() { return redirect()->route('trainees.home'); })->name('ajk.trainees');
});

// Trainee Routes
Route::prefix('trainee')->middleware(['auth', 'role:trainee'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('trainee.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('trainee.notifications');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('trainee.centres');
    Route::get('/activities', function() { return redirect()->route('activities.home'); })->name('trainee.activities');
});

// Parent Routes
Route::prefix('parent')->middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Trainee\ParentPortalController::class, 'dashboard'])->name('parent.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('parent.notifications');
});

/*
|--------------------------------------------------------------------------
| API ROUTES FOR AJAX CALLS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/activities', [ActivityController::class, 'apiIndex'])->name('activities');
    Route::get('/activities/categories', [ActivityController::class, 'getCategories'])->name('activities.categories');
    Route::post('/activities/check-conflicts', [ActivityController::class, 'checkScheduleConflicts'])->name('activities.check-conflicts');
    Route::get('/assets', [AssetController::class, 'getAssetsJson'])->name('assets');
    
    // Centre-based API endpoints for activity creation
    Route::get('/centres/{centreId}/instructors', [ActivityController::class, 'getInstructors'])->name('centres.instructors');
    Route::get('/centres/{centreId}/trainees', [ActivityController::class, 'getTrainees'])->name('centres.trainees');
    Route::get('/centres/{centreId}/trainees/filtered/{categoryId?}', [ActivityController::class, 'getFilteredTrainees'])->name('centres.trainees.filtered');
});

/*
|--------------------------------------------------------------------------
| PRODUCTION ROUTES
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PRODUCTION APPLICATION ROUTES
|--------------------------------------------------------------------------
*/

// Removed SafeDashboardController reference - functionality moved to main DashboardController

/*
|--------------------------------------------------------------------------
| SEARCH ROUTES
|--------------------------------------------------------------------------
*/

// Search routes accessible to authenticated users (using session-based auth)
Route::middleware(['web'])->group(function () {
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');
    Route::post('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search.post');
});

// Test route for debugging dashboard issues
Route::get('/test-dashboard', function() {
    session([
        'id' => 1, 
        'role' => 'admin', 
        'centre_id' => '01', 
        'name' => 'Muhammad Syafiq bin Moh'
    ]);
    
    $dashboardController = new App\Http\Controllers\Dashboard\DashboardController();
    $request = new Illuminate\Http\Request();
    
    try {
        $response = $dashboardController->index($request);
        
        if ($response instanceof Illuminate\View\View) {
            $data = $response->getData();
            
            return response()->json([
                'status' => 'success',
                'what_you_should_see' => [
                    'General Tab' => [
                        'Total Users' => ($data['stats_flat']['total_users'] ?? 0),
                        'Active Trainees' => ($data['stats_flat']['total_trainees'] ?? 0),
                        'Active Programs' => ($data['stats_flat']['total_activities'] ?? 0),
                        'Active Centres' => ($data['stats_flat']['active_centres'] ?? 0)
                    ],
                    'Personal Tab' => [
                        'My Activities' => ($data['personal_stats']['user_activities'] ?? 0),
                        'Weekly Sessions' => ($data['personal_stats']['weekly_sessions'] ?? 0),
                        'Completion Rate' => ($data['personal_stats']['completion_rate'] ?? 0) . '%',
                        'Average Attendance' => ($data['personal_stats']['avg_attendance'] ?? 0) . '%'
                    ],
                    'My Schedule' => [
                        'Calendar Events' => count($data['calendar_events'] ?? []) . ' upcoming sessions'
                    ]
                ],
                'css_check' => [
                    'dashboard_widgets_css_exists' => file_exists(public_path('css/dashboard-widgets.css'))
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Dashboard not returning view']);
        }
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| FALLBACK ROUTE
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    if (session('id') && session('role')) {
        $role = session('role');
        return redirect()->route("{$role}.dashboard")
            ->with('warning', 'The page you were looking for could not be found.');
    }
    return redirect()->route('home')
        ->with('warning', 'The page you were looking for could not be found.');
});