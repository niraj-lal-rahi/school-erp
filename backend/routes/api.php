<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\SIS\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware(['auth:api', 'tenant.resolve'])->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::prefix('sis')->group(function (): void {
            Route::get('/students', [StudentController::class, 'index'])->middleware('permission:students.view');
            Route::post('/students', [StudentController::class, 'store'])->middleware('permission:students.create');
            Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:students.view');
            Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('permission:students.update');
            Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete');
        });
    });
});
