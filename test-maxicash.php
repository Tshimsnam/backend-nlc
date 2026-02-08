<?php

/**
 * Script de test MaxiCash - À exécuter via: php test-maxicash.php
 * Ce script teste la connexion à l'API MaxiCash avec vos identifiants
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$merchantId = $_ENV['MAXICASH_MERCHANT_ID'] ?? '';
$merchantPassword = $_ENV['MAXICASH_MERCHANT_PASSWORD'] ?? '';
$apiUrl = $_ENV['MAXICASH_API_URL'] ?? 'https://webapi-test.maxicashapp.com';

echo "=== Test MaxiCash PayEntryWeb ===\n\n";
echo "MerchantID: " . ($merchantId ? substr($merchantId, 0, 8) . '...' : 'NON CONFIGURÉ') . "\n";
echo "MerchantPassword: " . ($merchantPassword ? '***' : 'NON CONFIGURÉ') . "\n";
echo "API URL: $apiUrl\n\n";

if (empty($merchantId) || empty($merchantPassword)) {
    echo "❌ ERREUR: Les identifiants MaxiCash ne sont pas configurés dans .env\n";
    exit(1);
}

$payload = [
    'PayType' => 'MaxiCash',
    'MerchantID' => $merchantId,
    'MerchantPassword' => $merchantPassword,
    'Amount' => '100', // 1 USD en cents
    'Currency' => 'maxiDollar',
    'Language' => 'fr',
    'Reference' => 'TEST-' . time(),
    'SuccessURL' => 'http://example.com/success',
    'FailureURL' => 'http://example.com/failure',
    'CancelURL' => 'http://example.com/cancel',
    'Email' => 'test@example.com',
];

echo "Payload envoyé:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init("$apiUrl/Integration/PayEntryWeb");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => false,
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
$decoded = json_decode($response, true);
if ($decoded) {
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($decoded['ResponseStatus']) && $decoded['ResponseStatus'] === 'success') {
        echo "✅ Succès! LogID: " . ($decoded['LogID'] ?? 'N/A') . "\n";
        if (isset($decoded['LogID'])) {
            $redirectBase = $_ENV['MAXICASH_REDIRECT_BASE'] ?? 'https://api-testbed.maxicashapp.com';
            echo "URL de redirection: $redirectBase/payentryweb?logid=" . $decoded['LogID'] . "\n";
        }
    } else {
        echo "❌ Échec: " . ($decoded['ResponseError'] ?? 'Erreur inconnue') . "\n";
    }
} else {
    echo $response . "\n";
}
