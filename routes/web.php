<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    // Rediriger vers le frontend
    // $frontendUrl = env('FRONTEND_WEBSITE_URL', 'http://localhost:8080');
    // return redirect($frontendUrl);
});

Route::get('/health', fn () => response()->json(['ok' => true]));


Route::get('/test-mail', function () {
    Mail::raw('Ceci est un test depuis Laravel !', function ($message) {
        $message->to('manassetshims@gmail.com')
                ->subject('Test de Laravel');
    });

    return 'Mail envoyé !';
});

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'webLogin'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

// Route de logout pour l'admin web
Route::post('/admin/logout', function () {
    session()->forget('admin_token');
    session()->forget('admin_user');
    return redirect()->route('login');
})->name('admin.logout');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Routes Admin Dashboard (Vue Blade)
Route::get('/admin', [DashboardController::class, 'view'])->name('admin.dashboard.view');
Route::post('/admin/tickets/{reference}/validate', [DashboardController::class, 'validateTicketWeb'])->name('admin.tickets.validate.web');
Route::post('/admin/agents/create', [DashboardController::class, 'createAgent'])->name('admin.agents.create');
Route::put('/admin/events/{id}/update', [DashboardController::class, 'updateEvent'])->name('admin.events.update');
Route::delete('/admin/event-prices/{id}', [DashboardController::class, 'deleteEventPrice'])->name('admin.event-prices.delete');

// Routes Admin Dashboard (API JSON)
Route::prefix('admin')->middleware(['auth:sanctum', 'admin.only'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/tickets/pending', [DashboardController::class, 'pendingTickets'])->name('admin.tickets.pending');
    Route::post('/tickets/{reference}/validate-api', [DashboardController::class, 'validateTicket'])->name('admin.tickets.validate');
    Route::get('/users', [DashboardController::class, 'users'])->name('admin.users');
    Route::get('/events/stats', [DashboardController::class, 'eventsStats'])->name('admin.events.stats');
});

