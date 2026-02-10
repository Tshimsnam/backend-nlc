<?php

/**
 * Test avec des URLs PUBLIQUES pour voir si MaxiCash retourne la référence
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$merchantId = $_ENV['MAXICASH_MERCHANT_ID'] ?? '';
$merchantPassword = $_ENV['MAXICASH_MERCHANT_PASSWORD'] ?? '';
$apiUrl = $_ENV['MAXICASH_API_URL'] ?? 'https://webapi-test.maxicashapp.com';

echo "=== Test avec URLs PUBLIQUES ===\n\n";

$reference = 'TEST-' . time();

// Utiliser httpbin.org (service public de test)
$payload = [
    'PayType' => 'MaxiCash',
    'MerchantID' => $merchantId,
    'MerchantPassword' => $merchantPassword,
    'Amount' => '5000',
    'Currency' => 'maxiDollar',
    'Language' => 'fr',
    'Reference' => $reference,
    'SuccessURL' => "https://httpbin.org/get?status=success&ref=$reference",
    'FailureURL' => "https://httpbin.org/get?status=failure&ref=$reference",
    'CancelURL' => "https://httpbin.org/get?status=cancel&ref=$reference",
    'NotifyURL' => 'https://httpbin.org/post',
    'Email' => 'test@example.com',
];

echo "Référence envoyée: $reference\n";
echo "URLs utilisées: httpbin.org (service public)\n\n";

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
curl_close($ch);

echo "Code HTTP: $httpCode\n\n";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "Réponse MaxiCash:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($decoded['ResponseStatus']) && $decoded['ResponseStatus'] === 'success') {
        echo "✅ Succès! LogID: " . ($decoded['LogID'] ?? 'N/A') . "\n\n";
        
        // Vérifier si la référence est retournée
        if (isset($decoded['Reference']) && !empty($decoded['Reference'])) {
            echo "✅ Référence retournée: " . $decoded['Reference'] . "\n";
            echo "✅ MaxiCash a bien conservé la référence!\n";
        } else {
            echo "❌ Référence NON retournée (null ou vide)\n";
            echo "❌ MaxiCash perd la référence même avec des URLs publiques\n";
        }
        
        if (isset($decoded['LogID'])) {
            $redirectBase = $_ENV['MAXICASH_REDIRECT_BASE'] ?? 'https://api-testbed.maxicashapp.com';
            $redirectUrl = "$redirectBase/payentryweb?logid=" . $decoded['LogID'];
            echo "\n📍 URL de test: $redirectUrl\n";
            echo "\n🧪 Testez cette URL dans votre navigateur:\n";
            echo "   - Si l'erreur apparaît: MaxiCash a un bug interne\n";
            echo "   - Si ça fonctionne: Le problème vient des URLs locales\n";
        }
    } else {
        echo "❌ Échec: " . ($decoded['ResponseError'] ?? 'Erreur inconnue') . "\n";
    }
}
