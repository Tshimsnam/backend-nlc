<?php

/**
 * Test de l'API d'inscription via HTTP
 * Simule une vraie requête depuis le frontend
 * Usage: php test-api-inscription.php
 */

$baseUrl = 'http://192.168.58.9:8000';
$endpoint = '/api/events/1/register';

$payload = [
    'event_price_id' => 2,
    'full_name' => 'Franck Kapuya',
    'email' => 'franckkapuya13@gmail.com',
    'phone' => '+243822902681',
    'days' => 1,
    'pay_type' => 'online',
];

echo "=== Test API d'inscription ===\n\n";
echo "URL: {$baseUrl}{$endpoint}\n";
echo "Méthode: POST\n";
echo "Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

echo "Envoi de la requête...\n\n";

$ch = curl_init($baseUrl . $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur cURL: $error\n";
    exit(1);
}

echo "Code HTTP: $httpCode\n\n";

if ($httpCode === 201) {
    echo "✅ Inscription réussie!\n\n";
    $data = json_decode($response, true);
    
    echo "Réponse:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    if (isset($data['redirect_url'])) {
        echo "🔗 URL de redirection MaxiCash:\n";
        echo $data['redirect_url'] . "\n\n";
        
        echo "📋 Référence du ticket: " . ($data['reference'] ?? 'N/A') . "\n";
        echo "🆔 Log ID MaxiCash: " . ($data['log_id'] ?? 'N/A') . "\n\n";
        
        echo "✅ Le frontend devrait maintenant rediriger l'utilisateur vers cette URL.\n";
    }
} elseif ($httpCode === 422) {
    echo "❌ Erreur de validation (422)\n\n";
    $data = json_decode($response, true);
    
    echo "Réponse:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    if (isset($data['errors'])) {
        echo "Erreurs de validation:\n";
        foreach ($data['errors'] as $field => $errors) {
            echo "  - $field: " . implode(', ', $errors) . "\n";
        }
    } elseif (isset($data['message'])) {
        echo "Message: " . $data['message'] . "\n";
    }
} else {
    echo "❌ Erreur HTTP $httpCode\n\n";
    echo "Réponse brute:\n";
    echo $response . "\n";
}

echo "\n=== Fin du test ===\n";
