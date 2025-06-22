<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Main Controllers
use App\Http\Controllers\MainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\TeachersHomeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VolunteerController;

// User Management Controllers
use App\Http\Controllers\UserController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AJKController;

// Trainee Management Controllers
use App\Http\Controllers\TraineeHomeController;
use App\Http\Controllers\TraineeProfileController;
use App\Http\Controllers\TraineeRegistrationController;
use App\Http\Controllers\TraineeActivityController;
use App\Http\Controllers\TraineeManagementController;

// Activity and Resource Controllers
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EventController;

// Report and Admin Controllers
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

// Notification and Communication Controllers
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;

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
    // Login routes (maintain both old and new patterns)
    Route::get('/auth/login', [MainController::class, 'login'])->name('auth.loginpage');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/auth/check', [MainController::class, 'check'])->name('auth.check');
    Route::post('/loginuser', [MainController::class, 'check'])->name('loginuser');

    // Registration routes
    Route::get('/auth/register', [MainController::class, 'registration'])->name('auth.registerpage');
    Route::get('/registration', [MainController::class, 'registration'])->name('registration');
    Route::post('/auth/save', [MainController::class, 'save'])->name('auth.save');
    Route::post('/registeruser', [MainController::class, 'save'])->name('registeruser');

    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])
        ->name('auth.forgotpassword')->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'submitForgotPasswordForm'])
        ->name('auth.processforgotpassword')->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])
        ->name('auth.resetpassword')->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])
        ->name('auth.updatepassword')->name('password.update');
});

// Logout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::post('/logout', [MainController::class, 'logout'])->name('logout.post');
});

/*
|--------------------------------------------------------------------------
| COMMON AUTHENTICATED ROUTES (Available to All Staff Roles)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API routes
    Route::prefix('dashboard/api')->name('dashboard.')->group(function () {
        Route::get('/refresh', [DashboardController::class, 'refresh'])->name('refresh');
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/charts', [DashboardController::class, 'getCharts'])->name('charts');
        Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications');
        Route::post('/customize', [DashboardController::class, 'saveCustomization'])->name('customize');
        Route::post('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
        Route::get('/health', [DashboardController::class, 'health'])->name('health');
    });
    
    // Search functionality
    Route::get('/search', [MainController::class, 'search'])->name('search');

    // Profile management (unified for all staff)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserProfileController::class, 'showProfile'])->name('show');
        Route::post('/update', [UserProfileController::class, 'updateProfile'])->name('update');
        Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('password');
        Route::post('/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('avatar');
    });

    // Legacy profile route
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');

    // Activity Management (Enhanced Structure)
    Route::prefix('activities')->name('activities.')->group(function () {
        // Common routes for all authenticated users
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/{id}', [ActivityController::class, 'show'])->name('show');
        
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
        
        // Teacher routes (can mark attendance for their sessions)
        Route::middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'markAttendance'])->name('attendance');
            Route::post('/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/{activityId}/sessions/{sessionId}/enrollments', [ActivityController::class, 'manageEnrollments'])->name('enrollments');
            Route::post('/{activityId}/sessions/{sessionId}/enroll', [ActivityController::class, 'enrollTrainees'])->name('enroll');
        });

        // Activity participation
        Route::post('/{id}/register', [ActivityController::class, 'registerParticipation'])->name('register');
        Route::delete('/{id}/unregister', [ActivityController::class, 'unregisterParticipation'])->name('unregister');
    });

    // Legacy activity routes (backward compatibility)
    Route::get('/sessions/{id}/attendance', [ActivityController::class, 'markAttendance'])->name('sessions.attendance');
    Route::post('/sessions/{id}/attendance', [ActivityController::class, 'storeAttendance'])->name('sessions.attendance.store');

    // Rehabilitation Routes
    Route::prefix('rehabilitation')->name('rehabilitation.')->group(function () {
        Route::get('/categories', [ActivityController::class, 'categories'])->name('categories');
        Route::get('/categories/{category}', [ActivityController::class, 'categoryShow'])->name('categories.show');
        
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::get('/activities/create', [ActivityController::class, 'createActivity'])->name('activities.create');
            Route::post('/activities', [ActivityController::class, 'storeActivity'])->name('activities.store');
            Route::get('/activities/{id}/edit', [ActivityController::class, 'editActivity'])->name('activities.edit');
            Route::put('/activities/{id}', [ActivityController::class, 'updateActivity'])->name('activities.update');
            Route::delete('/activities/{id}', [ActivityController::class, 'destroyActivity'])->name('activities.destroy');
        });
        
        Route::get('/activities/{id}', [ActivityController::class, 'showActivity'])->name('activities.show');
    });

    // Teachers Directory (centralized staff directory)
    Route::get('/teachershome', [TeachersHomeController::class, 'index'])->name('teachershome');
    Route::get('/updateuserprofile/{id}', [TeachersHomeController::class, 'updateuserpage'])->name('updateuser');
    Route::post('/updateuser/{id}', [TeachersHomeController::class, 'updateuser'])->name('updateuserpost');

    // Centres (common access)
    Route::prefix('centres')->name('centres.')->group(function () {
        Route::get('/', [CentreController::class, 'index'])->name('index');
        Route::get('/{id}', [CentreController::class, 'show'])->name('view')->name('show');
        Route::get('/{id}/assets', [CentreController::class, 'assets'])->name('assets');
    });

    // Legacy centre routes
    Route::get('/centres', [CentreController::class, 'index'])->name('centres');

    // Message System
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::get('/{id}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::post('/{id}/mark-read', [MessageController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });

    // Notification System
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'getUnread'])->name('unread');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Fallback routes for common menus
    Route::redirect('/messages', '/messages/index')->name('messages');
    Route::redirect('/notifications', '/notifications/index')->name('notifications');

    // User Management (Admin and Supervisor only)
    Route::middleware(['role:admin,supervisor'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [MainController::class, 'manageUsers'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminController::class, 'editUser'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'updateUser'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'deleteUser'])->name('destroy');
    });

    // Development/Testing route
    Route::get('/test-email', function() {
        try {
            \Mail::raw('Test email content', function($message) {
                $message->to('test@example.com')->subject('Test Email from CREAMS');
            });
            return "Email sent! Check storage/logs/laravel.log";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });
});

/*
|--------------------------------------------------------------------------
| TRAINEE MODULE ROUTES
|--------------------------------------------------------------------------
*/

