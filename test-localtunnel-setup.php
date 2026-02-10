<?php

/**
 * Test de configuration LocalTunnel
 * Vérifie que tout est correctement configuré
 */

echo "=== Test Configuration LocalTunnel ===\n\n";

// 1. Vérifier les variables d'environnement
echo "1. Vérification des variables d'environnement...\n";

$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "   ❌ Fichier .env introuvable\n";
    exit(1);
}

$envContent = file_get_contents($envFile);

$checks = [
    'FRONTEND_NLC' => 'https://nlc-maxicash-rdc.loca.lt',
    'MAXICASH_SUCCESS_URL' => 'https://nlc-maxicash-rdc.loca.lt/paiement/success',
    'MAXICASH_FAILURE_URL' => 'https://nlc-maxicash-rdc.loca.lt/paiement/failure',
    'MAXICASH_CANCEL_URL' => 'https://nlc-maxicash-rdc.loca.lt/paiement/cancel',
];

$allGood = true;
foreach ($checks as $key => $expected) {
    if (strpos($envContent, "$key=$expected") !== false) {
        echo "   ✅ $key configuré\n";
    } else {
        echo "   ❌ $key non configuré ou incorrect\n";
        echo "      Attendu: $key=$expected\n";
        $allGood = false;
    }
}

if (strpos($envContent, 'MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt') !== false) {
    echo "   ✅ MAXICASH_NOTIFY_URL configuré avec LocalTunnel\n";
} elseif (strpos($envContent, 'MAXICASH_NOTIFY_URL=http://192.168.241.9:8000') !== false) {
    echo "   ⚠️  MAXICASH_NOTIFY_URL utilise encore l'URL locale\n";
    echo "      Mettre à jour avec: https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash\n";
} else {
    echo "   ❌ MAXICASH_NOTIFY_URL non configuré\n";
    $allGood = false;
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

// 3. Tester LocalTunnel backend (si configuré)
echo "3. Test du backend LocalTunnel...\n";

$ch = curl_init('https://nlc-maxicash-api-rdc.loca.lt/api/test');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'API fonctionne') !== false) {
    echo "   ✅ Backend LocalTunnel accessible\n";
} elseif ($httpCode === 200 && strpos($response, 'Click to Continue') !== false) {
    echo "   ⚠️  LocalTunnel nécessite une autorisation\n";
    echo "      Ouvrir https://nlc-maxicash-api-rdc.loca.lt dans un navigateur\n";
    echo "      Cliquer sur 'Click to Continue'\n";
} else {
    echo "   ❌ Backend LocalTunnel non accessible\n";
    if ($error) {
        echo "      Erreur: $error\n";
    }
    echo "      Démarrer avec: lt --port 8000 --subdomain nlc-maxicash-api-rdc\n";
    $allGood = false;
}

echo "\n";

// 4. Tester LocalTunnel frontend
echo "4. Test du frontend LocalTunnel...\n";

$ch = curl_init('https://nlc-maxicash-rdc.loca.lt');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Click to Continue') === false) {
    echo "   ✅ Frontend LocalTunnel accessible\n";
} elseif ($httpCode === 200 && strpos($response, 'Click to Continue') !== false) {
    echo "   ⚠️  LocalTunnel nécessite une autorisation\n";
    echo "      Ouvrir https://nlc-maxicash-rdc.loca.lt dans un navigateur\n";
    echo "      Cliquer sur 'Click to Continue'\n";
} else {
    echo "   ❌ Frontend LocalTunnel non accessible\n";
    echo "      Démarrer avec: lt --port 8080 --subdomain nlc-maxicash-rdc\n";
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
} else {
    echo "⚠️  Configuration incomplète\n";
    echo "\n";
    echo "📋 Actions à faire:\n";
    echo "   1. Vérifier/mettre à jour .env\n";
    echo "   2. Démarrer Laravel: php artisan serve --host=192.168.241.9 --port=8000\n";
    echo "   3. Démarrer LocalTunnel backend: lt --port 8000 --subdomain nlc-maxicash-api-rdc\n";
    echo "   4. Démarrer LocalTunnel frontend: lt --port 8080 --subdomain nlc-maxicash-rdc\n";
    echo "   5. Autoriser les URLs LocalTunnel dans le navigateur\n";
    echo "   6. Relancer ce test: php test-localtunnel-setup.php\n";
}

echo "\n";
echo "📚 Documentation: BACKEND_LOCALTUNNEL_SETUP.md\n";
echo "🚀 Script auto: start-all-localtunnel.bat\n";
