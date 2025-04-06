<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Main Controllers
use App\Http\Controllers\MainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserProfileController;

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

// Activity and Resource Controllers
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EventController;

// Report and Admin Controllers
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

// Volunteer and Public Controllers
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\ContactController;

// Notification and Communication Controllers
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;

// Auth Controllers
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
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
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Custom authentication routes
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/auth/login', [MainController::class, 'login'])->name('auth.loginpage');
    Route::post('/auth/check', [MainController::class, 'check'])->name('auth.check');
    
    // Registration routes
    Route::get('/auth/register', [MainController::class, 'registration'])->name('auth.registerpage');
    Route::post('/auth/save', [MainController::class, 'save'])->name('auth.save');
    
    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])
    ->name('auth.forgotpassword');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'submitForgotPasswordForm'])
        ->name('auth.processforgotpassword');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])
        ->name('auth.resetpassword');
    Route::post('/reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])
        ->name('auth.updatepassword');
});

// Logout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::post('/logout', [MainController::class, 'logout'])->name('logout.post');
});

/*
|--------------------------------------------------------------------------
| Common Authenticated Routes (Available to All Roles)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard redirects to role-specific dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dynamic profile routes based on user role
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    
    // User messaging system
    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.conversation');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/mark-read/{id}', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/api/notifications', [NotificationController::class, 'getJson'])->name('api.notifications');
    
    // Common centre listings
    Route::get('/centres', [CentreController::class, 'index'])->name('centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('centres.view');
    
    // Trainee routes
    Route::get('/traineeshome', [TraineeHomeController::class, 'index'])->name('traineeshome');
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::post('/updatetraineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile');
    Route::get('/traineeactivity', function () { return view('traineeactivity'); })->name('traineeactivity');
    Route::get('/traineesregistration', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::post('/traineesregistration/store', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
    
    // Asset management
    Route::get('/asset-management', [AssetController::class, 'index'])->name('assetmanagementpage');
});

/*
|--------------------------------------------------------------------------
| Role-Based Dashboard Routes
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // User management (using UserController for general user operations)
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/supervisors', [AdminController::class, 'manageSupervisors'])->name('admin.users.supervisors');
    Route::get('/users/teachers', [AdminController::class, 'manageTeachers'])->name('admin.users.teachers');
    Route::get('/users/ajks', [AdminController::class, 'manageAJKs'])->name('admin.users.ajks');
    
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
    
    // User API endpoints
    Route::get('/api/users', [AdminController::class, 'getUsersJson'])->name('admin.api.users');
    Route::get('/api/users/filter', [AdminController::class, 'filterUsers'])->name('admin.api.users.filter');
    
    // User bulk actions
    Route::post('/users/bulk-action', [AdminController::class, 'bulkUserAction'])->name('admin.users.bulk-action');
    
    // Trainee management - using TraineeController
    Route::get('/trainees', [TraineeController::class, 'index'])->name('admin.trainees');
    Route::get('/trainee/create', [TraineeController::class, 'create'])->name('admin.trainee.create');
    Route::post('/trainee/store', [TraineeController::class, 'store'])->name('admin.trainee.store');
    Route::get('/trainee/view/{id}', [TraineeController::class, 'show'])->name('admin.trainee.view');
    Route::get('/trainee/edit/{id}', [TraineeController::class, 'edit'])->name('admin.trainee.edit');
    Route::post('/trainee/update/{id}', [TraineeController::class, 'update'])->name('admin.trainee.update');
    Route::delete('/trainee/delete/{id}', [TraineeController::class, 'destroy'])->name('admin.trainee.delete');
    
    // Activities management - using ActivityController
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
    
    // Assets management - using AssetController
    Route::get('/assets', [AssetController::class, 'index'])->name('admin.assets');
    Route::get('/asset/create', [AssetController::class, 'create'])->name('admin.asset.create');
    Route::post('/asset/store', [AssetController::class, 'store'])->name('admin.asset.store');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('admin.asset.view');
    Route::get('/asset/edit/{id}', [AssetController::class, 'edit'])->name('admin.asset.edit');
    Route::post('/asset/update/{id}', [AssetController::class, 'update'])->name('admin.asset.update');
    Route::delete('/asset/delete/{id}', [AssetController::class, 'destroy'])->name('admin.asset.delete');
    
    // Reports - using ReportController
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('admin.report.generate');
    Route::post('/report/export', [ReportController::class, 'export'])->name('admin.report.export');
    
    // System logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    
    // Settings - using SettingController
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/backup', [AdminController::class, 'createBackup'])->name('admin.settings.backup');
    Route::post('/settings/restore', [AdminController::class, 'restoreBackup'])->name('admin.settings.restore');
    
    // Notifications
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::post('/notifications/mark-read', [AdminController::class, 'markNotificationsRead'])->name('admin.notifications.mark-read');
    Route::delete('/notifications/clear', [AdminController::class, 'clearNotifications'])->name('admin.notifications.clear');
});

// Supervisor Routes
Route::prefix('supervisor')->middleware(['auth', 'role:supervisor'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('supervisor.dashboard');
    
    // Missing routes from the menu items in dashboard.php
    Route::get('/users', [SupervisorController::class, 'users'])->name('supervisor.users');
    Route::get('/trainees', [SupervisorController::class, 'trainees'])->name('supervisor.trainees');
    Route::get('/centres', [SupervisorController::class, 'centres'])->name('supervisor.centres');
    Route::get('/assets', [SupervisorController::class, 'assets'])->name('supervisor.assets');
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('supervisor.reports');
    Route::get('/settings', [SupervisorController::class, 'settings'])->name('supervisor.settings');
    
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
    
    // Trainees specific actions
    Route::get('/trainee/view/{id}', [SupervisorController::class, 'viewTrainee'])->name('supervisor.trainee.view');
    Route::get('/trainee/edit/{id}', [SupervisorController::class, 'editTrainee'])->name('supervisor.trainee.edit');
    Route::post('/trainee/update/{id}', [SupervisorController::class, 'updateTrainee'])->name('supervisor.trainee.update');
    
    // Reports - using ReportController
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('supervisor.report.generate');
    Route::post('/report/export', [ReportController::class, 'export'])->name('supervisor.report.export');
    
    // Notifications
    Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('supervisor.notifications');
    Route::post('/notifications/mark-read', [SupervisorController::class, 'markNotificationsRead'])->name('supervisor.notifications.mark-read');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('teacher.dashboard');
    
    // Missing routes from the menu items in dashboard.php
    Route::get('/users', [TeacherController::class, 'users'])->name('teacher.users');
    Route::get('/trainees', [TeacherController::class, 'trainees'])->name('teacher.trainees');
    Route::get('/centres', [TeacherController::class, 'centres'])->name('teacher.centres');
    Route::get('/assets', [TeacherController::class, 'assets'])->name('teacher.assets');
    Route::get('/reports', [TeacherController::class, 'reports'])->name('teacher.reports');
    Route::get('/settings', [TeacherController::class, 'settings'])->name('teacher.settings');
    
    // Trainees management
    Route::get('/trainee/view/{id}', [TeacherController::class, 'viewTrainee'])->name('teacher.trainee.view');
    Route::post('/trainee/progress/update/{id}', [TeacherController::class, 'updateTraineeProgress'])->name('teacher.trainee.progress.update');
    
    // Classes/Activities - using ClassController
    Route::get('/classes', [ClassController::class, 'index'])->name('teacher.classes');
    Route::get('/class/view/{id}', [ClassController::class, 'show'])->name('teacher.class.view');
    Route::post('/class/attendance/{id}', [ClassController::class, 'updateAttendance'])->name('teacher.class.attendance');
    
    // Schedule
    Route::get('/schedule', [ClassController::class, 'schedule'])->name('teacher.schedule');
    
    // Activities
    Route::get('/activities', [TeacherController::class, 'activities'])->name('teacher.activities');
    
    // Notifications
    Route::get('/notifications', [TeacherController::class, 'notifications'])->name('teacher.notifications');
    Route::post('/notifications/mark-read', [TeacherController::class, 'markNotificationsRead'])->name('teacher.notifications.mark-read');
});

// AJK Routes
Route::prefix('ajk')->middleware(['auth', 'role:ajk'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('ajk.dashboard');
    
    // Missing routes from the menu items in dashboard.php
    Route::get('/users', [AJKController::class, 'users'])->name('ajk.users');
    Route::get('/trainees', [AJKController::class, 'trainees'])->name('ajk.trainees');
    Route::get('/centres', [AJKController::class, 'centres'])->name('ajk.centres');
    Route::get('/assets', [AJKController::class, 'assets'])->name('ajk.assets');
    Route::get('/reports', [AJKController::class, 'reports'])->name('ajk.reports');
    Route::get('/settings', [AJKController::class, 'settings'])->name('ajk.settings');
    
    // Events - using EventController
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
    
    // Notifications
    Route::get('/notifications', [AJKController::class, 'notifications'])->name('ajk.notifications');
    Route::post('/notifications/mark-read', [AJKController::class, 'markNotificationsRead'])->name('ajk.notifications.mark-read');
});

/*
|--------------------------------------------------------------------------
| Resource-Based Routes (RESTful)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Trainee resource routes (accessible to multiple roles)
    Route::resource('trainees', TraineeController::class);
    
    // Activity resource routes
    Route::resource('activities', ActivityController::class)->except(['show']);
    Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');
    
    // Activity participation
    Route::post('/activities/{id}/register', [ActivityController::class, 'registerParticipation'])->name('activities.register');
    Route::delete('/activities/{id}/unregister', [ActivityController::class, 'unregisterParticipation'])->name('activities.unregister');
    
    // Progress updates for trainees
    Route::post('/trainees/{id}/progress', [TraineeController::class, 'updateProgress'])->name('trainees.progress.update');
});

/*
|--------------------------------------------------------------------------
| Legacy Routes for Backward Compatibility
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/teachershome', function () { return view('teachershome'); })->name('teachershome');
    Route::get('/aboutus', function () { return view('aboutus'); })->name('aboutus');
    Route::get('/accountprofile', function () { return view('accountprofile'); })->name('accountprofile');
    Route::get('/schedulehomepage', function () { return view('schedulehome'); })->name('schedulehomepage');
});

/*
|--------------------------------------------------------------------------
| Debug & Testing Routes
|--------------------------------------------------------------------------
*/

// Only enable these routes in local development environment
if (config('app.env') === 'local') {
    // Session debugging routes
    Route::get('/debug-session', function() {
        return [
            'session_all' => session()->all(),
            'has_id' => session()->has('id'),
            'id' => session('id'),
            'role' => session('role'),
            'session_id' => session()->getId(),
            'cookies' => request()->cookies->all()
        ];
    });

    // Test login route (for bypassing normal login flow during debugging)
    Route::get('/test-login', function() {
        // Force set session data - use an actual admin ID from your database
        session([
            'id' => 1, 
            'iium_id' => 'TEST1234',
            'name' => 'Test Admin',
            'role' => 'admin',
            'email' => 'test@example.com',
            'logged_in' => true,
            'login_time' => now()->toDateTimeString()
        ]);
        
        session()->save();
        
        Log::info('Test login session set', [
            'session_id' => session()->getId(),
            'all_session' => session()->all()
        ]);
        
        return redirect('/admin/dashboard');
    });
}

/*
|--------------------------------------------------------------------------
| Fallback Route
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