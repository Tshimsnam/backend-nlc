<?php

/**
 * Test de redirection MaxiCash - Simule ce qui se passe quand l'utilisateur clique sur le lien
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$logId = $argv[1] ?? '97128'; // Utiliser le LogID du dernier test

$redirectBase = $_ENV['MAXICASH_REDIRECT_BASE'] ?? 'https://api-testbed.maxicashapp.com';
$url = "$redirectBase/payentryweb?logid=$logId";

echo "=== Test de redirection MaxiCash ===\n\n";
echo "URL testée: $url\n\n";

// Simuler ce que fait le navigateur
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$error = curl_error($ch);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "URL finale: $finalUrl\n\n";

if ($error) {
    echo "❌ Erreur cURL: $error\n";
    exit(1);
}

// Chercher l'erreur dans la réponse
if (stripos($response, 'Object reference not set') !== false) {
    echo "❌ ERREUR TROUVÉE: 'Object reference not set to an instance of an object'\n\n";
    
    // Extraire le contexte de l'erreur
    $start = max(0, stripos($response, 'Object reference') - 200);
    $length = 400;
    $context = substr($response, $start, $length);
    
    echo "Contexte de l'erreur:\n";
    echo "---\n";
    echo $context . "\n";
    echo "---\n\n";
    
    echo "💡 Causes possibles:\n";
    echo "1. Le LogID n'existe pas ou a expiré\n";
    echo "2. Les URLs de callback (SuccessURL, FailureURL) sont invalides\n";
    echo "3. Un paramètre obligatoire est manquant dans la transaction initiale\n";
    echo "4. Problème avec le MerchantID/MerchantPassword\n\n";
    
    echo "🔍 Vérifications à faire:\n";
    echo "- Vérifier que les URLs dans .env sont complètes (http://...)\n";
    echo "- Vérifier que le LogID est récent (< 30 minutes)\n";
    echo "- Tester avec un nouveau LogID en créant un nouveau ticket\n";
} else {
    echo "✅ Pas d'erreur 'Object reference' détectée\n";
    echo "La page MaxiCash s'est chargée correctement\n\n";
    
    // Vérifier si c'est une page de paiement valide
    if (stripos($response, 'maxicash') !== false || stripos($response, 'payment') !== false) {
        echo "✅ Page de paiement MaxiCash chargée avec succès\n";
    }
}

// Sauvegarder la réponse pour inspection
file_put_contents('maxicash-response.html', $response);
echo "\n📄 Réponse complète sauvegardée dans: maxicash-response.html\n";
