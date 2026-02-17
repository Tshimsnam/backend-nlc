<?php

/**
 * Script de test pour vérifier les routes admin
 * 
 * Usage: php test-admin-routes.php
 */

echo "🧪 Test des Routes Admin\n";
echo "========================\n\n";

// Vérifier que nous sommes dans le bon répertoire
if (!file_exists('artisan')) {
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel\n";
    exit(1);
}

echo "✅ Répertoire Laravel détecté\n\n";

// Lister les routes admin
echo "📋 Routes Admin disponibles:\n";
echo "----------------------------\n";

$output = shell_exec('php artisan route:list --path=admin --columns=method,uri,name 2>&1');

if ($output === null) {
    echo "❌ Erreur lors de l'exécution de la commande artisan\n";
    exit(1);
}

echo $output;

// Vérifier que les routes attendues existent
$expectedRoutes = [
    'admin/dashboard',
    'admin/tickets/pending',
    'admin/tickets/{reference}/validate',
    'admin/users',
    'admin/events/stats'
];

echo "\n✅ Vérification des routes attendues:\n";
echo "-------------------------------------\n";

foreach ($expectedRoutes as $route) {
    if (strpos($output, $route) !== false) {
        echo "✅ $route - Trouvée\n";
    } else {
        echo "❌ $route - Manquante\n";
    }
}

// Vérifier le middleware
echo "\n🔒 Vérification du middleware:\n";
echo "------------------------------\n";

$middlewareOutput = shell_exec('php artisan route:list --path=admin --columns=uri,middleware 2>&1');

if (strpos($middlewareOutput, 'auth:sanctum') !== false) {
    echo "✅ Middleware auth:sanctum - Présent\n";
} else {
    echo "❌ Middleware auth:sanctum - Manquant\n";
}

if (strpos($middlewareOutput, 'admin.only') !== false) {
    echo "✅ Middleware admin.only - Présent\n";
} else {
    echo "❌ Middleware admin.only - Manquant\n";
}

// Vérifier que le contrôleur existe
echo "\n📁 Vérification du contrôleur:\n";
echo "------------------------------\n";

$controllerPath = 'app/Http/Controllers/Admin/DashboardController.php';
if (file_exists($controllerPath)) {
    echo "✅ DashboardController - Existe\n";
    
    // Vérifier les méthodes
    $controllerContent = file_get_contents($controllerPath);
    $methods = ['index', 'pendingTickets', 'validateTicket', 'users', 'eventsStats'];
    
    foreach ($methods as $method) {
        if (strpos($controllerContent, "function $method") !== false) {
            echo "  ✅ Méthode $method() - Présente\n";
        } else {
            echo "  ❌ Méthode $method() - Manquante\n";
        }
    }
} else {
    echo "❌ DashboardController - Manquant\n";
}

// Vérifier le middleware AdminOnly
echo "\n🛡️  Vérification du middleware AdminOnly:\n";
echo "----------------------------------------\n";

$middlewarePath = 'app/Http/Middleware/AdminOnly.php';
if (file_exists($middlewarePath)) {
    echo "✅ AdminOnly middleware - Existe\n";
} else {
    echo "❌ AdminOnly middleware - Manquant\n";
}

// Vérifier l'enregistrement du middleware
echo "\n⚙️  Vérification de l'enregistrement du middleware:\n";
echo "--------------------------------------------------\n";

$bootstrapPath = 'bootstrap/app.php';
if (file_exists($bootstrapPath)) {
    $bootstrapContent = file_get_contents($bootstrapPath);
    if (strpos($bootstrapContent, 'admin.only') !== false) {
        echo "✅ Middleware admin.only - Enregistré dans bootstrap/app.php\n";
    } else {
        echo "❌ Middleware admin.only - Non enregistré dans bootstrap/app.php\n";
    }
} else {
    echo "❌ Fichier bootstrap/app.php - Manquant\n";
}

echo "\n";
echo "========================\n";
echo "✅ Test terminé!\n";
echo "========================\n\n";

echo "💡 Pour tester les routes avec curl:\n";
echo "------------------------------------\n";
echo "1. Obtenir un token admin:\n";
echo "   curl -X POST http://localhost:8000/api/login \\\n";
echo "     -H \"X-API-SECRET: votre_secret\" \\\n";
echo "     -H \"Content-Type: application/json\" \\\n";
echo "     -d '{\"email\":\"admin@nlc.com\",\"password\":\"Admin@123\"}'\n\n";

echo "2. Tester le dashboard:\n";
echo "   curl -X GET http://localhost:8000/admin/dashboard \\\n";
echo "     -H \"Authorization: Bearer {votre_token}\"\n\n";

echo "3. Nettoyer le cache si nécessaire:\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";
