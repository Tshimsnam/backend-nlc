<?php

use App\Http\Controllers\Orientation\PortalController;
use App\Http\Middleware\OrientationSession;
use Illuminate\Support\Facades\Route;

Route::prefix('api/orientation')->name('orientation.')->group(function () {
    Route::get('/config', [PortalController::class, 'config']);
    Route::get('/csrf', fn () => response()->json(['csrf_token' => csrf_token()])->header('Cache-Control', 'no-store'));
    Route::post('/access/request', [PortalController::class, 'requestAccess'])->middleware('throttle:3,1,orientation_1');
    Route::post('/access/consume', [PortalController::class, 'consume'])->middleware('throttle:10,1,orientation_2');
    Route::middleware(OrientationSession::class)->group(function () {
        Route::get('/me', [PortalController::class, 'me']);
        Route::post('/logout', [PortalController::class, 'logout']);
        Route::get('/draft', [PortalController::class, 'draft']);
        Route::put('/draft', [PortalController::class, 'saveDraft'])->middleware('throttle:20,1,orientation_3');
        Route::get('/requests', [PortalController::class, 'index']);
        Route::post('/requests', [PortalController::class, 'store'])->middleware('throttle:5,1,orientation_4');
        Route::get('/requests/{consultation}', [PortalController::class, 'show']);
        Route::post('/requests/{consultation}/guardian', [PortalController::class, 'guardian'])->middleware('throttle:5,1,orientation_5');
        Route::post('/requests/{consultation}/guardian/resend', [PortalController::class, 'resendGuardian'])->middleware('throttle:1,1,orientation_6');
        Route::post('/requests/{consultation}/supplement', [PortalController::class, 'supplement'])->middleware('throttle:5,1,orientation_7');
        Route::post('/requests/{consultation}/documents', [PortalController::class, 'upload'])->middleware('throttle:10,1,orientation_8');
        Route::get('/requests/{consultation}/documents/{index}', [PortalController::class, 'document'])->whereNumber('index');
        Route::post('/requests/{consultation}/messages', [PortalController::class, 'message'])->middleware('throttle:10,1,orientation_9');
        Route::post('/appointments/{appointment}', [PortalController::class, 'appointment'])->middleware('throttle:10,1,orientation_10');
        Route::post('/appointments/{appointment}/pay', [PortalController::class, 'pay'])->middleware('throttle:3,1,orientation_11');
        Route::post('/payments/{payment}/report', [PortalController::class, 'reportPayment'])->middleware('throttle:3,1,orientation_12');
        Route::post('/payments/{payment}/check', [PortalController::class, 'checkPayment'])->middleware('throttle:1,1,orientation_13');
        Route::get('/notifications', [PortalController::class, 'notifications']);
        Route::post('/notifications/{notice}/read', [PortalController::class, 'read']);
    });
});
// L'ancien chemin de dépôt utilise désormais la même session vérifiée et les mêmes règles.
Route::post('/api/consultations', [PortalController::class, 'store'])->middleware([OrientationSession::class, 'throttle:5,1,orientation_14']);
