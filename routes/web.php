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
    
    // Enhanced logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
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
        /*
        Route::get('/updates', [App\Http\Controllers\Dashboard\DashboardController::class, 'getUpdates'])->name('updates');
        Route::post('/refresh-stats', [App\Http\Controllers\Dashboard\DashboardController::class, 'refreshStats'])->name('refresh-stats');
        Route::get('/widget/{widget}', [App\Http\Controllers\Dashboard\DashboardController::class, 'getWidget'])->name('widget');
        Route::get('/export/{format?}', [App\Http\Controllers\Dashboard\DashboardController::class, 'export'])->name('export');
        Route::post('/clear-cache', [App\Http\Controllers\Dashboard\DashboardController::class, 'clearCache'])->name('clear-cache');
        Route::get('/mobile', [App\Http\Controllers\Dashboard\DashboardController::class, 'mobile'])->name('mobile');
        */
    });
    
    // Profile management
    // Profile management routes
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    
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
    Route::post('/profile/letters/generate', [LetterController::class, 'store'])->name('profile.letters.generate');
    Route::get('/profile/letters/{letter}/preview', [LetterController::class, 'show'])->name('profile.letters.preview');
    Route::get('/profile/letters/{letter}/download', [LetterController::class, 'download'])->name('profile.letters.download');


    // Activity Management
    Route::prefix('activities')->name('activities.')->middleware(['centre.access:activity'])->group(function () {
        Route::get('/', function() { return redirect()->route('activities.home'); }); // Legacy redirect
        Route::get('/home', [ActivityController::class, 'index'])->name('home'); // New structure
        Route::get('/modern-home', [ActivityController::class, 'modernHome'])->name('modern-home'); // Modern activities homepage
        Route::get('/categories', [ActivityController::class, 'categories'])->name('categories');
        Route::get('/categories/{categorySlug}', [ActivityController::class, 'categoryShow'])->name('categories.show');
        Route::get('/schedule', [ActivityController::class, 'scheduleIndex'])->name('schedule');
        
        // Admin and Supervisor routes
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::get('/create', [ActivityController::class, 'create'])->name('create');
            Route::post('/', [ActivityController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ActivityController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ActivityController::class, 'update'])->name('update');
            Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/sessions', [ActivityController::class, 'sessions'])->name('sessions');
            Route::post('/{id}/sessions', [ActivityController::class, 'createSession'])->name('sessions.create');
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
            Route::get('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'markAttendance'])->name('attendance');
            Route::post('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/{activityId}/sessions/{sessionId}/enrollments', [ActivityController::class, 'manageEnrollments'])->name('enrollments');
            Route::post('/{activityId}/sessions/{sessionId}/enroll', [ActivityController::class, 'enrollTrainees'])->name('enroll.legacy');
            Route::post('/{activityId}/sessions/{sessionId}/enrollments/add', [ActivityController::class, 'addEnrollment'])->name('enrollments.add');
        });
        
        // Schedule routes (moved under activities)
        Route::get('/schedule', [ActivityController::class, 'scheduleIndex'])->name('schedule');
        Route::get('/schedule/weekly', [ActivityController::class, 'weeklySchedule'])->name('schedule.weekly');
        Route::get('/schedule/teacher/{teacherId}', [ActivityController::class, 'teacherSchedule'])->name('schedule.teacher');
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

    // Staff Daily Attendance Management
    Route::prefix('staff-attendance')->name('staff-attendance.')->middleware(['auth'])->group(function () {
        Route::get('/', [StaffAttendanceController::class, 'index'])->name('index');
        Route::post('/mark', [StaffAttendanceController::class, 'markAttendance'])->name('mark');
        Route::get('/user/{userId}', [StaffAttendanceController::class, 'getUserAttendance'])->name('user');
        Route::get('/status/{userId}', [StaffAttendanceController::class, 'getTodayStatus'])->name('status');
    });


    // New Letter Management System - Complete Rewrite with Modern Architecture
    Route::prefix('letters')->name('letters.')->group(function () {
        Route::get('/', [ModernLetterController::class, 'dashboard'])->name('dashboard');
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
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{id}/edit', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::put('/traineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('traineeprofile.update');
    Route::put('/traineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile');
    Route::post('/traineeprofile/{id}/progress', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.progress');
    Route::post('/traineeprofile/{id}/attendance', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.attendance');
    Route::post('/traineeprofile/{id}/activity', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/traineeprofile/{id}/download', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');
    Route::delete('/traineeprofile/{id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    
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
    Route::get('/{id}/profile', [TraineeProfileController::class, 'index'])->name('profile'); // Add missing profile route
    Route::get('/{id}/schedule', [TraineeHomeController::class, 'schedule'])->name('schedule'); // Schedule route
    Route::get('/{id}/attendance', [TraineeHomeController::class, 'attendance'])->name('attendance'); // Attendance route
    Route::get('/{encrypted_id}/edit', [TraineeProfileController::class, 'edit'])->name('edit'); // Edit route with encrypted ID
    Route::put('/{id}', [TraineeProfileController::class, 'update'])->name('update'); // Add missing update route
    Route::get('/{id}', [TraineeHomeController::class, 'show'])->name('show'); // Show route (must be last)
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
    Route::get('/assets', [AssetController::class, 'getAssetsJson'])->name('assets');
    
    // Centre-based API endpoints for activity creation
    Route::get('/centres/{centreId}/instructors', [ActivityController::class, 'getInstructors'])->name('centres.instructors');
    Route::get('/centres/{centreId}/trainees', [ActivityController::class, 'getTrainees'])->name('centres.trainees');
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