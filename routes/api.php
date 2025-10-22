<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\VerifyApiSecret;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\SettingController;

Route::middleware([VerifyApiSecret::class])->group(function () {
    Route::post('/users', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/set-password', [SetPasswordController::class, 'setPassword']);

});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('roles', RoleController::class);
    Route::get('/roles/{id}/users', [RoleController::class, 'users']);
    Route::get('/users', [UserController::class, 'index']);

    // Routes pour les enfants (sauf DELETE)
    Route::apiResource('children', ChildController::class)->except(['destroy']);

    // Routes pour les programmes (sauf DELETE)
    Route::apiResource('programs', ProgramController::class)->except(['destroy']);

    // Routes pour les cours (sauf DELETE)
    Route::apiResource('courses', CourseController::class)->except(['destroy']);

    // Routes pour les rendez-vous (sauf DELETE)
    Route::apiResource('appointments', AppointmentController::class)->except(['destroy']);

    // Routes pour les messages (sauf DELETE)
    Route::apiResource('messages', MessageController::class)->except(['destroy']);

    // Routes pour les rapports (sauf DELETE)
    Route::apiResource('reports', ReportController::class)->except(['destroy']);

    // Routes pour les notifications (sauf DELETE)
    Route::apiResource('notifications', NotificationController::class)->except(['destroy']);

    // Routes pour les dossiers (sauf DELETE)
    Route::apiResource('dossiers', DossierController::class)->except(['destroy']);

    // Routes pour les paramètres (sauf DELETE)
    Route::apiResource('settings', SettingController::class)->except(['destroy']);

    // Routes DELETE - Réservées aux administrateurs uniquement
    Route::middleware(['admin.only'])->group(function () {
        Route::delete('/children/{child}', [ChildController::class, 'destroy'])->name('children.destroy');
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
        Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/dossiers/{dossier}', [DossierController::class, 'destroy'])->name('dossiers.destroy');
        Route::delete('/settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
    });

    // Route protégée par rôle admin
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Bienvenue Admin']);
    })->middleware('role:Administrateur');

    // par rôle Educateur
    Route::get('/educateur/dashboard', function (){
        return response()->json(['message'=>'Bienvenue cher educateur']);
    })->middleware('role:Educateur');

    //par rôle Parent
    Route::get('parent/dashboard', function() {
        return response()->json(['message'=>'bienvenue cher parent']);
    })->middleware('role:Parent');

    //

    Route::get('Super Teacher/dashboard', function(){
        return response()->json(['message'=>'Super Teacher connecté']);
    })->middleware('role:Super Teacher');
});
