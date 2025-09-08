<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ImportController;

// ------------------- Public Auth Routes -------------------
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

// ------------------- Protected Routes -------------------
Route::middleware('auth:api')->group(function () {
    // 🔑 Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // 👤 User profile
    Route::get('users/me', [UserController::class, 'me']);
    Route::put('users/me', [UserController::class, 'update']);

    // ✅ Tasks CRUD
    Route::get('tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{id}', [TaskController::class, 'show']);
    Route::put('tasks/{id}', [TaskController::class, 'update']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);

    // 📂 File Upload (PDF/Image)
    Route::post('import', [ImportController::class, 'upload']);
});

// ------------------- Admin Routes -------------------
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('users', function () {
        return App\Models\User::paginate(20);
    });
});
