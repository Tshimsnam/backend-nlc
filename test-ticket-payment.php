<?php

/**
 * Script de test pour l'API Laravel - Création de ticket et paiement MaxiCash
 * Usage: php test-ticket-payment.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiUrl = $_ENV['APP_URL'] ?? 'http://192.168.241.9:8000';
$apiUrl = rtrim($apiUrl, '/');

echo "=== Test Laravel API - Ticket Payment ===\n\n";
echo "API URL: $apiUrl\n\n";

// Étape 1: Récupérer un événement
echo "1. Récupération des événements...\n";
$ch = curl_init("$apiUrl/api/events");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Erreur lors de la récupération des événements (HTTP $httpCode)\n";
    echo $response . "\n";
    exit(1);
}

$events = json_decode($response, true);
if (empty($events)) {
    echo "❌ Aucun événement trouvé. Créez d'abord un événement.\n";
    exit(1);
}

$event = $events[0];
$eventId = $event['id'];
echo "✅ Événement trouvé: {$event['title']} (ID: $eventId)\n\n";

// Étape 2: Récupérer les prix de l'événement
echo "2. Récupération des prix...\n";
$ch = curl_init("$apiUrl/api/events/$eventId/prices");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Erreur lors de la récupération des prix (HTTP $httpCode)\n";
    echo $response . "\n";
    exit(1);
}

$prices = json_decode($response, true);
if (empty($prices)) {
    echo "❌ Aucun prix trouvé pour cet événement.\n";
    exit(1);
}

$price = $prices[0];
$priceId = $price['id'];
echo "✅ Prix trouvé: {$price['category']} - {$price['amount']} {$price['currency']} (ID: $priceId)\n\n";

// Étape 3: Créer un ticket et initier le paiement
echo "3. Création du ticket et initiation du paiement...\n";

$payload = [
    'event_price_id' => $priceId,
    'full_name' => 'Test User ' . time(),
    'email' => 'test' . time() . '@example.com',
    'phone' => '+243999999999',
    'pay_type' => 'credit_card',
    'pay_sub_type' => null,
];

echo "Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init("$apiUrl/api/events/$eventId/register");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Code HTTP: $httpCode\n";

if ($error) {
    echo "❌ Erreur cURL: $error\n";
    exit(1);
}

echo "Réponse:\n";
$result = json_decode($response, true);
if ($result) {
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($httpCode === 201 && isset($result['success']) && $result['success']) {
        echo "✅ Succès!\n";
        echo "Référence: {$result['reference']}\n";
        echo "URL de redirection: {$result['redirect_url']}\n";
        echo "LogID: " . ($result['log_id'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Échec: " . ($result['message'] ?? 'Erreur inconnue') . "\n";
        
        if (isset($result['errors'])) {
            echo "\nErreurs de validation:\n";
            foreach ($result['errors'] as $field => $errors) {
                echo "  - $field: " . implode(', ', $errors) . "\n";
            }
        }
    }
} else {
    echo $response . "\n";
}

echo "\n=== Vérifiez les logs Laravel ===\n";
echo "tail -f storage/logs/laravel.log\n";
