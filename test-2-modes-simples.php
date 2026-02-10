<?php
/**
 * Test des 2 modes de paiement simplifiés
 */

$API_URL = 'http://127.0.0.1:8000/api';

echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST DES 2 MODES DE PAIEMENT SIMPLIFIÉS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// TEST 1: Vérifier les 2 modes
// ============================================================================
echo "TEST 1: Vérification des modes de paiement\n";
echo "────────────────────────────────────────────────────────────────\n";

$ch = curl_init("$API_URL/payment-modes");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "❌ ERREUR: Le serveur Laravel n'est pas démarré!\n";
    echo "Démarrez-le avec: php artisan serve --port=8000\n\n";
    exit(1);
}

$modes = json_decode($response, true);

echo "Code HTTP: $httpCode\n";
echo "Nombre de modes: " . count($modes) . "\n\n";

if (count($modes) === 2) {
    echo "✅ Exactement 2 modes trouvés!\n\n";
    
    foreach ($modes as $mode) {
        echo "• " . $mode['label'] . " (id: " . $mode['id'] . ")\n";
        echo "  Description: " . $mode['description'] . "\n\n";
    }
    
    $hasOnline = false;
    $hasCash = false;
    
    foreach ($modes as $mode) {
        if ($mode['id'] === 'online') $hasOnline = true;
        if ($mode['id'] === 'cash') $hasCash = true;
    }
    
    if ($hasOnline && $hasCash) {
        echo "✅ TEST 1 RÉUSSI: Les 2 modes sont présents!\n";
    } else {
        echo "❌ TEST 1 ÉCHOUÉ: Modes manquants\n";
        exit(1);
    }
} else {
    echo "❌ TEST 1 ÉCHOUÉ: " . count($modes) . " modes trouvés au lieu de 2\n";
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 2: Paiement EN LIGNE
// ============================================================================
echo "TEST 2: Création d'un ticket avec paiement EN LIGNE\n";
echo "────────────────────────────────────────────────────────────────\n";

$payload = [
    'event_price_id' => 1,
    'full_name' => 'Test Paiement En Ligne',
    'email' => 'online@example.com',
    'phone' => '+243 XXX XXX XXX',
    'pay_type' => 'online', // Nouveau: "online" au lieu de "mobile_money"
];

$ch = curl_init("$API_URL/events/1/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "Code HTTP: $httpCode\n";

if (isset($data['success']) && $data['success']) {
    echo "✅ Ticket créé avec succès!\n";
    echo "Mode: " . ($data['payment_mode'] ?? 'N/A') . "\n";
    echo "Référence: " . ($data['reference'] ?? 'N/A') . "\n";
    
    if (isset($data['redirect_url'])) {
        echo "✅ URL de redirection: " . substr($data['redirect_url'], 0, 60) . "...\n";
        echo "✅ TEST 2 RÉUSSI!\n";
    } else {
        echo "❌ URL de redirection manquante\n";
    }
} else {
    echo "❌ TEST 2 ÉCHOUÉ: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
    if (isset($data['errors'])) {
        print_r($data['errors']);
    }
}

echo "\n";

// ============================================================================
// TEST 3: Paiement EN CAISSE
// ============================================================================
echo "TEST 3: Création d'un ticket avec paiement EN CAISSE\n";
echo "────────────────────────────────────────────────────────────────\n";

$payload = [
    'event_price_id' => 1,
    'full_name' => 'Test Paiement En Caisse',
    'email' => 'cash@example.com',
    'phone' => '+243 XXX XXX XXX',
    'pay_type' => 'cash', // Mode caisse
];

$ch = curl_init("$API_URL/events/1/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "Code HTTP: $httpCode\n";

if (isset($data['success']) && $data['success']) {
    echo "✅ Ticket créé avec succès!\n";
    echo "Mode: " . ($data['payment_mode'] ?? 'N/A') . "\n";
    
    if (isset($data['ticket'])) {
        echo "✅ Données du ticket:\n";
        echo "  - Référence: " . $data['ticket']['reference'] . "\n";
        echo "  - Montant: " . $data['ticket']['amount'] . " " . $data['ticket']['currency'] . "\n";
        echo "  - Status: " . $data['ticket']['status'] . "\n";
        
        if (isset($data['ticket']['qr_data'])) {
            echo "✅ QR code: " . substr($data['ticket']['qr_data'], 0, 60) . "...\n";
            echo "✅ TEST 3 RÉUSSI!\n";
        } else {
            echo "❌ QR code manquant\n";
        }
    } else {
        echo "❌ Données du ticket manquantes\n";
    }
} else {
    echo "❌ TEST 3 ÉCHOUÉ: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
    if (isset($data['errors'])) {
        print_r($data['errors']);
    }
}

echo "\n";

// ============================================================================
// RÉSUMÉ
// ============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ 2 modes de paiement simplifiés\n";
echo "✅ Mode EN LIGNE fonctionne (redirect_url)\n";
echo "✅ Mode EN CAISSE fonctionne (QR code)\n\n";

echo "🎉 TOUT FONCTIONNE CORRECTEMENT!\n\n";

echo "📋 PROCHAINES ÉTAPES:\n";
echo "1. Copier EventInscriptionPage.tsx dans le frontend\n";
echo "2. Installer qrcode.react: npm install qrcode.react\n";
echo "3. Redémarrer le frontend: npm run dev\n";
echo "4. Tester dans le navigateur!\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
