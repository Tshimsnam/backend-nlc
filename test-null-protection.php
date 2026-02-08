<?php

/**
 * Test de protection contre les valeurs NULL
 * Vérifie que toutes les validations fonctionnent correctement
 */

require __DIR__ . '/vendor/autoload.php';

$apiUrl = 'http://192.168.241.9:8000';

echo "=== Test de protection contre les valeurs NULL ===\n\n";

$tests = [
    [
        'name' => 'Test 1: Nom manquant',
        'payload' => [
            'event_price_id' => 1,
            'email' => 'test@example.com',
            'phone' => '+243999999999',
            'pay_type' => 'credit_card',
        ],
        'expected_error' => 'nom complet',
    ],
    [
        'name' => 'Test 2: Email invalide',
        'payload' => [
            'event_price_id' => 1,
            'full_name' => 'Test User',
            'email' => 'invalid-email',
            'phone' => '+243999999999',
            'pay_type' => 'credit_card',
        ],
        'expected_error' => 'email',
    ],
    [
        'name' => 'Test 3: Téléphone trop court',
        'payload' => [
            'event_price_id' => 1,
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123',
            'pay_type' => 'credit_card',
        ],
        'expected_error' => 'phone',
    ],
    [
        'name' => 'Test 4: Type de paiement invalide',
        'payload' => [
            'event_price_id' => 1,
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+243999999999',
            'pay_type' => 'bitcoin',
        ],
        'expected_error' => 'paiement',
    ],
    [
        'name' => 'Test 5: Nom trop court',
        'payload' => [
            'event_price_id' => 1,
            'full_name' => 'AB',
            'email' => 'test@example.com',
            'phone' => '+243999999999',
            'pay_type' => 'credit_card',
        ],
        'expected_error' => 'full_name',
    ],
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "🧪 {$test['name']}\n";
    
    $ch = curl_init("$apiUrl/api/events/1/register");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($test['payload']),
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 422) {
        // Erreur de validation attendue
        $errorMessage = json_encode($result);
        
        if (stripos($errorMessage, $test['expected_error']) !== false) {
            echo "   ✅ PASS - Validation bloquée correctement\n";
            echo "   Message: " . ($result['message'] ?? 'Erreur de validation') . "\n";
            $passed++;
        } else {
            echo "   ❌ FAIL - Erreur inattendue\n";
            echo "   Attendu: {$test['expected_error']}\n";
            echo "   Reçu: $errorMessage\n";
            $failed++;
        }
    } else {
        echo "   ❌ FAIL - Devrait retourner 422, reçu $httpCode\n";
        echo "   Réponse: $response\n";
        $failed++;
    }
    
    echo "\n";
}

// Test final: Requête valide
echo "🧪 Test 6: Requête valide (devrait réussir)\n";

$validPayload = [
    'event_price_id' => 1,
    'full_name' => 'Test User Valid',
    'email' => 'valid@example.com',
    'phone' => '+243999999999',
    'pay_type' => 'credit_card',
];

$ch = curl_init("$apiUrl/api/events/1/register");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($validPayload),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 201 && isset($result['success']) && $result['success']) {
    echo "   ✅ PASS - Ticket créé avec succès\n";
    echo "   Référence: {$result['reference']}\n";
    $passed++;
} else {
    echo "   ❌ FAIL - Devrait retourner 201, reçu $httpCode\n";
    echo "   Réponse: $response\n";
    $failed++;
}

echo "\n";
echo "=== Résultats ===\n";
echo "✅ Tests réussis: $passed\n";
echo "❌ Tests échoués: $failed\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 Toutes les protections fonctionnent correctement!\n";
    echo "Aucune valeur null ne peut atteindre MaxiCash.\n";
} else {
    echo "⚠️  Certaines validations ne fonctionnent pas comme prévu.\n";
    echo "Vérifiez les logs Laravel pour plus de détails.\n";
}
