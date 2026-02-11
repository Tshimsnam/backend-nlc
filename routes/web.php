<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/', function () {
    // Rediriger vers le frontend (page événements)
    $frontendUrl = env('FRONTEND_WEBSITE_URL', 'http://localhost:8080');
    return redirect($frontendUrl . '/evenements');
});

Route::get('/health', fn () => response()->json(['ok' => true]));


Route::get('/test-mail', function () {
    Mail::raw('Ceci est un test depuis Laravel !', function ($message) {
        $message->to('manassetshims@gmail.com')
                ->subject('Test de Laravel');
    });

    return 'Mail envoyé !';
});

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

