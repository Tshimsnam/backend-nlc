<?php

/**
 * Test de la réponse de l'API pour un ticket
 * Usage: php test-ticket-response.php [reference]
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
$reference = $argv[1] ?? '3LN00ULCMK';

echo "🔍 Test de la réponse API pour le ticket\n";
echo "URL: {$baseUrl}/api/tickets/{$reference}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/api/tickets/{$reference}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: {$httpCode}\n\n";

$data = json_decode($response, true);

if ($data) {
    echo "📦 Structure de la réponse:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    // Vérifier la présence de la référence
    if (isset($data['reference'])) {
        echo "✅ Référence trouvée à la racine: {$data['reference']}\n";
    } elseif (isset($data['ticket']['reference'])) {
        echo "✅ Référence trouvée dans ticket: {$data['ticket']['reference']}\n";
    } else {
        echo "❌ Référence non trouvée dans la réponse\n";
    }
    
    // Vérifier success
    if (isset($data['success'])) {
        echo "✅ Champ 'success': " . ($data['success'] ? 'true' : 'false') . "\n";
    } else {
        echo "❌ Champ 'success' manquant\n";
    }
} else {
    echo "❌ Réponse invalide (pas de JSON)\n";
    echo "Réponse brute:\n{$response}\n";
}
