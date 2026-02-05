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
use App\Http\Controllers\Webhooks\MaxiCashWebhookController;
use App\Http\Controllers\API\EventPriceController;

// --- Événements (lecture publique)
Route::get('/events', [APIEventController::class, 'index']);
Route::get('/events/{event}', [APIEventController::class, 'show']);

// --- Inscription participant & paiement
Route::post('/register', [RegistrationController::class, 'store']);
Route::post('/payments/initiate', [PaymentController::class, 'initiate']);

// --- Ticket par numéro (lecture)
Route::get('/tickets/{ticketNumber}', [TicketController::class, 'show']);

// --- Webhook MaxiCash (vérification signature)
Route::post('/webhooks/maxicash', [MaxiCashWebhookController::class, 'handle'])
    ->middleware('maxicash.signature');

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

    // Tarifs par événement (admin)
    Route::get('/events/{event}/prices', [EventPriceController::class, 'index'])->middleware('admin.only');
    Route::post('/events/{event}/prices', [EventPriceController::class, 'store'])->middleware('admin.only');
    Route::put('/events/{event}/prices/{eventPrice}', [EventPriceController::class, 'update'])->middleware('admin.only');
    Route::delete('/events/{event}/prices/{eventPrice}', [EventPriceController::class, 'destroy'])->middleware('admin.only');
});



