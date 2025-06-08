<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\VerifyApiSecret;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\RoleController;

Route::middleware([VerifyApiSecret::class])->group(function () {
    Route::post('/users', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/set-password', [SetPasswordController::class, 'setPassword']);

});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('roles', RoleController::class);

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
