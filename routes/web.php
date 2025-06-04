<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Main Controllers
use App\Http\Controllers\MainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\TeachersHomeController;

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

// Activity and Resource Controllers
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetItemController;
use App\Http\Controllers\AssetTypeController;
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

    // Add search route
    Route::get('/search', [MainController::class, 'search'])->name('search');

    // Teachers Home page - centralized staff directory
    Route::get('/teachershome', [TeachersHomeController::class, 'index'])->name('teachershome');
    Route::get('/updateuserprofile/{id}', [TeachersHomeController::class, 'updateuserpage'])->name('updateuser');
    Route::post('/updateuser/{id}', [TeachersHomeController::class, 'updateuser'])->name('updateuserpost');

    // Dynamic profile routes based on user role
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Add fallback routes for common menus
    Route::redirect('/messages', '/messages/index')->name('messages');
    Route::redirect('/notifications', '/notifications/index')->name('notifications');

    // Message Routes
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

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'getUnread'])->name('unread');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Common centre listings
    Route::get('/centres', [CentreController::class, 'index'])->name('centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('centres.view');

    /*
    |--------------------------------------------------------------------------
    | TRAINEE MODULE ROUTES (Common for all authenticated users)
    |--------------------------------------------------------------------------
    */

    // Trainee Home page - List all trainees
    Route::get('/trainees', [TraineeHomeController::class, 'index'])->name('traineeshome');
    Route::get('/traineeshome', [TraineeHomeController::class, 'index'])->name('traineeshome'); // Keep legacy route
    Route::get('/trainees/filter', [TraineeHomeController::class, 'filter'])->name('trainees.filter');

    // Individual Trainee Profile
    Route::get('/trainee/profile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile'); // Keep legacy route
    Route::get('/trainee/edit/{id}', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::post('/trainee/update/{id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile');
    Route::post('/updatetraineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('updatetraineeprofile'); // Keep legacy route

    // Trainee profile actions
    Route::delete('/trainee/delete/{id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    Route::post('/trainee/progress/{id}', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.updateProgress');
    Route::post('/trainee/attendance/{id}', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.recordAttendance');
    Route::post('/trainee/activity/{id}', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/trainee/download/{id}', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');

    // Trainee Registration
    Route::get('/trainee/register', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::get('/traineesregistration', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage'); // Keep legacy route
    Route::post('/trainee/register', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
    Route::post('/traineesregistration/store', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore'); // Keep legacy route
    Route::post('/trainee/validate-email', [TraineeRegistrationController::class, 'validateEmail'])->name('validateEmail');

    // Trainee Activities
    Route::get('/trainee/activities', [TraineeActivityController::class, 'index'])->name('traineeactivity');
    Route::get('/traineeactivity', [TraineeActivityController::class, 'index'])->name('traineeactivity'); // Keep legacy route
    Route::get('/trainee/activities/{id}', [TraineeActivityController::class, 'traineeActivities'])->name('traineeactivity.trainee');
    Route::post('/trainee/activities', [TraineeActivityController::class, 'store'])->name('traineeactivity.store');
    Route::post('/traineeactivity/store', [TraineeActivityController::class, 'store'])->name('traineeactivity.store'); // Keep legacy route
    Route::get('/trainee/activities/edit/{id}', [TraineeActivityController::class, 'edit'])->name('traineeactivity.edit');
    Route::put('/trainee/activities/update/{id}', [TraineeActivityController::class, 'update'])->name('traineeactivity.update');
    Route::post('/traineeactivity/update/{id}', [TraineeActivityController::class, 'update'])->name('traineeactivity.update'); // Keep legacy route
    Route::delete('/trainee/activities/delete/{id}', [TraineeActivityController::class, 'destroy'])->name('traineeactivity.destroy');
    Route::delete('/traineeactivity/delete/{id}', [TraineeActivityController::class, 'destroy'])->name('traineeactivity.destroy'); // Keep legacy route
    Route::get('/traineeactivity/details/{id}', [TraineeActivityController::class, 'getActivityDetails'])->name('traineeactivity.details');

    // Trainee JSON API
    Route::get('/api/trainees', [TraineeController::class, 'getTraineesJson'])->name('api.trainees');
    Route::get('/api/trainees/filter', [TraineeController::class, 'filterTrainees'])->name('api.trainees.filter');

    // Activity resource routes
    Route::resource('activities', ActivityController::class)->except(['show']);
    Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');

    // Activity participation
    Route::post('/activities/{id}/register', [ActivityController::class, 'registerParticipation'])->name('activities.register');
    Route::delete('/activities/{id}/unregister', [ActivityController::class, 'unregisterParticipation'])->name('activities.unregister');

    /*
    |--------------------------------------------------------------------------
    | Rehabilitation Routes
    |--------------------------------------------------------------------------
    */

    // Rehabilitation (Categories & Activities)
    Route::get('/rehabilitation/categories', [ActivityController::class, 'categories'])->name('rehabilitation.categories');
    Route::get('/rehabilitation/categories/{category}', [ActivityController::class, 'categoryShow'])->name('rehabilitation.categories.show');
    Route::get('/rehabilitation/activities/create', [ActivityController::class, 'createActivity'])->name('rehabilitation.activities.create');
    Route::post('/rehabilitation/activities', [ActivityController::class, 'storeActivity'])->name('rehabilitation.activities.store');
    Route::get('/rehabilitation/activities/{id}', [ActivityController::class, 'showActivity'])->name('rehabilitation.activities.show');
    Route::get('/rehabilitation/activities/{id}/edit', [ActivityController::class, 'editActivity'])->name('rehabilitation.activities.edit');
    Route::put('/rehabilitation/activities/{id}', [ActivityController::class, 'updateActivity'])->name('rehabilitation.activities.update');
    Route::delete('/rehabilitation/activities/{id}', [ActivityController::class, 'destroyActivity'])->name('rehabilitation.activities.destroy');

    // Add role-specific routes for dashboard links
    Route::get('/admin/rehabilitation', function () {
        return redirect()->route('rehabilitation.categories');
    })->name('admin.rehabilitation');

    Route::get('/supervisor/rehabilitation', function () {
        return redirect()->route('rehabilitation.categories');
    })->name('supervisor.rehabilitation');

    Route::get('/teacher/rehabilitation', function () {
        return redirect()->route('rehabilitation.categories');
    })->name('teacher.rehabilitation');

    Route::get('/ajk/rehabilitation', function () {
        return redirect()->route('rehabilitation.categories');
    })->name('ajk.rehabilitation');
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

    // Redirect role-specific trainee routes to common trainee routes
    Route::get('/trainees', function () {
        return redirect()->route('traineeshome');
    })->name('admin.trainees');

    Route::get('/trainee/create', function () {
        return redirect()->route('traineesregistrationpage');
    })->name('admin.trainee.create');

    Route::get('/trainee/view/{id}', function ($id) {
        return redirect()->route('traineeprofile', ['id' => $id]);
    })->name('admin.trainee.view');

    Route::get('/trainee/edit/{id}', function ($id) {
        return redirect()->route('traineeprofile.edit', ['id' => $id]);
    })->name('admin.trainee.edit');

    // Staff/Teachers Home - Redirect to common teachershome
    Route::get('/users', function () {
        return redirect()->route('teachershome');
    })->name('admin.users');

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

    // Activities management - using ActivityController
    Route::get('/activities', [ActivityController::class, 'index'])->name('admin.activities');
    Route::get('/activity/create', [ActivityController::class, 'create'])->name('admin.activity.create');
    Route::post('/activity/store', [ActivityController::class, 'store'])->name('admin.activity.store');
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('admin.activity.view');
    Route::get('/activity/edit/{id}', [ActivityController::class, 'edit'])->name('admin.activity.edit');
    Route::post('/activity/update/{id}', [ActivityController::class, 'update'])->name('admin.activity.update');
    Route::delete('/activity/delete/{id}', [ActivityController::class, 'destroy'])->name('admin.activity.delete');

    // Centres management - IMPORTANT: These center routes are for the dashboard links
    Route::get('/centres', [CentreController::class, 'index'])->name('admin.centres');
    Route::get('/centre/create', [CentreController::class, 'create'])->name('admin.centre.create');
    Route::post('/centre/store', [CentreController::class, 'store'])->name('admin.centre.store');
    Route::get('/centre/view/{id}', [CentreController::class, 'show'])->name('admin.centre.view');
    Route::get('/centre/edit/{id}', [CentreController::class, 'edit'])->name('admin.centre.edit');
    Route::post('/centre/update/{id}', [CentreController::class, 'update'])->name('admin.centre.update');
    Route::delete('/centre/delete/{id}', [CentreController::class, 'destroy'])->name('admin.centre.delete');

    // Add extra routes to match dashboard references
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('admin.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('admin.centres.assets');
    Route::get('/centre/{id}/assets', [CentreController::class, 'assets'])->name('admin.centre.assets');

    /* // Assets management - using AssetController
    Route::get('/assets', [AssetController::class, 'index'])->name('admin.assets');
    Route::get('/asset/create', [AssetController::class, 'create'])->name('admin.asset.create');
    Route::post('/asset/store', [AssetController::class, 'store'])->name('admin.asset.store');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('admin.asset.view');
    Route::get('/asset/edit/{id}', [AssetController::class, 'edit'])->name('admin.asset.edit');
    Route::post('/asset/update/{id}', [AssetController::class, 'update'])->name('admin.asset.update');
    Route::delete('/asset/delete/{id}', [AssetController::class, 'destroy'])->name('admin.asset.delete'); */

    // Asset types & asset items management
    Route::prefix('asset-types')->name('admin.asset-types.')->group(function () {
        Route::get('/', [AssetTypeController::class, 'index'])->name('index');               // List/search/filter asset types
        Route::get('/create', [AssetTypeController::class, 'create'])->name('create');       // Show form to add new asset type
        Route::post('/', [AssetTypeController::class, 'store'])->name('store');              // Store new asset type
        Route::get('/{id}/edit', [AssetTypeController::class, 'edit'])->name('edit');        // Show edit form
        Route::put('/{id}', [AssetTypeController::class, 'update'])->name('update');         // Update asset type
        Route::delete('/{id}', [AssetTypeController::class, 'destroy'])->name('destroy');    // Delete asset type
    });
    Route::prefix('asset-items')->name('admin.asset-items.')->group(function () {
        Route::get('/', [AssetItemController::class, 'index'])->name('index');                 // List/search asset items
        Route::get('/create', [AssetItemController::class, 'create'])->name('create');         // Show form to add new asset item
        Route::post('/', [AssetItemController::class, 'store'])->name('store');                // Store new asset item
        Route::get('/{assetItem}/edit', [AssetItemController::class, 'edit'])->name('edit');   // Show edit form (model binding)
        Route::put('/{assetItem}', [AssetItemController::class, 'update'])->name('update');    // Update asset item (model binding)
        Route::delete('/{assetItem}', [AssetItemController::class, 'destroy'])->name('destroy'); // Delete asset item (model binding)
    });

    // Support naming scheme from dashboard
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('admin.assets.show');

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
});

// Supervisor Routes
Route::prefix('supervisor')->middleware(['auth', 'role:supervisor'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('supervisor.dashboard');

    // Redirect role-specific trainee routes to common trainee routes
    Route::get('/trainees', function () {
        return redirect()->route('traineeshome');
    })->name('supervisor.trainees');

    Route::get('/trainee/view/{id}', function ($id) {
        return redirect()->route('traineeprofile', ['id' => $id]);
    })->name('supervisor.trainee.view');

    Route::get('/trainee/edit/{id}', function ($id) {
        return redirect()->route('traineeprofile.edit', ['id' => $id]);
    })->name('supervisor.trainee.edit');

    // Staff/Teachers Home - Redirect to common teachershome
    Route::get('/users', function () {
        return redirect()->route('teachershome');
    })->name('supervisor.users');

    Route::get('/user/view/{id}', [SupervisorController::class, 'viewUser'])->name('supervisor.user.view');
    Route::get('/user/edit/{id}', [SupervisorController::class, 'editUser'])->name('supervisor.user.edit');

    // Centres management
    Route::get('/centres', [SupervisorController::class, 'centres'])->name('supervisor.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('supervisor.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('supervisor.centres.assets');

    // Backward compatibility for old URLs
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

    // Reports - using ReportController
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('supervisor.reports');
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('supervisor.report.generate');
    Route::post('/report/export', [ReportController::class, 'export'])->name('supervisor.report.export');

    // Settings
    Route::get('/settings', [SupervisorController::class, 'settings'])->name('supervisor.settings');

    // Notifications
    Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('supervisor.notifications');
    Route::post('/notifications/mark-read', [SupervisorController::class, 'markNotificationsRead'])->name('supervisor.notifications.mark-read');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('teacher.dashboard');

    // Redirect role-specific trainee routes to common trainee routes
    Route::get('/trainees', function () {
        return redirect()->route('traineeshome');
    })->name('teacher.trainees');

    Route::get('/trainee/view/{id}', function ($id) {
        return redirect()->route('traineeprofile', ['id' => $id]);
    })->name('teacher.trainee.view');

    // Staff/Teachers Home - Redirect to common teachershome
    Route::get('/users', function () {
        return redirect()->route('teachershome');
    })->name('teacher.users');

    Route::get('/user/view/{id}', [TeacherController::class, 'viewUser'])->name('teacher.user.view');

    // Centres management
    Route::get('/centres', [TeacherController::class, 'centres'])->name('teacher.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('teacher.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('teacher.centres.assets');

    // Backward compatibility for old URLs
    Route::get('/centre/{id}', [CentreController::class, 'show'])->name('teacher.centre.view');

    // Assets management
    Route::get('/assets', [TeacherController::class, 'assets'])->name('teacher.assets');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('teacher.assets.show');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('teacher.asset.view');

    // Classes/Activities - using ClassController
    Route::get('/classes', [ClassController::class, 'index'])->name('teacher.classes');
    Route::get('/class/view/{id}', [ClassController::class, 'show'])->name('teacher.class.view');
    Route::post('/class/attendance/{id}', [ClassController::class, 'updateAttendance'])->name('teacher.class.attendance');

    // Schedule
    Route::get('/schedule', [ClassController::class, 'schedule'])->name('teacher.schedule');

    // Activities
    Route::get('/activities', [TeacherController::class, 'activities'])->name('teacher.activities');
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('teacher.activity.view');

    // Reports
    Route::get('/reports', [TeacherController::class, 'reports'])->name('teacher.reports');

    // Settings
    Route::get('/settings', [TeacherController::class, 'settings'])->name('teacher.settings');

    // Notifications
    Route::get('/notifications', [TeacherController::class, 'notifications'])->name('teacher.notifications');
    Route::post('/notifications/mark-read', [TeacherController::class, 'markNotificationsRead'])->name('teacher.notifications.mark-read');
});

// AJK Routes
Route::prefix('ajk')->middleware(['auth', 'role:ajk'])->group(function () {
    // Dashboard - using the DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('ajk.dashboard');

    // Redirect role-specific trainee routes to common trainee routes
    Route::get('/trainees', function () {
        return redirect()->route('traineeshome');
    })->name('ajk.trainees');

    // Staff/Teachers Home - Redirect to common teachershome
    Route::get('/users', function () {
        return redirect()->route('teachershome');
    })->name('ajk.users');

    Route::get('/user/view/{id}', [AJKController::class, 'viewUser'])->name('ajk.user.view');

    // Centres management
    Route::get('/centres', [AJKController::class, 'centres'])->name('ajk.centres');
    Route::get('/centres/{id}', [CentreController::class, 'show'])->name('ajk.centres.show');
    Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('ajk.centres.assets');

    // Backward compatibility for old URLs
    Route::get('/centre/{id}', [CentreController::class, 'show'])->name('ajk.centre.view');

    // Assets management
    Route::get('/assets', [AJKController::class, 'assets'])->name('ajk.assets');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('ajk.assets.show');
    Route::get('/asset/view/{id}', [AssetController::class, 'show'])->name('ajk.asset.view');

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
    Route::get('/activity/view/{id}', [ActivityController::class, 'show'])->name('ajk.activity.view');

    // Reports
    Route::get('/reports', [AJKController::class, 'reports'])->name('ajk.reports');

    // Settings
    Route::get('/settings', [AJKController::class, 'settings'])->name('ajk.settings');

    // Notifications
    Route::get('/notifications', [AJKController::class, 'notifications'])->name('ajk.notifications');
    Route::post('/notifications/mark-read', [AJKController::class, 'markNotificationsRead'])->name('ajk.notifications.mark-read');
});

/*
|--------------------------------------------------------------------------
| Legacy Routes for Backward Compatibility
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/teachershome', [TeachersHomeController::class, 'index'])->name('teachershome');
    Route::get('/aboutus', function () {
        return view('aboutus');
    })->name('aboutus');
    Route::get('/accountprofile', function () {
        return view('accountprofile');
    })->name('accountprofile');
    Route::get('/schedulehomepage', function () {
        return view('schedulehome');
    })->name('schedulehomepage');
    Route::get('/assetmanagementpage', [AssetController::class, 'index'])->name('assetmanagementpage');
});

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
