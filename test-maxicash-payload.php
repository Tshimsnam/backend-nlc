<?php

/**
 * Test pour voir exactement ce qui est envoyé à MaxiCash
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$merchantId = $_ENV['MAXICASH_MERCHANT_ID'] ?? '';
$merchantPassword = $_ENV['MAXICASH_MERCHANT_PASSWORD'] ?? '';
$apiUrl = $_ENV['MAXICASH_API_URL'] ?? 'https://webapi-test.maxicashapp.com';

echo "=== Test Payload MaxiCash ===\n\n";

// Payload EXACT comme dans votre code
$payload = [
    'PayType' => 'MaxiCash',
    'MerchantID' => $merchantId,
    'MerchantPassword' => $merchantPassword,
    'Amount' => '5000',
    'Currency' => 'maxiDollar',
    'Language' => 'fr',
    'Reference' => 'TEST-' . time(),
    'SuccessURL' => 'http://192.168.241.9:8080/paiement/success?reference=TEST-' . time(),
    'FailureURL' => 'http://192.168.241.9:8080/paiement/failure?reference=TEST-' . time(),
    'CancelURL' => 'http://192.168.241.9:8080/paiement/cancel?reference=TEST-' . time(),
    'NotifyURL' => 'http://192.168.241.9:8000/api/webhooks/maxicash',
    'Email' => 'test@example.com',
];

echo "Payload envoyé:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Vérifier chaque valeur
echo "Vérification des valeurs:\n";
foreach ($payload as $key => $value) {
    $type = gettype($value);
    $length = is_string($value) ? strlen($value) : 'N/A';
    $isEmpty = empty($value) ? 'OUI' : 'NON';
    $isNull = is_null($value) ? 'OUI' : 'NON';
    
    echo sprintf(
        "  %-20s | Type: %-7s | Longueur: %-4s | Vide: %-3s | Null: %-3s | Valeur: %s\n",
        $key,
        $type,
        $length,
        $isEmpty,
        $isNull,
        is_string($value) && strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value
    );
}

echo "\n";

// Envoyer à MaxiCash
echo "Envoi à MaxiCash...\n";
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
    CURLOPT_VERBOSE => true,
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

echo "\nRéponse MaxiCash:\n";
$decoded = json_decode($response, true);
if ($decoded) {
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($decoded['ResponseStatus']) && $decoded['ResponseStatus'] === 'success') {
        echo "✅ Succès! LogID: " . ($decoded['LogID'] ?? 'N/A') . "\n";
        
        // Vérifier si la référence est retournée
        if (isset($decoded['Reference'])) {
            echo "✅ Référence retournée: " . $decoded['Reference'] . "\n";
        } else {
            echo "⚠️  Référence NON retournée par MaxiCash\n";
        }
        
        if (isset($decoded['LogID'])) {
            $redirectBase = $_ENV['MAXICASH_REDIRECT_BASE'] ?? 'https://api-testbed.maxicashapp.com';
            $redirectUrl = "$redirectBase/payentryweb?logid=" . $decoded['LogID'];
            echo "\nURL de test: $redirectUrl\n";
            echo "\n⚠️  IMPORTANT: Testez cette URL dans votre navigateur pour voir si l'erreur apparaît\n";
        }
    } else {
        echo "❌ Échec: " . ($decoded['ResponseError'] ?? 'Erreur inconnue') . "\n";
        echo "ResponseDesc: " . ($decoded['ResponseDesc'] ?? 'N/A') . "\n";
    }
} else {
    echo $response . "\n";
}

echo "\n=== Analyse ===\n";
echo "Si MaxiCash retourne 'success' mais que l'erreur apparaît sur la page de paiement,\n";
echo "cela signifie que MaxiCash a un problème INTERNE avec les URLs de callback.\n";
echo "\nPossibilités:\n";
echo "1. MaxiCash ne peut pas accéder aux URLs (192.168.x.x est local)\n";
echo "2. MaxiCash essaie de valider les URLs et échoue\n";
echo "3. MaxiCash a un bug interne avec certains paramètres\n";
