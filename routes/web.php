<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
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
    
    // Task image removal
    Route::delete('tasks/{task}/images/{imageIndex}', [TaskController::class, 'removeImage'])->name('tasks.removeImage');
    
    // Task export - only for admin and kierownik
    Route::get('tasks/export/excel', [TaskController::class, 'export'])->name('tasks.export')->middleware('role:admin,kierownik');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('vehicles', VehicleController::class);
        Route::resource('users', UserController::class);
        Route::resource('teams', TeamController::class);
        
        // Settings routes
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