// Trainee Management (for staff)
Route::middleware(['auth'])->prefix('trainees')->name('trainees.')->group(function () {
    // Enhanced trainee management with both old and new patterns
    Route::get('/', [TraineeHomeController::class, 'index'])->name('index');
    Route::get('/filter', [TraineeHomeController::class, 'filter'])->name('filter');
    Route::get('/create', [TraineeRegistrationController::class, 'index'])->name('create');
    Route::post('/', [TraineeRegistrationController::class, 'store'])->name('store');
    Route::get('/{id}', [TraineeProfileController::class, 'index'])->name('show');
    Route::get('/{id}/edit', [TraineeProfileController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TraineeProfileController::class, 'update'])->name('update');
    Route::delete('/{id}', [TraineeProfileController::class, 'destroy'])->name('destroy');
});

// Legacy trainee routes (backward compatibility)
Route::middleware(['auth'])->group(function () {
    Route::get('/traineeshome', [TraineeHomeController::class, 'index'])->name('traineeshome');
    Route::get('/trainee/profile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index']); // Legacy
    Route::get('/trainee/edit/{id}', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::post('/trainee/update/{id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile');
    Route::post('/updatetraineeprofile/{id}', [TraineeProfileController::class, 'update']); // Legacy

    // Trainee profile actions
    Route::delete('/trainee/delete/{id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    Route::post('/trainee/progress/{id}', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.updateProgress');
    Route::post('/trainee/attendance/{id}', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.recordAttendance');
    Route::post('/trainee/activity/{id}', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/trainee/download/{id}', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');

    // Trainee Registration
    Route::get('/trainee/register', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::get('/traineesregistration', [TraineeRegistrationController::class, 'index']); // Legacy
    Route::post('/trainee/register', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
    Route::post('/traineesregistration/store', [TraineeRegistrationController::class, 'store']); // Legacy
    Route::post('/trainee/validate-email', [TraineeRegistrationController::class, 'validateEmail'])->name('validateEmail');

    // Trainee Activities
    Route::get('/trainee/activities', [TraineeActivityController::class, 'index'])->name('traineeactivity');
    Route::get('/traineeactivity', [TraineeActivityController::class, 'index']); // Legacy
    Route::get('/trainee/activities/{id}', [TraineeActivityController::class, 'traineeActivities'])->name('traineeactivity.trainee');
    Route::post('/trainee/activities', [TraineeActivityController::class, 'store'])->name('traineeactivity.store');
    Route::post('/traineeactivity/store', [TraineeActivityController::class, 'store']); // Legacy
    Route::get('/trainee/activities/edit/{id}', [TraineeActivityController::class, 'edit'])->name('traineeactivity.edit');
    Route::put('/trainee/activities/update/{id}', [TraineeActivityController::class, 'update'])->name('traineeactivity.update');
    Route::post('/traineeactivity/update/{id}', [TraineeActivityController::class, 'update']); // Legacy
    Route::delete('/trainee/activities/delete/{id}', [TraineeActivityController::class, 'destroy'])->name('traineeactivity.destroy');
    Route::delete('/traineeactivity/delete/{id}', [TraineeActivityController::class, 'destroy']); // Legacy
    Route::get('/traineeactivity/details/{id}', [TraineeActivityController::class, 'getActivityDetails'])->name('traineeactivity.details');
});

// Separate Trainee Authentication (New Feature)
Route::prefix('trainee')->name('trainee.')->group(function () {
    Route::get('/login', [TraineeController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [TraineeController::class, 'login'])->name('login.submit');
    Route::get('/register', [TraineeController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [TraineeController::class, 'register'])->name('register.submit');
    
    Route::middleware(['trainee.auth'])->group(function () {
        Route::get('/home', [TraineeController::class, 'home'])->name('home');
        Route::get('/profile', [TraineeProfileController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [TraineeProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [TraineeProfileController::class, 'update'])->name('profile.update');
        Route::get('/activities', [TraineeController::class, 'activities'])->name('activities');
        Route::get('/schedule', [TraineeController::class, 'schedule'])->name('schedule');
        Route::post('/logout', [TraineeController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| ROLE-BASED DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Contact & Volunteer Management
    Route::get('/contacts', [ContactController::class, 'getMessages'])->name('admin.contacts.index');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('admin.contacts.show');
    Route::post('/contacts/{id}/status', [ContactController::class, 'updateStatus'])->name('admin.contacts.update-status');
    Route::get('/volunteers', [VolunteerController::class, 'getApplications'])->name('admin.volunteers.index');
    Route::get('/volunteers/{id}', [VolunteerController::class, 'show'])->name('admin.volunteers.show');
    Route::post('/volunteers/{id}/status', [VolunteerController::class, 'updateStatus'])->name('admin.volunteers.update-status');

    // Redirect to common routes
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('admin.trainees');
    Route::get('/trainee/create', function() { return redirect()->route('traineesregistrationpage'); })->name('admin.trainee.create');
    Route::get('/trainee/view/{id}', function($id) { return redirect()->route('traineeprofile', ['id' => $id]); })->name('admin.trainee.view');
    Route::get('/trainee/edit/{id}', function($id) { return redirect()->route('traineeprofile.edit', ['id' => $id]); })->name('admin.trainee.edit');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('admin.users');

    // User CRUD operations
    Route::get('/user/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/user/store', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/user/view/{id}', [AdminController::class, 'viewUser'])->name('admin.user.view');
    Route::get('/user/edit/{id}', [AdminController::class, 'editUser'])->name('admin.user.edit');
    Route::post('/user/update/{id}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::post('/user/reset-password/{id}', [AdminController::class, 'resetUserPassword'])->name('admin.user.reset-password');
    Route::post('/user/change-status/{id}', [AdminController::class, 'changeUserStatus'])->name('admin.user.change-status');
    Route::delete('/user/delete/{id}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
    Route::post('/user/send-message/{id}', [AdminController::class, 'sendUserMessage'])->name('admin.user.send-message');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkUserAction'])->name('admin.users.bulk-action');

    // Activities management
    Route::get('/activities', [ActivityController::class, 'index'])->name('admin.activities');
    Route::get('/activity/create', [ActivityController::class, 'create'])->name('admin.activity.create');
    Route::post('/activity/store', [ActivityController::class, 'store'])->name('admin.activity.store');
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('admin.activity.view');
    Route::get('/activity/edit/{id}', [ActivityController::class, 'edit'])->name('admin.activity.edit');
    Route::post('/activity/update/{id}', [ActivityController::class, 'update'])->name('admin.activity.update');
    Route::delete('/activity/delete/{id}', [ActivityController::class, 'destroy'])->name('admin.activity.delete');

    // Centres management
    Route::get('/centres', [CentreController::class, 'index'])->name('admin.centres');
    Route::get('/centre/create', [CentreController::class, 'create'])->name('admin.centre.create');
    Route::post('/centre/store', [CentreController::class, 'store'])->name('admin.centre.store');
    Route::get('/centre/view/{id}', [CentreController::class, 'show'])->name('admin.centre.view');
    Route::get('/centre/edit/{id}', [CentreController::class, 'edit'])->name('admin.centre.edit');
    Route::post('/centre/update/{id}', [CentreController::class, 'update'])->name('admin.centre.update');
    Route::delete('/centre/delete/{id}', [CentreController::class, 'destroy'])->name('admin.centre.delete');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('admin.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('admin.centres.assets');
    Route::get('/centre/{id}/assets', [CentreController::class, 'assets'])->name('admin.centre.assets');

    // Assets management
    Route::get('/assets', [AssetController::class, 'index'])->name('admin.assets');
    Route::get('/asset/create', [AssetController::class, 'create'])->name('admin.asset.create');
    Route::post('/asset/store', [AssetController::class, 'store'])->name('admin.asset.store');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('admin.asset.view');
    Route::get('/asset/edit/{id}', [AssetController::class, 'edit'])->name('admin.asset.edit');
    Route::post('/asset/update/{id}', [AssetController::class, 'update'])->name('admin.asset.update');
    Route::delete('/asset/delete/{id}', [AssetController::class, 'destroy'])->name('admin.asset.delete');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('admin.assets.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('admin.report.generate');
    Route::post('/report/export', [ReportController::class, 'export'])->name('admin.report.export');

    // System
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/backup', [AdminController::class, 'createBackup'])->name('admin.settings.backup');
    Route::post('/settings/restore', [AdminController::class, 'restoreBackup'])->name('admin.settings.restore');

    // Rehabilitation redirects
    Route::get('/rehabilitation', function() { return redirect()->route('rehabilitation.categories'); })->name('admin.rehabilitation');
});

// Supervisor Routes
Route::prefix('supervisor')->middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('supervisor.dashboard');

    // Redirect to common routes
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('supervisor.trainees');
    Route::get('/trainee/view/{id}', function($id) { return redirect()->route('traineeprofile', ['id' => $id]); })->name('supervisor.trainee.view');
    Route::get('/trainee/edit/{id}', function($id) { return redirect()->route('traineeprofile.edit', ['id' => $id]); })->name('supervisor.trainee.edit');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('supervisor.users');

    // User management
    Route::get('/user/view/{id}', [SupervisorController::class, 'viewUser'])->name('supervisor.user.view');
    Route::get('/user/edit/{id}', [SupervisorController::class, 'editUser'])->name('supervisor.user.edit');

    // Centres management
    Route::get('/centres', [SupervisorController::class, 'centres'])->name('supervisor.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('supervisor.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('supervisor.centres.assets');
    Route::get('/centre/{id}', [CentreController::class, 'show'])->name('supervisor.centre.view');

    // Assets management
    Route::get('/assets', [SupervisorController::class, 'assets'])->name('supervisor.assets');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('supervisor.assets.show');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('supervisor.asset.view');

    // Teachers management
    Route::get('/teachers', [SupervisorController::class, 'manageTeachers'])->name('supervisor.teachers');
    Route::get('/teacher/view/{id}', [SupervisorController::class, 'viewTeacher'])->name('supervisor.teacher.view');
    Route::get('/teacher/edit/{id}', [SupervisorController::class, 'editTeacher'])->name('supervisor.teacher.edit');
    Route::post('/teacher/update/{id}', [SupervisorController::class, 'updateTeacher'])->name('supervisor.teacher.update');
    Route::post('/teacher/change-status/{id}', [SupervisorController::class, 'changeTeacherStatus'])->name('supervisor.teacher.change-status');

    // Activities
    Route::get('/activities', [SupervisorController::class, 'activities'])->name('supervisor.activities');
    Route::get('/activity/view/{id}', [SupervisorController::class, 'viewActivity'])->name('supervisor.activity.view');
    Route::get('/activity/edit/{id}', [SupervisorController::class, 'editActivity'])->name('supervisor.activity.edit');
    Route::post('/activity/update/{id}', [SupervisorController::class, 'updateActivity'])->name('supervisor.activity.update');

    // Reports
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('supervisor.reports');
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('supervisor.report.generate');
    Route::post('/report/export', [ReportController::class, 'export'])->name('supervisor.report.export');

    // Settings & Notifications
    Route::get('/settings', [SupervisorController::class, 'settings'])->name('supervisor.settings');
    Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('supervisor.notifications');
    Route::post('/notifications/mark-read', [SupervisorController::class, 'markNotificationsRead'])->name('supervisor.notifications.mark-read');

    // Rehabilitation redirects
    Route::get('/rehabilitation', function() { return redirect()->route('rehabilitation.categories'); })->name('supervisor.rehabilitation');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('teacher.dashboard');

    // Redirect to common routes
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('teacher.trainees');
    Route::get('/trainee/view/{id}', function($id) { return redirect()->route('traineeprofile', ['id' => $id]); })->name('teacher.trainee.view');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('teacher.users');

    Route::get('/user/view/{id}', [TeacherController::class, 'viewUser'])->name('teacher.user.view');

    // Centres management
    Route::get('/centres', [TeacherController::class, 'centres'])->name('teacher.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('teacher.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('teacher.centres.assets');
    Route::get('/centre/{id}', [CentreController::class, 'show'])->name('teacher.centre.view');

    // Assets management
    Route::get('/assets', [TeacherController::class, 'assets'])->name('teacher.assets');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('teacher.assets.show');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('teacher.asset.view');

    // Classes/Activities
    Route::get('/classes', [ClassController::class, 'index'])->name('teacher.classes');
    Route::get('/class/view/{id}', [ClassController::class, 'show'])->name('teacher.class.view');
    Route::post('/class/attendance/{id}', [ClassController::class, 'updateAttendance'])->name('teacher.class.attendance');
    Route::get('/schedule', [ClassController::class, 'schedule'])->name('teacher.schedule');

    // Activities
    Route::get('/activities', [TeacherController::class, 'activities'])->name('teacher.activities');
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('teacher.activity.view');

    // Reports, Settings & Notifications
    Route::get('/reports', [TeacherController::class, 'reports'])->name('teacher.reports');
    Route::get('/settings', [TeacherController::class, 'settings'])->name('teacher.settings');
    Route::get('/notifications', [TeacherController::class, 'notifications'])->name('teacher.notifications');
    Route::post('/notifications/mark-read', [TeacherController::class, 'markNotificationsRead'])->name('teacher.notifications.mark-read');

    // Rehabilitation redirects
    Route::get('/rehabilitation', function() { return redirect()->route('rehabilitation.categories'); })->name('teacher.rehabilitation');
});

// AJK Routes
Route::prefix('ajk')->middleware(['auth', 'role:ajk'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('ajk.dashboard');

    // Redirect to common routes
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('ajk.trainees');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('ajk.users');

    Route::get('/user/view/{id}', [AJKController::class, 'viewUser'])->name('ajk.user.view');

    // Centres management
    Route::get('/centres', [AJKController::class, 'centres'])->name('ajk.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('ajk.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('ajk.centres.assets');
    Route::get('/centre/{id}', [CentreController::class, 'show'])->name('ajk.centre.view');

    // Assets management
    Route::get('/assets', [AJKController::class, 'assets'])->name('ajk.assets');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('ajk.assets.show');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('ajk.asset.view');

    // Events
    Route::get('/events', [EventController::class, 'index'])->name('ajk.events');
    Route::get('/event/create', [EventController::class, 'create'])->name('ajk.event.create');
    Route::post('/event/store', [EventController::class, 'store'])->name('ajk.event.store');
    Route::get('/event/view/{id}', [EventController::class, 'show'])->name('ajk.event.view');
    Route::get('/event/edit/{id}', [EventController::class, 'edit'])->name('ajk.event.edit');
    Route::post('/event/update/{id}', [EventController::class, 'update'])->name('ajk.event.update');
    Route::delete('/event/delete/{id}', [EventController::class, 'destroy'])->name('ajk.event.delete');

    // Volunteers
    Route::get('/volunteers', [AJKController::class, 'manageVolunteers'])->name('ajk.volunteers');
    Route::get('/volunteer/view/{id}', [AJKController::class, 'viewVolunteer'])->name('ajk.volunteer.view');
    Route::get('/volunteer/edit/{id}', [AJKController::class, 'editVolunteer'])->name('ajk.volunteer.edit');
    Route::post('/volunteer/update/{id}', [AJKController::class, 'updateVolunteer'])->name('ajk.volunteer.update');
    Route::post('/volunteer/change-status/{id}', [AJKController::class, 'changeVolunteerStatus'])->name('ajk.volunteer.change-status');

    // Activities
    Route::get('/activities', [AJKController::class, 'activities'])->name('ajk.activities');
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('ajk.activity.view');

    // Reports, Settings & Notifications
    Route::get('/reports', [AJKController::class, 'reports'])->name('ajk.reports');
    Route::get('/settings', [AJKController::class, 'settings'])->name('ajk.settings');
    Route::get('/notifications', [AJKController::class, 'notifications'])->name('ajk.notifications');
    Route::post('/notifications/mark-read', [AJKController::class, 'markNotificationsRead'])->name('ajk.notifications.mark-read');

    // Rehabilitation redirects
    Route::get('/rehabilitation', function() { return redirect()->route('rehabilitation.categories'); })->name('ajk.rehabilitation');
});

/*
|--------------------------------------------------------------------------
<<<<<<< HEAD
| ENHANCED TEMPLATES ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Enhanced Trainee Routes
    Route::get('/trainees/enhanced-registration', [TraineeRegistrationController::class, 'enhancedIndex'])->name('trainees.enhanced-registration');
    Route::get('/trainees/enhanced-dashboard', [TraineeHomeController::class, 'enhancedIndex'])->name('trainees.enhanced-dashboard');
    
    // Enhanced Activities Routes
    Route::get('/activities/enhanced-homepage', [ActivityController::class, 'enhancedHomepage'])->name('activities.enhanced-homepage');
    
    // Enhanced Assets Routes
    Route::get('/assets/enhanced-management', [AssetController::class, 'enhancedManagement'])->name('assets.enhanced-management');
    
    // Enhanced Settings Routes
    Route::get('/settings/enhanced-settings', [SettingController::class, 'enhancedIndex'])->name('settings.enhanced-settings');
    Route::post('/settings/enhanced-update', [SettingController::class, 'enhancedUpdate'])->name('settings.enhanced-update');
    
    // Role-specific enhanced settings routes
    Route::get('/admin/settings/enhanced', [SettingController::class, 'enhancedIndex'])->name('admin.settings.enhanced');
    Route::get('/supervisor/settings/enhanced', [SettingController::class, 'enhancedIndex'])->name('supervisor.settings.enhanced');
    Route::get('/teacher/settings/enhanced', [SettingController::class, 'enhancedIndex'])->name('teacher.settings.enhanced');
    Route::get('/ajk/settings/enhanced', [SettingController::class, 'enhancedIndex'])->name('ajk.settings.enhanced');
});

/*
|--------------------------------------------------------------------------
=======
>>>>>>> 143e32d27006496b74e6c06d9c359084d812058c
| API ROUTES FOR AJAX CALLS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // User APIs
    Route::get('/users', [AdminController::class, 'getUsersJson'])->name('users');
    Route::get('/users/filter', [AdminController::class, 'filterUsers'])->name('users.filter');
    Route::get('/search/users', [MainController::class, 'searchUsers'])->name('search.users');

    // Trainee APIs
    Route::get('/trainees', [TraineeController::class, 'getTraineesJson'])->name('trainees');
    Route::get('/trainees/filter', [TraineeController::class, 'filterTrainees'])->name('trainees.filter');
    Route::get('/search/trainees', [TraineeManagementController::class, 'search'])->name('search.trainees');

    // Activity APIs
<<<<<<< HEAD
    Route::get('/activities', [ActivityController::class, 'apiIndex'])->name('activities');
    Route::get('/activities/categories', [ActivityController::class, 'getCategories'])->name('activities.categories');
    Route::get('/activities/filter', [ActivityController::class, 'filterActivities'])->name('activities.filter');

    // Asset APIs
    Route::get('/assets', [AssetController::class, 'getAssetsJson'])->name('assets');
    Route::get('/asset-categories', [AssetController::class, 'getCategoriesJson'])->name('asset-categories');
    Route::get('/assets/filter', [AssetController::class, 'filterAssets'])->name('assets.filter');

    // Settings APIs
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/preferences', [SettingController::class, 'updatePreferences'])->name('settings.preferences');
    Route::post('/settings/security', [SettingController::class, 'updateSecurity'])->name('settings.security');
    Route::post('/settings/privacy', [SettingController::class, 'updatePrivacy'])->name('settings.privacy');
    Route::post('/settings/avatar', [SettingController::class, 'uploadAvatar'])->name('settings.avatar');
    Route::post('/settings/sessions/revoke', [SettingController::class, 'revokeSession'])->name('settings.sessions.revoke');
    
    // Dashboard APIs
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/charts', [DashboardController::class, 'getCharts'])->name('dashboard.charts');
});

/*
|--------------------------------------------------------------------------
| ENHANCED TEMPLATE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Enhanced Trainee Dashboard
    Route::get('/trainees/enhanced', [TraineeHomeController::class, 'enhancedIndex'])->name('trainees.enhanced');
    
    // Enhanced Activities Homepage  
    Route::get('/activities/enhanced', [ActivityController::class, 'enhancedHomepage'])->name('activities.enhanced');
    
    // Enhanced Asset Management
    Route::get('/assets/enhanced', [AssetController::class, 'enhancedManagement'])->name('assets.enhanced');
    
    // Enhanced Settings Page
    Route::get('/settings/enhanced', [SettingController::class, 'enhancedIndex'])->name('settings.enhanced');
    Route::get('/settings/user', [SettingController::class, 'userSettings'])->name('settings.user');
=======
    Route::get('/activities/categories', [ActivityController::class, 'getCategories'])->name('activities.categories');
>>>>>>> 143e32d27006496b74e6c06d9c359084d812058c
});

/*
|--------------------------------------------------------------------------
| LEGACY ROUTES (Backward Compatibility)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/aboutus', function () { return view('aboutus'); })->name('aboutus');
    Route::get('/accountprofile', function () { return view('accountprofile'); })->name('accountprofile');
    Route::get('/schedulehomepage', function () { return view('schedulehome'); })->name('schedulehomepage');
    Route::get('/assetmanagementpage', [AssetController::class, 'index'])->name('assetmanagementpage');
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