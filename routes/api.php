<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderExamController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'ability:refresh'])->group(function () {
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
});

Route::middleware(['auth:sanctum', 'ability:access'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('user/me', [UserController::class, 'me']);
    Route::apiResource('user', UserController::class);
    Route::apiResource('exam', ExamController::class);
    Route::apiResource('partner', PartnerController::class);
    Route::apiResource('patient', PatientController::class);
    Route::apiResource('order', OrderController::class);
    Route::apiResource('financial', FinancialController::class);
});
