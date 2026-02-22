<?php

/**
 * Test de recherche de tickets par téléphone
 * Usage: php test-ticket-search.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
$phone = '0827029543'; // Numéro à tester

echo "🔍 Test de recherche de tickets par téléphone\n";
echo "URL: {$baseUrl}/api/tickets/search?phone={$phone}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/api/tickets/search?phone={$phone}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: {$httpCode}\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
