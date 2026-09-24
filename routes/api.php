<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'ability:refresh'])->group(function () {
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
});

// Patient portal routes are declared before the staff `patient` apiResource
// below: both share the `patient/...` prefix, and Laravel matches routes in
// declaration order, so `patient/{patient}` would otherwise swallow
// `patient/me` (treating "me" as the wildcard id) if it came first.
Route::post('patient/auth/login', [PatientAuthController::class, 'login']);

Route::middleware(['auth:patient', 'ability:patient-refresh'])->group(function () {
    Route::post('patient/auth/refresh', [PatientAuthController::class, 'refresh']);
});

Route::middleware(['auth:patient', 'ability:patient-access'])->group(function () {
    Route::post('patient/auth/logout', [PatientAuthController::class, 'logout']);
    Route::get('patient/me', [PatientAuthController::class, 'me']);
    Route::get('patient/results', [ExamResultController::class, 'indexForPatient']);
    Route::get('patient/results/{examResult}/download', [ExamResultController::class, 'download']);
});

// Signed, short-lived download link for locally-stored results (S3 uses a
// temporary signed bucket URL instead and never hits this route).
Route::get('exam-results/{examResult}/stream', [ExamResultController::class, 'stream'])
    ->name('exam-results.stream')
    ->middleware('signed');

Route::middleware(['auth:sanctum', 'ability:access'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::put('me/password', [UserController::class, 'updatePassword']);
        Route::put('{user}/password', [UserController::class, 'resetPassword']);
    });
    Route::apiResource('user', UserController::class);
    Route::apiResource('exam', ExamController::class);
    Route::apiResource('partner', PartnerController::class);
    Route::apiResource('patient', PatientController::class);
    Route::apiResource('order', OrderController::class);
    Route::apiResource('financial', FinancialController::class);

    Route::prefix('exam-result')->group(function () {
        Route::get('/', [ExamResultController::class, 'index']);
        Route::post('/', [ExamResultController::class, 'store']);
        Route::patch('{examResult}/release', [ExamResultController::class, 'release']);
        Route::delete('{examResult}', [ExamResultController::class, 'destroy']);
    });
});
