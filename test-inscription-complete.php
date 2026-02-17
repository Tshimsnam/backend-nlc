<?php

/**
 * Test complet d'inscription avec MaxiCash
 * Usage: php test-inscription-complete.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test d'inscription complète ===\n\n";

// Payload de test
$payload = [
    'event_price_id' => 2,
    'full_name' => 'Franck Kapuya',
    'email' => 'franckkapuya13@gmail.com',
    'phone' => '+243822902681',
    'days' => 1,
    'pay_type' => 'online',
];

echo "1. Payload de test:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

try {
    // Simuler la requête
    $event = \App\Models\Event::findOrFail(1);
    $price = \App\Models\EventPrice::where('id', $payload['event_price_id'])
        ->where('event_id', $event->id)
        ->firstOrFail();
    
    echo "2. Création du ticket:\n";
    
    $ticket = \App\Models\Ticket::create([
        'event_id' => $event->id,
        'event_price_id' => $price->id,
        'full_name' => $payload['full_name'],
        'email' => $payload['email'],
        'phone' => $payload['phone'],
        'category' => $price->category,
        'days' => $payload['days'] ?? 1,
        'amount' => $price->amount,
        'currency' => $price->currency,
        'reference' => strtoupper(\Illuminate\Support\Str::random(10)),
        'pay_type' => $payload['pay_type'],
        'pay_sub_type' => null,
        'payment_status' => 'pending',
    ]);
    
    echo "   ✓ Ticket créé avec succès\n";
    echo "   - ID: {$ticket->id}\n";
    echo "   - Référence: {$ticket->reference}\n";
    echo "   - Montant: {$ticket->amount} {$ticket->currency}\n";
    echo "   - Statut: {$ticket->payment_status}\n\n";
    
    echo "3. Initialisation du paiement MaxiCash:\n";
    
    $maxiCash = new \App\Services\Payments\MaxiCashService();
    
    $baseUrl = rtrim(config('app.url'), '/');
    $frontendUrl = rtrim(env('FRONTEND_NLC', $baseUrl), '/');
    
    $successUrl = config('services.maxicash.success_url') ?? "{$frontendUrl}/paiement/success";
    $failureUrl = config('services.maxicash.failure_url') ?? "{$frontendUrl}/paiement/failure";
    $cancelUrl = config('services.maxicash.cancel_url') ?? $failureUrl;
    
    // Ajouter la référence aux URLs
    $separator = strpos($successUrl, '?') !== false ? '&' : '?';
    $successUrl .= $separator . 'reference=' . $ticket->reference;
    
    $separator = strpos($failureUrl, '?') !== false ? '&' : '?';
    $failureUrl .= $separator . 'reference=' . $ticket->reference;
    
    $separator = strpos($cancelUrl, '?') !== false ? '&' : '?';
    $cancelUrl .= $separator . 'reference=' . $ticket->reference;
    
    $urls = [
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'failure_url' => $failureUrl,
        'notify_url' => config('services.maxicash.notify_url') ?? "{$baseUrl}/api/webhooks/maxicash",
    ];
    
    echo "   URLs de callback:\n";
    foreach ($urls as $key => $url) {
        echo "   - $key: $url\n";
    }
    echo "\n";
    
    $result = $maxiCash->initiatePaymentForTicket($ticket, $urls);
    
    if ($result['success'] ?? false) {
        echo "   ✓ Paiement initié avec succès\n";
        echo "   - Log ID: " . ($result['log_id'] ?? 'N/A') . "\n";
        echo "   - Redirect URL: " . ($result['redirect_url'] ?? 'N/A') . "\n\n";
        
        echo "4. Réponse JSON qui serait retournée au frontend:\n";
        $response = [
            'success' => true,
            'payment_mode' => 'online',
            'reference' => $ticket->reference,
            'redirect_url' => $result['redirect_url'],
            'log_id' => $result['log_id'] ?? null,
            'message' => 'Redirection vers MaxiCash pour finaliser le paiement (Mobile Money, Visa, Carte ou PayPal).',
        ];
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "   ✗ Échec de l'initialisation du paiement\n";
        echo "   - Message: " . ($result['message'] ?? 'Erreur inconnue') . "\n\n";
        
        echo "4. Réponse d'erreur qui serait retournée au frontend:\n";
        $response = [
            'success' => false,
            'message' => $result['message'] ?? 'Impossible d\'initier le paiement.',
            'ticket' => [
                'reference' => $ticket->reference,
                'amount' => $ticket->amount,
                'currency' => $ticket->currency,
            ],
        ];
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Nettoyer le ticket de test
    echo "\n5. Nettoyage:\n";
    $ticket->delete();
    echo "   ✓ Ticket de test supprimé\n";
    
} catch (\Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n";
