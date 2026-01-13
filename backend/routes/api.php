<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleMemoController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskRemarkController;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });

    Route::apiResource('schedules', ScheduleController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('schedules/{schedule}/memos', [ScheduleMemoController::class, 'store']);
    Route::get('tasks/completed', [TaskController::class, 'completed']);
    Route::apiResource('tasks', TaskController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('tasks/{task}/remarks', [TaskRemarkController::class, 'store']);
    Route::apiResource('users', \App\Http\Controllers\UserController::class)->only(['index']);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('departments', [DepartmentController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'check.role:admin,manager,auditor'])->prefix('admin')->group(function () {
    Route::apiResource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->only(['index', 'store', 'update']);
    Route::apiResource('projects', \App\Http\Controllers\Admin\ProjectController::class)->only(['index', 'store', 'update']);
    Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class);
});
