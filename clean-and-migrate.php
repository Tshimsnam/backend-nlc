<?php
/**
 * Script de nettoyage et migration pour production
 * Nettoie les données problématiques avant de lancer les migrations
 */

echo "═══════════════════════════════════════════════════════════════\n";
echo "  NETTOYAGE ET MIGRATION PRODUCTION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Ce script va:\n";
echo "1. Nettoyer les données problématiques\n";
echo "2. Lancer les migrations\n\n";

echo "⚠️  Assurez-vous d'avoir sauvegardé la base de données!\n";
echo "Appuyez sur Entrée pour continuer ou Ctrl+C pour annuler...\n";
fgets(STDIN);

echo "\n🧹 Nettoyage des données...\n";

// Nettoyer les tickets avec ticket_number vide
echo "- Suppression des tickets avec ticket_number vide...\n";
passthru('php artisan tinker --execute="DB::table(\'tickets\')->whereNull(\'ticket_number\')->delete(); DB::table(\'tickets\')->where(\'ticket_number\', \'\')->delete();"');

echo "\n🔄 Lancement des migrations...\n";
passthru('php artisan migrate');

echo "\n📊 Vérification...\n";
passthru('php artisan migrate:status');

echo "\n✅ Terminé!\n\n";

echo "📋 Prochaines étapes:\n";
echo "1. Vérifier que toutes les migrations sont 'Ran'\n";
echo "2. Tester l'API: php test-2-modes-simples.php\n";
echo "3. Tester le frontend\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
