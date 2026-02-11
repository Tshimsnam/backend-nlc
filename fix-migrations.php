<?php
/**
 * Script pour corriger les problèmes de migration
 */

echo "═══════════════════════════════════════════════════════════════\n";
echo "  CORRECTION DES MIGRATIONS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Ce script va:\n";
echo "1. Supprimer toutes les tables\n";
echo "2. Relancer toutes les migrations\n\n";

echo "⚠️  ATTENTION: Toutes les données seront perdues!\n";
echo "Appuyez sur Entrée pour continuer ou Ctrl+C pour annuler...\n";
fgets(STDIN);

echo "\n🔄 Suppression de toutes les tables...\n";
passthru('php artisan db:wipe');

echo "\n🔄 Relancement des migrations...\n";
passthru('php artisan migrate');

echo "\n✅ Terminé!\n";
echo "\n📋 Prochaines étapes:\n";
echo "1. Créer des événements de test\n";
echo "2. Créer des prix pour les événements\n";
echo "3. Tester l'inscription\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
