<?php

/**
 * Test du système de réservation en 2 étapes
 * Usage: php test-reservation-2-etapes.php
 */

$baseUrl = 'http://192.168.58.9:8000';

echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                              ║\n";
echo "║              🧪 TEST - Système de réservation en 2 étapes                    ║\n";
echo "║                                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// ÉTAPE 1: Créer une réservation
// ============================================================================
echo "📋 ÉTAPE 1: Créer une réservation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$payload1 = [
    'event_price_id' => 2,
];

echo "Endpoint: POST {$baseUrl}/api/events/1/reserve\n";
echo "Payload:\n";
echo json_encode($payload1, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($baseUrl . '/api/events/1/reserve');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload1));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode1\n\n";

if ($httpCode1 === 201) {
    echo "✅ Réservation créée avec succès!\n\n";
    $data1 = json_decode($response1, true);
    
    echo "Réponse:\n";
    echo json_encode($data1, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    $reference = $data1['reservation']['reference'] ?? null;
    
    if (!$reference) {
        echo "❌ Erreur: Référence non trouvée dans la réponse\n";
        exit(1);
    }
    
    echo "📋 Référence générée: $reference\n";
    echo "💰 Montant: {$data1['reservation']['price']['amount']} {$data1['reservation']['price']['currency']}\n";
    echo "📊 Statut: {$data1['reservation']['status']}\n\n";
    
} else {
    echo "❌ Erreur lors de la création de la réservation\n\n";
    echo "Réponse:\n";
    echo $response1 . "\n\n";
    exit(1);
}

// ============================================================================
// ÉTAPE 1.5: Vérifier la réservation
// ============================================================================
echo "🔍 ÉTAPE 1.5: Vérifier la réservation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Endpoint: GET {$baseUrl}/api/reservations/{$reference}\n\n";

$ch = curl_init($baseUrl . '/api/reservations/' . $reference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode2\n\n";

if ($httpCode2 === 200) {
    echo "✅ Réservation trouvée!\n\n";
    $data2 = json_decode($response2, true);
    
    echo "Réponse:\n";
    echo json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    echo "📊 Statut: {$data2['reservation']['status']}\n";
    echo "✅ Complétée: " . ($data2['reservation']['is_completed'] ? 'Oui' : 'Non') . "\n\n";
    
} else {
    echo "❌ Erreur lors de la vérification\n\n";
    echo "Réponse:\n";
    echo $response2 . "\n\n";
}

// ============================================================================
// ÉTAPE 2: Compléter la réservation
// ============================================================================
echo "📝 ÉTAPE 2: Compléter la réservation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$payload3 = [
    'full_name' => 'Franck Kapuya',
    'email' => 'franckkapuya13@gmail.com',
    'phone' => '+243822902681',
    'pay_type' => 'online',
    'days' => 1,
];

echo "Endpoint: POST {$baseUrl}/api/reservations/{$reference}/complete\n";
echo "Payload:\n";
echo json_encode($payload3, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($baseUrl . '/api/reservations/' . $reference . '/complete');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload3));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode3\n\n";

if ($httpCode3 === 200) {
    echo "✅ Réservation complétée avec succès!\n\n";
    $data3 = json_decode($response3, true);
    
    echo "Réponse:\n";
    echo json_encode($data3, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    if (isset($data3['redirect_url'])) {
        echo "🔗 URL de redirection MaxiCash:\n";
        echo $data3['redirect_url'] . "\n\n";
        
        echo "📋 Référence: " . ($data3['reference'] ?? 'N/A') . "\n";
        echo "🆔 Log ID MaxiCash: " . ($data3['log_id'] ?? 'N/A') . "\n\n";
        
        echo "✅ Le frontend devrait maintenant rediriger l'utilisateur vers MaxiCash.\n";
    } elseif (isset($data3['ticket']['qr_data'])) {
        echo "📱 QR Code généré pour paiement en caisse\n";
        echo "📋 Référence: " . ($data3['ticket']['reference'] ?? 'N/A') . "\n";
        echo "💰 Montant: {$data3['ticket']['amount']} {$data3['ticket']['currency']}\n";
    }
    
} else {
    echo "❌ Erreur lors de la complétion de la réservation\n\n";
    echo "Réponse:\n";
    echo $response3 . "\n\n";
}

// ============================================================================
// RÉSUMÉ
// ============================================================================
echo "\n╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                              ║\n";
echo "║                              🎉 TEST TERMINÉ                                 ║\n";
echo "║                                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 RÉSUMÉ:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Étape 1: Réservation créée (HTTP $httpCode1)\n";
echo "✅ Étape 1.5: Réservation vérifiée (HTTP $httpCode2)\n";
echo "✅ Étape 2: Réservation complétée (HTTP $httpCode3)\n";
echo "\n📋 Référence finale: $reference\n";
echo "\n";
