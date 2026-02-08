<?php

/**
 * Test de configuration Cloudflare Tunnel
 * Vérifie que tout est correctement configuré
 */

echo "=== Test Configuration Cloudflare Tunnel ===\n\n";

// 1. Vérifier les variables d'environnement
echo "1. Vérification des variables d'environnement...\n";

$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "   ❌ Fichier .env introuvable\n";
    exit(1);
}

$envContent = file_get_contents($envFile);

// Vérifier que les URLs Cloudflare sont configurées
if (strpos($envContent, 'trycloudflare.com') !== false) {
    echo "   ✅ URLs Cloudflare Tunnel configurées\n";
    
    // Extraire l'URL Cloudflare
    preg_match('/FRONTEND_NLC=(https:\/\/[^\/\s]+\.trycloudflare\.com)/', $envContent, $matches);
    $cloudflareUrl = $matches[1] ?? null;
    
    if ($cloudflareUrl) {
        echo "   ✅ URL Frontend: $cloudflareUrl\n";
    }
} else {
    echo "   ❌ URLs Cloudflare Tunnel non configurées\n";
    echo "      Mettre à jour .env avec l'URL Cloudflare\n";
    exit(1);
}

// Vérifier les URLs MaxiCash
$checks = [
    'MAXICASH_SUCCESS_URL' => 'trycloudflare.com/paiement/success',
    'MAXICASH_FAILURE_URL' => 'trycloudflare.com/paiement/failure',
    'MAXICASH_CANCEL_URL' => 'trycloudflare.com/paiement/cancel',
];

$allGood = true;
foreach ($checks as $key => $expected) {
    if (strpos($envContent, $key) !== false && strpos($envContent, $expected) !== false) {
        echo "   ✅ $key configuré\n";
    } else {
        echo "   ❌ $key non configuré ou incorrect\n";
        $allGood = false;
    }
}

echo "\n";

// 2. Tester le backend local
echo "2. Test du backend local...\n";

$ch = curl_init('http://192.168.241.9:8000/api/test');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "   ✅ Backend local accessible\n";
} else {
    echo "   ❌ Backend local non accessible (Code: $httpCode)\n";
    echo "      Démarrer avec: php artisan serve --host=192.168.241.9 --port=8000\n";
    $allGood = false;
}

echo "\n";

// 3. Tester Cloudflare Tunnel frontend
if (isset($cloudflareUrl)) {
    echo "3. Test du frontend Cloudflare Tunnel...\n";
    
    $ch = curl_init($cloudflareUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0'],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && strlen($response) > 100) {
        echo "   ✅ Frontend Cloudflare accessible\n";
        echo "   ✅ PAS de mot de passe requis!\n";
    } elseif ($httpCode === 0) {
        echo "   ❌ Frontend Cloudflare non accessible\n";
        if ($error) {
            echo "      Erreur: $error\n";
        }
        echo "      Démarrer avec: cloudflared tunnel --url http://localhost:8080\n";
        $allGood = false;
    } else {
        echo "   ⚠️  Frontend Cloudflare répond mais contenu inattendu\n";
        echo "      Code HTTP: $httpCode\n";
        echo "      Vérifier que npm run dev tourne\n";
    }
} else {
    echo "3. ⚠️  URL Cloudflare non trouvée dans .env\n";
    $allGood = false;
}

echo "\n";

// Résumé
echo "=== Résumé ===\n";
if ($allGood) {
    echo "✅ Configuration complète et fonctionnelle!\n";
    echo "\n";
    echo "🎉 Vous pouvez maintenant tester un paiement:\n";
    echo "   php test-ticket-payment.php\n";
    echo "\n";
    echo "📍 URL Frontend: $cloudflareUrl\n";
    echo "   Ouvrir dans le navigateur - PAS de mot de passe requis!\n";
} else {
    echo "⚠️  Configuration incomplète\n";
    echo "\n";
    echo "📋 Actions à faire:\n";
    echo "   1. Démarrer Laravel: php artisan serve --host=192.168.241.9 --port=8000\n";
    echo "   2. Démarrer Frontend: npm run dev (dans le dossier frontend)\n";
    echo "   3. Démarrer Cloudflare: cloudflared tunnel --url http://localhost:8080\n";
    echo "   4. Copier l'URL Cloudflare affichée\n";
    echo "   5. Mettre à jour .env avec cette URL\n";
    echo "   6. Redémarrer Laravel\n";
    echo "   7. Relancer ce test: php test-cloudflare-setup.php\n";
}

echo "\n";
echo "📚 Documentation: BACKEND_CLOUDFLARE_SETUP.md\n";
echo "\n";
echo "💡 Avantage Cloudflare: PAS de mot de passe!\n";
echo "   MaxiCash peut accéder directement à vos URLs.\n";
