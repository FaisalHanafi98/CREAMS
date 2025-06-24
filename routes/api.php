<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/forgot-password', [ForgotPasswordController::class, 'forgot'])->name('auth.forgotpassword');

// API Controller for system endpoints
use App\Http\Controllers\Api\ApiController;

// Public API endpoints
Route::get('/health', [ApiController::class, 'healthCheck']);
Route::get('/stats', [ApiController::class, 'getStats']);
Route::get('/search', [ApiController::class, 'search']);

// Protected API endpoints (require session authentication)
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard-data', [ApiController::class, 'getDashboardData']);
});
