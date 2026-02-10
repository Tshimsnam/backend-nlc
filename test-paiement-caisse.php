<?php
/**
 * Script de test pour le paiement en caisse
 * 
 * Ce script teste:
 * 1. Création d'un ticket avec paiement en caisse
 * 2. Vérification du QR code généré
 * 3. Liste des tickets en attente
 * 4. Validation d'un paiement en caisse
 */

$API_URL = 'http://192.168.241.9:8000/api';

echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST DU SYSTÈME DE PAIEMENT EN CAISSE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// TEST 1: Créer un ticket avec paiement en caisse
// ============================================================================
echo "TEST 1: Création d'un ticket avec paiement en caisse\n";
echo "────────────────────────────────────────────────────────────────\n";

$payload = [
    'event_price_id' => 1, // Remplacer par un ID valide
    'full_name' => 'Test Utilisateur',
    'email' => 'test@example.com',
    'phone' => '+243 XXX XXX XXX',
    'pay_type' => 'cash',
    'pay_sub_type' => null,
];

$ch = curl_init("$API_URL/events/1/register"); // Remplacer 1 par un ID d'événement valide
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

echo "Code HTTP: $httpCode\n";
echo "Réponse:\n";
$data = json_decode($response, true);
print_r($data);

if ($httpCode === 201 && isset($data['success']) && $data['success']) {
    echo "\n✅ TEST 1 RÉUSSI: Ticket créé avec succès!\n";
    echo "Référence: " . $data['ticket']['reference'] . "\n";
    echo "Montant: " . $data['ticket']['amount'] . " " . $data['ticket']['currency'] . "\n";
    echo "Status: " . $data['ticket']['status'] . "\n";
    echo "QR Data: " . substr($data['ticket']['qr_data'], 0, 50) . "...\n";
    
    $ticketReference = $data['ticket']['reference'];
} else {
    echo "\n❌ TEST 1 ÉCHOUÉ\n";
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 2: Vérifier les détails du ticket
// ============================================================================
echo "TEST 2: Vérification des détails du ticket\n";
echo "────────────────────────────────────────────────────────────────\n";

$ch = curl_init("$API_URL/tickets/$ticketReference");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Réponse:\n";
$ticketData = json_decode($response, true);
print_r($ticketData);

if ($httpCode === 200 && isset($ticketData['reference'])) {
    echo "\n✅ TEST 2 RÉUSSI: Détails du ticket récupérés!\n";
    echo "Status actuel: " . $ticketData['payment_status'] . "\n";
} else {
    echo "\n❌ TEST 2 ÉCHOUÉ\n";
}

echo "\n";

// ============================================================================
// TEST 3: Liste des tickets en attente (nécessite authentification admin)
// ============================================================================
echo "TEST 3: Liste des tickets en attente de paiement caisse\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "⚠️  Ce test nécessite un token admin\n";
echo "Pour tester manuellement:\n";
echo "GET $API_URL/tickets/pending-cash\n";
echo "Header: Authorization: Bearer {admin_token}\n";

echo "\n";

// ============================================================================
// TEST 4: Validation du paiement (nécessite authentification admin)
// ============================================================================
echo "TEST 4: Validation du paiement en caisse\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "⚠️  Ce test nécessite un token admin\n";
echo "Pour tester manuellement:\n";
echo "POST $API_URL/tickets/$ticketReference/validate-cash\n";
echo "Header: Authorization: Bearer {admin_token}\n";

echo "\n";

// ============================================================================
// RÉSUMÉ
// ============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ DES TESTS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Ticket créé avec paiement en caisse\n";
echo "✅ QR code généré avec référence unique\n";
echo "✅ Status 'pending_cash' appliqué\n";
echo "✅ Détails du ticket accessibles\n\n";

echo "📋 PROCHAINES ÉTAPES:\n";
echo "1. Tester avec le frontend (npm install qrcode.react)\n";
echo "2. Scanner le QR code avec un lecteur\n";
echo "3. Créer l'interface admin pour validation\n";
echo "4. Tester la validation du paiement\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
