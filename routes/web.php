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
    // Login routes
    Route::get('/auth/login', [MainController::class, 'login'])->name('auth.loginpage');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/auth/check', [MainController::class, 'check'])->name('auth.check');

    // Registration routes
    Route::get('/auth/register', [MainController::class, 'registration'])->name('auth.registerpage');
    Route::get('/registration', [MainController::class, 'registration'])->name('registration');
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
});

/*
|--------------------------------------------------------------------------
| COMMON AUTHENTICATED ROUTES (Available to All Logged-in Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile management
    // Profile management routes
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Activity Management
    Route::prefix('activities')->name('activities.')->group(function () {
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
        
        // Scheduling routes (Teacher, Admin, Supervisor)
        Route::middleware(['role:teacher,admin,supervisor'])->group(function () {
            Route::get('/{id}/schedule', [ActivityController::class, 'schedule'])->name('schedule');
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
        });
    });

    // Schedule Routes
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/weekly', [ActivityController::class, 'weeklySchedule'])->name('weekly');
        Route::get('/teacher/{teacherId}', [ActivityController::class, 'teacherSchedule'])->name('teacher');
    });

    // Rehabilitation Routes
    Route::prefix('rehabilitation')->name('rehabilitation.')->group(function () {
        Route::get('/categories', [ActivityController::class, 'categories'])->name('categories');
        Route::get('/categories/{category}', [ActivityController::class, 'categoryShow'])->name('categories.show');
    });

    // Teachers/Staff Directory
    Route::get('/teachershome', [TeachersHomeController::class, 'index'])->name('teachershome');
    Route::get('/updateuser/{id}', [TeachersHomeController::class, 'updateuserpage'])->name('updateuser');
    Route::post('/updateuser/{id}', [TeachersHomeController::class, 'updateuser'])->name('updateuser.post');
    
    // NEW STAFF PROFILE MANAGEMENT SYSTEM
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/view/{id}', [App\Http\Controllers\StaffController::class, 'viewProfile'])->name('view');
        Route::get('/edit/{id}', [App\Http\Controllers\StaffController::class, 'editProfile'])->name('edit');
        Route::put('/update/{id}', [App\Http\Controllers\StaffController::class, 'updateProfile'])->name('update');
        Route::get('/schedule/{id}', [App\Http\Controllers\StaffController::class, 'showSchedule'])->name('schedule');
        Route::get('/activities/{id}', [App\Http\Controllers\StaffController::class, 'showActivities'])->name('activities');
        Route::get('/trainees/{id}', [App\Http\Controllers\StaffController::class, 'showTrainees'])->name('trainees');
    });

    // Centres
    Route::prefix('centres')->name('centres.')->group(function () {
        Route::get('/', [CentreController::class, 'index'])->name('index');
        Route::get('/{id}', [CentreController::class, 'show'])->name('show');
        Route::get('/{id}/assets', [CentreController::class, 'assets'])->name('assets');
    });

    // Assets
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
    });

    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| TRAINEE MODULE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('trainees')->name('trainees.')->group(function () {
    Route::get('/', [TraineeHomeController::class, 'index'])->name('index');
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
    
    // Trainee Profile Routes
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{id}/edit', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::put('/traineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('traineeprofile.update');
    Route::post('/traineeprofile/{id}/progress', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.progress');
    Route::post('/traineeprofile/{id}/attendance', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.attendance');
    Route::post('/traineeprofile/{id}/activity', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/traineeprofile/{id}/download', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');
    Route::delete('/traineeprofile/{id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    
    Route::get('/traineesregistrationpage', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::post('/traineesregistrationstore', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
    Route::post('/validateEmail', [TraineeRegistrationController::class, 'validateEmail'])->name('validateEmail');
});

// Legacy asset route
Route::middleware(['auth'])->group(function () {
    Route::get('/assetmanagementpage', [AssetController::class, 'index'])->name('assetmanagementpage');
    Route::get('/schedulehomepage', function () { return view('schedulehome'); })->name('schedulehomepage');
    Route::get('/aboutus', function () { return view('aboutus'); })->name('aboutus');
});

/*
|--------------------------------------------------------------------------
| ROLE-BASED DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Centres management
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

    // Assets management
    Route::prefix('assets')->name('admin.assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
    });

    // Redirect routes to common routes
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('admin.centres');
    Route::get('/assets', function() { return redirect()->route('assets.index'); })->name('admin.assets');
    Route::get('/activities', function() { return redirect()->route('activities.index'); })->name('admin.activities');
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('admin.trainees');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('admin.users');
});

// Supervisor Routes
Route::prefix('supervisor')->middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('supervisor.centres');
    Route::get('/activities', function() { return redirect()->route('activities.index'); })->name('supervisor.activities');
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('supervisor.trainees');
    Route::get('/users', function() { return redirect()->route('teachershome'); })->name('supervisor.users');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('teacher.dashboard');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('teacher.centres');
    Route::get('/activities', function() { return redirect()->route('activities.index'); })->name('teacher.activities');
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('teacher.trainees');
    Route::get('/schedule', [ClassController::class, 'schedule'])->name('teacher.schedule');
});

// AJK Routes
Route::prefix('ajk')->middleware(['auth', 'role:ajk'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('ajk.dashboard');
    Route::get('/centres', function() { return redirect()->route('centres.index'); })->name('ajk.centres');
    Route::get('/activities', function() { return redirect()->route('activities.index'); })->name('ajk.activities');
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('ajk.trainees');
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
});

/*
|--------------------------------------------------------------------------
| DEBUG ROUTE - Remove in production
|--------------------------------------------------------------------------
*/


Route::get('/debug/database', function () {
    try {
        $tables = ['users', 'trainees', 'activities', 'centres', 'assets', 'events'];
        $results = [];
        
        foreach($tables as $table) {
            if (Schema::hasTable($table)) {
                $results[$table] = [
                    'columns' => Schema::getColumnListing($table),
                    'record_count' => DB::table($table)->count()
                ];
            } else {
                $results[$table] = 'TABLE NOT FOUND';
            }
        }
        
        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->name('debug.database');

/*
|--------------------------------------------------------------------------
| SAFE DASHBOARD ROUTES - Backup for testing
|--------------------------------------------------------------------------
*/

Route::get('/safe-dashboard', 'App\Http\Controllers\SafeDashboardController@index')
    ->name('safe.dashboard');

Route::get('/api/safe-stats', 'App\Http\Controllers\SafeDashboardController@getStats')
    ->name('safe.dashboard.stats');

/*
|--------------------------------------------------------------------------
| SEARCH ROUTE
|--------------------------------------------------------------------------
*/

Route::get('/search', function () {
    // Redirect to appropriate dashboard based on role
    if (session('id') && session('role')) {
        $role = session('role');
        return redirect()->route("{$role}.dashboard")
            ->with('info', 'Search functionality is coming soon.');
    }
    return redirect()->route('home');
})->name('search');

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