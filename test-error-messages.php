<?php

/**
 * Test des messages d'erreur personnalisés
 * Usage: php test-error-messages.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');

echo "🧪 Test des messages d'erreur personnalisés\n";
echo "URL de base: {$baseUrl}\n\n";

$tests = [
    [
        'name' => 'Recherche ticket inexistant',
        'url' => '/api/tickets/TKT-INEXISTANT',
        'method' => 'GET',
    ],
    [
        'name' => 'Recherche par téléphone (aucun résultat)',
        'url' => '/api/tickets/search?phone=0000000000',
        'method' => 'GET',
    ],
    [
        'name' => 'Validation ticket inexistant',
        'url' => '/api/tickets/TKT-INEXISTANT/validate-cash',
        'method' => 'POST',
    ],
    [
        'name' => 'Notification ticket inexistant',
        'url' => '/api/tickets/TKT-INEXISTANT/send-notification',
        'method' => 'POST',
    ],
    [
        'name' => 'Réservation inexistante',
        'url' => '/api/reservations/TKT-INEXISTANT',
        'method' => 'GET',
    ],
];

foreach ($tests as $test) {
    echo "📝 Test: {$test['name']}\n";
    echo "   URL: {$test['url']}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $test['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $test['method']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode === 404 && isset($data['success']) && $data['success'] === false) {
        echo "   ✅ Status: {$httpCode}\n";
        echo "   ✅ Message: {$data['message']}\n";
    } else {
        echo "   ❌ Status: {$httpCode}\n";
        echo "   ❌ Réponse: " . json_encode($data) . "\n";
    }
    
    echo "\n";
}

echo "✅ Tests terminés !\n";
