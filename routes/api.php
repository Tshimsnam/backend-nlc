<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnfantController;
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
use App\Http\Controllers\API\EventController as APIEventController;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\Webhooks\MaxiCashWebhookController;
use App\Http\Controllers\Webhooks\MpesaWebhookController;
use App\Http\Controllers\Webhooks\OrangeMoneyWebhookController;
use App\Http\Controllers\API\EventPriceController;
use App\Http\Controllers\API\EventScanController;
use App\Http\Controllers\API\QRScanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\API\PhysicalTicketController;

// --- Événements (lecture publique)
Route::get('/events', [APIEventController::class, 'index']);
Route::get('/events/{event:slug}', [APIEventController::class, 'show'])->name('api.events.show.slug');
Route::get('/events/id/{event}', [APIEventController::class, 'show'])->name('api.events.show.id');

// --- Scan QR Code événement
Route::post('/events/{slug}/scan', [EventScanController::class, 'recordScan']);
Route::get('/events/{slug}/scans', [EventScanController::class, 'getEventScans']);

// --- Prix des événements (lecture publique pour affichage frontend)
Route::get('/events/{event}/prices', [EventPriceController::class, 'index']);

// --- Inscription à un événement (création ticket) + modes de paiement
Route::post('/events/{event}/register', [TicketController::class, 'store']);
Route::get('/events/{event}/tickets/payment-modes', [TicketController::class, 'paymentModes']);

// --- Système de réservation en 2 étapes (NOUVEAU)
// Étape 1: Créer une réservation (référence) avec juste event_price_id
Route::post('/events/{event}/reserve', [ReservationController::class, 'createReservation']);
// Étape 2: Compléter la réservation avec les informations du participant
Route::post('/reservations/{reference}/complete', [ReservationController::class, 'completeReservation']);
// Vérifier le statut d'une réservation
Route::get('/reservations/{reference}', [ReservationController::class, 'checkReservation']);

// --- Test route
Route::get('/test', function() {
    return response()->json(['message' => 'API fonctionne!', 'timestamp' => now()]);
});

// --- Inscription participant & paiement (flux alternatif - DÉSACTIVÉ, utiliser /events/{event}/register)
// Route::post('/register', [RegistrationController::class, 'store']);
Route::post('/payments/initiate', [PaymentController::class, 'initiate']);

// --- Recherche de tickets par téléphone
Route::get('/tickets/search', [TicketController::class, 'searchByPhone']);

// --- Ticket par référence (lecture)
Route::get('/tickets/{ticketNumber}', [TicketController::class, 'show']);

// --- Envoyer une notification par email pour un ticket
Route::post('/tickets/{ticketNumber}/send-notification', [TicketController::class, 'sendNotification']);

// --- Scan de billets (QR Code) - Accessible à tous les utilisateurs connectés
// Scanner un billet (via QR code, référence ou téléphone)
Route::post('/tickets/scan', [QRScanController::class, 'scan'])->middleware('auth:sanctum');
// Historique des scans d'un billet
Route::get('/tickets/{reference}/scans', [QRScanController::class, 'getScanHistory'])->middleware('auth:sanctum');
// Statistiques de scan pour un événement
Route::get('/events/{eventId}/scan-stats', [QRScanController::class, 'getEventScanStats'])->middleware('auth:sanctum');

// --- Billets physiques (QR codes pré-générés)
// Vérifier si un QR physique est déjà activé
Route::post('/tickets/physical/check', [PhysicalTicketController::class, 'checkPhysicalQR'])->middleware('auth:sanctum');
// Créer un ticket depuis un QR code physique
Route::post('/tickets/physical', [PhysicalTicketController::class, 'createFromPhysicalQR'])->middleware('auth:sanctum');
// Récupérer les prix d'un événement pour les billets physiques
Route::get('/events/{eventId}/prices-for-physical', [PhysicalTicketController::class, 'getEventPrices'])->middleware('auth:sanctum');

// --- Webhooks pour les paiements
Route::match(['get', 'post'], '/webhooks/maxicash', [MaxiCashWebhookController::class, 'handle'])
    ->middleware('maxicash.signature');

Route::post('/webhooks/mpesa', [MpesaWebhookController::class, 'handle']);

Route::post('/webhooks/orange-money', [OrangeMoneyWebhookController::class, 'handle']);

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

      //store de l'enfant
    Route::post('/store_enfant', [EnfantController::class, 'store']);

     //show des infos de l'enfant
    Route::get('/show_enfant/{id}', [EnfantController::class, 'show']);

    //index de la liste des enfants
    Route::get('index_enfant', [EnfantController::class, 'index']);

    //destroy des infos sur l'infant
    Route::delete('/destroy_enfant/{id}', [EnfantController::class, 'destroy']);

    // Événements : création, modification, suppression réservées à l'admin
    Route::post('/events', [APIEventController::class, 'store'])->middleware('admin.only');
    Route::put('/events/{event}', [APIEventController::class, 'update'])->middleware('admin.only');
    Route::delete('/events/{event}', [APIEventController::class, 'destroy'])->middleware('admin.only');

    // Tarifs par événement (admin) - création, modification, suppression uniquement
    Route::post('/events/{event}/prices', [EventPriceController::class, 'store'])->middleware('admin.only');
    Route::put('/events/{event}/prices/{eventPrice}', [EventPriceController::class, 'update'])->middleware('admin.only');
    Route::delete('/events/{event}/prices/{eventPrice}', [EventPriceController::class, 'destroy'])->middleware('admin.only');

    // Validation des paiements en caisse - Accessible à tous les utilisateurs connectés
    Route::get('/tickets/pending-cash', [TicketController::class, 'pendingCashPayments']);
    Route::post('/tickets/{ticketNumber}/validate-cash', [TicketController::class, 'validateCashPayment']);

    // Routes admin pour le dashboard
    Route::prefix('admin')->middleware('admin.only')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/pending-tickets', [DashboardController::class, 'pendingTickets']);
        Route::post('/dashboard/validate-ticket/{reference}', [DashboardController::class, 'validateTicket']);
        Route::get('/dashboard/users', [DashboardController::class, 'users']);
        Route::get('/dashboard/events-stats', [DashboardController::class, 'eventsStats']);
        Route::get('/dashboard/scan-stats', [DashboardController::class, 'scanStats']);
    });

    // Statistiques de l'agent connecté (pour l'app mobile)
    Route::get('/my-stats', [DashboardController::class, 'myStats']);
});



