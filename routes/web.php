<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AsekZestawController;
use App\Http\Controllers\DelegationController;
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

    // Tasks routes - available for all authenticated users except ksiegowy
    Route::middleware('role:admin,kierownik,lider,pracownik')->group(function () {
        Route::resource('tasks', TaskController::class);

        // Task work logs
        Route::get('tasks/{task}/work-logs', [TaskController::class, 'workLogs'])->name('tasks.work-logs');
        Route::post('tasks/{task}/work-logs/bulk-update', [TaskWorkLogController::class, 'bulkUpdate'])->name('tasks.work-logs.bulk-update');
        Route::post('tasks/{task}/work-logs/add', [TaskWorkLogController::class, 'addWorkDay'])->name('tasks.work-logs.add');
        Route::delete('tasks/{task}/work-logs/{workLog}', [TaskWorkLogController::class, 'destroy'])->name('tasks.work-logs.destroy');

        // Task image removal
        Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'removeAttachment'])->name('tasks.removeAttachment');

        // Absences routes - available for all authenticated users except ksiegowy
        Route::resource('absences', AbsenceController::class);

        // Absence approval routes - only for admin and kierownik
        Route::post('absences/{absence}/approve', [AbsenceController::class, 'approve'])->name('absences.approve')->middleware('role:admin,kierownik');
        Route::post('absences/{absence}/reject', [AbsenceController::class, 'reject'])->name('absences.reject')->middleware('role:admin,kierownik');
    });

    // Delegations list route - available for all authenticated users including ksiegowy
    Route::get('delegations', [DelegationController::class, 'index'])->name('delegations.index');

    // Group delegation routes (admin/kierownik only) - MUST be before {delegation} parameter routes
    Route::get('delegations/create-group', [DelegationController::class, 'createGroup'])->name('delegations.create-group')->middleware('role:admin,kierownik');
    Route::post('delegations/store-group', [DelegationController::class, 'storeGroup'])->name('delegations.store-group')->middleware('role:admin,kierownik');

    // Delegations management routes - NOT available for ksiegowy (MUST be before {delegation} parameter routes)
    Route::middleware('role:admin,kierownik,lider,pracownik')->group(function () {
        Route::get('delegations/create', [DelegationController::class, 'create'])->name('delegations.create');
        Route::post('delegations', [DelegationController::class, 'store'])->name('delegations.store');
        Route::get('delegations/{delegation}/edit', [DelegationController::class, 'edit'])->name('delegations.edit');
        Route::put('delegations/{delegation}', [DelegationController::class, 'update'])->name('delegations.update');
        Route::delete('delegations/{delegation}', [DelegationController::class, 'destroy'])->name('delegations.destroy');

        // Delegation approval routes
        Route::post('delegations/{delegation}/employee-approval', [DelegationController::class, 'employeeApproval'])->name('delegations.employee-approval');
        Route::post('delegations/{delegation}/supervisor-approval', [DelegationController::class, 'supervisorApproval'])->name('delegations.supervisor-approval');
        Route::post('delegations/{delegation}/revoke-approval', [DelegationController::class, 'revokeApproval'])->name('delegations.revoke-approval');
    });

    // Delegations view route with parameter - available for all authenticated users including ksiegowy (MUST be AFTER specific routes)
    Route::get('delegations/{delegation}', [DelegationController::class, 'show'])->name('delegations.show');

    // PDF generation route - available for all including ksiegowy
    Route::get('delegations/{delegation}/pdf', [DelegationController::class, 'generatePdf'])->name('delegations.pdf');

    // Delegation export - only for admin and ksiegowy
    Route::get('delegations/export/excel', [DelegationController::class, 'export'])->name('delegations.export')->middleware('role:admin,ksiegowy');

    // Task export - only for admin and kierownik
    Route::get('tasks/export/excel', [TaskController::class, 'export'])->name('tasks.export')->middleware('role:admin,kierownik');
    Route::get('tasks/export/daily', [TaskController::class, 'exportDaily'])->name('tasks.export.daily')->middleware('role:admin,kierownik');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('vehicles', VehicleController::class);
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('teams', TeamController::class);
        
        // Settings routes
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/smtp', [SettingController::class, 'updateSmtp'])->name('settings.smtp.update');
        Route::post('settings/smtp/test', [SettingController::class, 'testSmtp'])->name('settings.smtp.test');
        
        // Task Types management
        Route::get('settings/task-types', [TaskTypeController::class, 'index'])->name('settings.task-types.index');
        Route::post('settings/task-types', [TaskTypeController::class, 'store'])->name('settings.task-types.store');
        Route::put('settings/task-types/{taskType}', [TaskTypeController::class, 'update'])->name('settings.task-types.update');
        Route::delete('settings/task-types/{taskType}', [TaskTypeController::class, 'destroy'])->name('settings.task-types.destroy');
        Route::patch('settings/task-types/{taskType}/toggle-active', [TaskTypeController::class, 'toggleActive'])->name('settings.task-types.toggle-active');
        
        // Delegation Settings management
        Route::get('settings/delegations', [\App\Http\Controllers\Settings\DelegationSettingsController::class, 'index'])->name('settings.delegations.index');
        Route::put('settings/delegations', [\App\Http\Controllers\Settings\DelegationSettingsController::class, 'update'])->name('settings.delegations.update');
        Route::put('settings/delegations/defaults', [\App\Http\Controllers\Settings\DelegationSettingsController::class, 'updateDefaults'])->name('settings.delegations.update-defaults');
    });

    // ASEK Zestawy routes (read-only from external database)
    Route::prefix('asek')->name('asek.')->group(function () {
        Route::get('zestawy', [AsekZestawController::class, 'index'])->name('zestawy.index');
        Route::get('zestawy/{id}', [AsekZestawController::class, 'show'])->name('zestawy.show');
    });
});

require __DIR__.'/auth.php';
