<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\MasterData\AcademicYearController;
use App\Http\Controllers\Api\V1\MasterData\GuardianController;
use App\Http\Controllers\Api\V1\MasterData\SectionController;
use App\Http\Controllers\Api\V1\MasterData\SchoolClassController;
use App\Http\Controllers\Api\V1\SIS\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware(['auth:api', 'tenant.resolve'])->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard/overview', [DashboardController::class, 'overview'])->middleware('permission:students.view');

        Route::get('/students', [StudentController::class, 'index'])->middleware('permission:students.view');
        Route::post('/students', [StudentController::class, 'store'])->middleware('permission:students.create');
        Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:students.view');
        Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete');
        Route::post('/students/{student}/documents', [StudentController::class, 'uploadDocument'])->middleware('permission:students.documents.upload');

        Route::get('/guardians', [GuardianController::class, 'index'])->middleware('permission:students.view');
        Route::post('/guardians', [GuardianController::class, 'store'])->middleware('permission:students.create');
        Route::put('/guardians/{guardian}', [GuardianController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])->middleware('permission:students.delete');

        Route::get('/academic-years', [AcademicYearController::class, 'index'])->middleware('permission:students.view');
        Route::post('/academic-years', [AcademicYearController::class, 'store'])->middleware('permission:students.create');

        Route::get('/classes', [SchoolClassController::class, 'index'])->middleware('permission:students.view');
        Route::post('/classes', [SchoolClassController::class, 'store'])->middleware('permission:students.create');

        Route::get('/sections', [SectionController::class, 'index'])->middleware('permission:students.view');
        Route::post('/sections', [SectionController::class, 'store'])->middleware('permission:students.create');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->middleware('permission:students.delete');
    });
});
