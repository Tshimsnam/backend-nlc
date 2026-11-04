<?php

use App\Http\Controllers\Consortium\AuthController;
use App\Http\Controllers\Consortium\PartnerController;
use App\Http\Middleware\ConsortiumVerified;
use Illuminate\Support\Facades\Route;

// Chargé par web.php : session + protection CSRF, y compris pour les requêtes JSON.
Route::prefix('api/consortium')->name('consortium.')->group(function () {
    Route::get('/csrf', fn () => response()->json(['csrf_token' => csrf_token()])->header('Cache-Control', 'no-store'));
    Route::get('/config', [PartnerController::class, 'config']);
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::post('/verify-email', [AuthController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:5,1');
    Route::middleware(['auth:consortium', 'auth.session'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/account', [AuthController::class, 'updateAccount'])->middleware('throttle:5,1');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/resend-verification', [AuthController::class, 'resend'])->middleware('throttle:1,1');
        Route::get('/notifications', [PartnerController::class, 'notifications']);
        Route::post('/notifications/{notification}/read', [PartnerController::class, 'markRead']);
        Route::middleware(ConsortiumVerified::class)->group(function () {
            Route::get('/application', [PartnerController::class, 'show']);
            Route::get('/application/summary', [PartnerController::class, 'summary']);
            Route::put('/application', [PartnerController::class, 'save'])->middleware('throttle:30,1');
            Route::post('/application/submit', [PartnerController::class, 'submit'])->middleware('throttle:5,1');
            Route::post('/application/withdraw', [PartnerController::class, 'withdraw']);
            Route::post('/documents', [PartnerController::class, 'upload'])->middleware('throttle:15,1');
            Route::get('/documents/{document}', [PartnerController::class, 'download']);
            Route::post('/messages', [PartnerController::class, 'message'])->middleware('throttle:10,1');
        });
    });
});
