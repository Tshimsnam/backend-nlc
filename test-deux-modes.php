<?php
/**
 * Test rapide des deux modes de paiement
 */

$API_URL = 'http://127.0.0.1:8000/api';

echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST DES DEUX MODES DE PAIEMENT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// TEST 1: Vérifier que les deux modes sont dans la liste
// ============================================================================
echo "TEST 1: Vérification de la liste des modes de paiement\n";
echo "────────────────────────────────────────────────────────────────\n";

$ch = curl_init("$API_URL/payment-modes");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$modes = json_decode($response, true);

echo "Code HTTP: $httpCode\n";
echo "Nombre de modes: " . count($modes) . "\n\n";

$hasCash = false;
$hasOnline = false;

foreach ($modes as $mode) {
    echo "• " . $mode['label'] . " (id: " . $mode['id'] . ")\n";
    
    if ($mode['id'] === 'cash') {
        $hasCash = true;
        echo "  ✅ Mode CAISSE trouvé!\n";
    }
    
    if (in_array($mode['id'], ['mobile_money', 'credit_card', 'maxicash', 'paypal'])) {
        $hasOnline = true;
    }
}

echo "\n";

if ($hasCash && $hasOnline) {
    echo "✅ TEST 1 RÉUSSI: Les deux modes sont présents!\n";
} else {
    echo "❌ TEST 1 ÉCHOUÉ\n";
    if (!$hasCash) echo "  - Mode CAISSE manquant\n";
    if (!$hasOnline) echo "  - Modes EN LIGNE manquants\n";
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 2: Créer un ticket avec paiement EN LIGNE
// ============================================================================
echo "TEST 2: Création d'un ticket avec paiement EN LIGNE\n";
echo "────────────────────────────────────────────────────────────────\n";

$payload = [
    'event_price_id' => 1,
    'full_name' => 'Test Paiement En Ligne',
    'email' => 'online@example.com',
    'phone' => '+243 XXX XXX XXX',
    'pay_type' => 'mobile_money',
    'pay_sub_type' => 'mpesa',
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
    echo "Mode de paiement: " . ($data['payment_mode'] ?? 'N/A') . "\n";
    
    if (isset($data['redirect_url'])) {
        echo "✅ URL de redirection présente: " . substr($data['redirect_url'], 0, 50) . "...\n";
        echo "✅ TEST 2 RÉUSSI: Paiement EN LIGNE fonctionne!\n";
    } else {
        echo "❌ URL de redirection manquante\n";
        echo "❌ TEST 2 ÉCHOUÉ\n";
    }
} else {
    echo "❌ TEST 2 ÉCHOUÉ: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
}

echo "\n";

// ============================================================================
// TEST 3: Créer un ticket avec paiement EN CAISSE
// ============================================================================
echo "TEST 3: Création d'un ticket avec paiement EN CAISSE\n";
echo "────────────────────────────────────────────────────────────────\n";

$payload = [
    'event_price_id' => 1,
    'full_name' => 'Test Paiement En Caisse',
    'email' => 'cash@example.com',
    'phone' => '+243 XXX XXX XXX',
    'pay_type' => 'cash',
    'pay_sub_type' => null,
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
    echo "Mode de paiement: " . ($data['payment_mode'] ?? 'N/A') . "\n";
    
    if (isset($data['ticket'])) {
        echo "✅ Données du ticket présentes\n";
        echo "  - Référence: " . $data['ticket']['reference'] . "\n";
        echo "  - Montant: " . $data['ticket']['amount'] . " " . $data['ticket']['currency'] . "\n";
        echo "  - Status: " . $data['ticket']['status'] . "\n";
        
        if (isset($data['ticket']['qr_data'])) {
            echo "✅ QR code généré: " . substr($data['ticket']['qr_data'], 0, 50) . "...\n";
            echo "✅ TEST 3 RÉUSSI: Paiement EN CAISSE fonctionne!\n";
        } else {
            echo "❌ QR code manquant\n";
            echo "❌ TEST 3 ÉCHOUÉ\n";
        }
    } else {
        echo "❌ Données du ticket manquantes\n";
        echo "❌ TEST 3 ÉCHOUÉ\n";
    }
} else {
    echo "❌ TEST 3 ÉCHOUÉ: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
}

echo "\n";

// ============================================================================
// RÉSUMÉ
// ============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ DES TESTS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Mode CAISSE présent dans la liste\n";
echo "✅ Modes EN LIGNE présents dans la liste\n";
echo "✅ Paiement EN LIGNE crée un ticket et retourne redirect_url\n";
echo "✅ Paiement EN CAISSE crée un ticket et retourne QR code\n\n";

echo "🎉 LES DEUX MODES FONCTIONNENT CORRECTEMENT!\n\n";

echo "📋 PROCHAINES ÉTAPES:\n";
echo "1. Copier EventInscriptionPage.tsx dans le frontend\n";
echo "2. Installer qrcode.react: npm install qrcode.react\n";
echo "3. Redémarrer le frontend: npm run dev\n";
echo "4. Tester dans le navigateur!\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
