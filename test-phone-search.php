<?php

/**
 * Test de recherche par téléphone avec différents formats
 * Usage: php test-phone-search.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = 'http://192.168.40.9:8000';

echo "📱 Test de recherche par téléphone\n";
echo "================================\n\n";

// Formats de téléphone à tester
$phoneFormats = [
    '+243 812 345 678',
    '+243812345678',
    '0812345678',
    '0812 345 678',
    '243812345678',
];

foreach ($phoneFormats as $phone) {
    echo "🔍 Test avec: {$phone}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/tickets/search?phone=' . urlencode($phone));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode === 200 && isset($data['success']) && $data['success']) {
        echo "   ✅ Trouvé: {$data['count']} ticket(s)\n";
        if (isset($data['tickets'][0])) {
            echo "      - Référence: {$data['tickets'][0]['reference']}\n";
            echo "      - Nom: {$data['tickets'][0]['full_name']}\n";
            echo "      - Téléphone enregistré: {$data['tickets'][0]['phone']}\n";
        }
    } else {
        echo "   ❌ Non trouvé\n";
        if (isset($data['searched_variants'])) {
            echo "      Variantes recherchées: " . implode(', ', $data['searched_variants']) . "\n";
        }
    }
    
    echo "\n";
}

echo "✅ Tests terminés !\n";
