<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\TaskWorkLogController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Tasks routes - available for all authenticated users
    Route::resource('tasks', TaskController::class);
    
    // Task work logs
    Route::get('tasks/{task}/work-logs', [TaskController::class, 'workLogs'])->name('tasks.work-logs');
    Route::post('tasks/{task}/work-logs/bulk-update', [TaskWorkLogController::class, 'bulkUpdate'])->name('tasks.work-logs.bulk-update');
    Route::post('tasks/{task}/work-logs/add', [TaskWorkLogController::class, 'addWorkDay'])->name('tasks.work-logs.add');
    Route::delete('tasks/{task}/work-logs/{workLog}', [TaskWorkLogController::class, 'destroy'])->name('tasks.work-logs.destroy');
    
    // Task image removal
    Route::delete('tasks/{task}/images/{imageIndex}', [TaskController::class, 'removeImage'])->name('tasks.removeImage');
    
    // Task export - only for admin and kierownik
    Route::get('tasks/export/excel', [TaskController::class, 'export'])->name('tasks.export')->middleware('role:admin,kierownik');
    Route::get('tasks/export/daily', [TaskController::class, 'exportDaily'])->name('tasks.export.daily')->middleware('role:admin,kierownik');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('vehicles', VehicleController::class);
        Route::resource('users', UserController::class);
        Route::resource('teams', TeamController::class);
        
        // Settings routes
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        
        // Task Types management
        Route::get('settings/task-types', [TaskTypeController::class, 'index'])->name('settings.task-types.index');
        Route::post('settings/task-types', [TaskTypeController::class, 'store'])->name('settings.task-types.store');
        Route::put('settings/task-types/{taskType}', [TaskTypeController::class, 'update'])->name('settings.task-types.update');
        Route::delete('settings/task-types/{taskType}', [TaskTypeController::class, 'destroy'])->name('settings.task-types.destroy');
        Route::patch('settings/task-types/{taskType}/toggle-active', [TaskTypeController::class, 'toggleActive'])->name('settings.task-types.toggle-active');
    });
});

require __DIR__.'/auth.php';
