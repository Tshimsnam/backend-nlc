<?php

/**
 * Script de débogage pour l'inscription MaxiCash
 * Usage: php test-inscription-debug.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test de configuration MaxiCash ===\n\n";

// 1. Vérifier la configuration
echo "1. Configuration MaxiCash:\n";
echo "   - Merchant ID: " . config('services.maxicash.merchant_id') . "\n";
echo "   - Sandbox: " . (config('services.maxicash.sandbox') ? 'OUI' : 'NON') . "\n";
echo "   - API URL: " . config('services.maxicash.api_url') . "\n";
echo "   - Redirect Base: " . config('services.maxicash.redirect_base') . "\n";
echo "   - Success URL: " . config('services.maxicash.success_url') . "\n";
echo "   - Failure URL: " . config('services.maxicash.failure_url') . "\n";
echo "   - Cancel URL: " . config('services.maxicash.cancel_url') . "\n";
echo "   - Notify URL: " . config('services.maxicash.notify_url') . "\n\n";

// 2. Vérifier l'événement et les prix
echo "2. Vérification de l'événement ID=1:\n";
$event = \App\Models\Event::find(1);
if ($event) {
    echo "   ✓ Événement trouvé: {$event->title}\n";
    echo "   - Capacité: " . ($event->capacity ?? 'illimitée') . "\n";
    echo "   - Inscrits: " . ($event->registered ?? 0) . "\n\n";
    
    echo "3. Prix disponibles pour cet événement:\n";
    $prices = \App\Models\EventPrice::where('event_id', 1)->get();
    if ($prices->count() > 0) {
        foreach ($prices as $price) {
            echo "   - ID: {$price->id} | Catégorie: {$price->category} | Durée: {$price->duration_type} | Prix: {$price->amount} {$price->currency}\n";
        }
    } else {
        echo "   ✗ Aucun prix trouvé pour cet événement\n";
    }
} else {
    echo "   ✗ Événement non trouvé\n";
}

echo "\n4. Test de validation du payload:\n";
$testPayload = [
    'event_price_id' => 2,
    'full_name' => 'Franck Kapuya',
    'email' => 'franckkapuya13@gmail.com',
    'phone' => '+243822902681',
    'days' => 1,
    'pay_type' => 'online',
];

$validator = \Illuminate\Support\Facades\Validator::make($testPayload, [
    'event_price_id' => ['required', 'integer', 'exists:event_prices,id'],
    'full_name' => ['required', 'string', 'max:255', 'min:3'],
    'email' => ['required', 'email', 'max:255'],
    'phone' => ['required', 'string', 'max:50', 'min:9'],
    'days' => ['nullable', 'integer', 'min:1'],
    'pay_type' => ['required', 'string', 'max:50', 'in:online,cash'],
]);

if ($validator->fails()) {
    echo "   ✗ Validation échouée:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "     - $error\n";
    }
} else {
    echo "   ✓ Payload valide\n";
    
    // Vérifier si le prix existe
    $price = \App\Models\EventPrice::find($testPayload['event_price_id']);
    if ($price) {
        echo "   ✓ Prix trouvé: {$price->amount} {$price->currency} (Catégorie: {$price->category}, Durée: {$price->duration_type})\n";
    } else {
        echo "   ✗ Prix ID={$testPayload['event_price_id']} non trouvé dans la base de données\n";
    }
}

echo "\n5. Test de création de ticket (simulation):\n";
try {
    $event = \App\Models\Event::findOrFail(1);
    $price = \App\Models\EventPrice::findOrFail($testPayload['event_price_id']);
    
    echo "   ✓ Événement et prix trouvés\n";
    echo "   - Référence qui serait générée: " . strtoupper(\Illuminate\Support\Str::random(10)) . "\n";
    echo "   - Montant: {$price->amount} {$price->currency}\n";
    echo "   - Statut initial: pending\n";
    
} catch (\Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n6. URLs de callback qui seraient utilisées:\n";
$baseUrl = rtrim(config('app.url'), '/');
$frontendUrl = rtrim(env('FRONTEND_NLC', $baseUrl), '/');
$successUrl = config('services.maxicash.success_url') ?? "{$frontendUrl}/paiement/success";
$failureUrl = config('services.maxicash.failure_url') ?? "{$frontendUrl}/paiement/failure";
$cancelUrl = config('services.maxicash.cancel_url') ?? $failureUrl;
$notifyUrl = config('services.maxicash.notify_url') ?? "{$baseUrl}/api/webhooks/maxicash";

echo "   - Success: $successUrl\n";
echo "   - Failure: $failureUrl\n";
echo "   - Cancel: $cancelUrl\n";
echo "   - Notify: $notifyUrl\n";

echo "\n=== Fin du test ===\n";
