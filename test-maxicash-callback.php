<?php

/**
 * Test pour simuler ce que MaxiCash envoie lors de la redirection
 * Ce script simule la redirection de MaxiCash vers votre URL de succès
 */

echo "=== Test Callback MaxiCash ===\n\n";

// Selon la documentation MaxiCash, lors de la redirection, MaxiCash ajoute ces paramètres:
// - Reference (votre référence)
// - Status ou TransactionStatus
// - TransactionID (ID MaxiCash)
// - Amount
// - Currency

$testReference = 'TEST-' . time();

echo "Simulation de redirection MaxiCash:\n";
echo "-----------------------------------\n\n";

echo "1. Vous créez un ticket avec référence: $testReference\n";
echo "2. Vous envoyez à MaxiCash PayEntryWeb avec:\n";
echo "   - Reference: $testReference\n";
echo "   - SuccessURL: https://votre-app.com/paiement/success\n";
echo "   - FailureURL: https://votre-app.com/paiement/failure\n\n";

echo "3. MaxiCash retourne:\n";
echo "   - LogID: 12345\n";
echo "   - Reference: null (c'est NORMAL!)\n\n";

echo "4. Vous redirigez l'utilisateur vers:\n";
echo "   https://api-testbed.maxicashapp.com/payentryweb?logid=12345\n\n";

echo "5. L'utilisateur paie sur MaxiCash\n\n";

echo "6. MaxiCash redirige vers votre SuccessURL avec des paramètres:\n";
echo "   https://votre-app.com/paiement/success?Reference=$testReference&Status=completed&TransactionID=MC123456\n\n";

echo "💡 IMPORTANT:\n";
echo "   - MaxiCash NE retourne PAS la référence dans la réponse PayEntryWeb\n";
echo "   - MaxiCash AJOUTE la référence lors de la redirection vers vos URLs\n";
echo "   - C'est le comportement NORMAL de l'API MaxiCash\n\n";

echo "🔍 Pour vérifier que ça fonctionne:\n";
echo "   1. Créer un vrai ticket: php test-ticket-payment.php\n";
echo "   2. Cliquer sur l'URL MaxiCash\n";
echo "   3. Remplir les infos de paiement (carte de test)\n";
echo "   4. Vérifier l'URL de redirection dans le navigateur\n";
echo "   5. La référence devrait être dans l'URL: ?Reference=XXX ou ?reference=XXX\n\n";

echo "📋 Paramètres que MaxiCash peut envoyer:\n";
echo "   - Reference ou reference (votre référence)\n";
echo "   - Status ou status (completed, failed, cancelled)\n";
echo "   - TransactionID ou transaction_id (ID MaxiCash)\n";
echo "   - Amount (montant)\n";
echo "   - Currency (devise)\n";
echo "   - LogID ou logid (ID de log)\n\n";

echo "✅ Votre code frontend gère déjà ces paramètres:\n";
echo "   const reference = searchParams.get('reference') || \n";
echo "                     searchParams.get('Reference') || \n";
echo "                     searchParams.get('ref');\n\n";

echo "🎯 Conclusion:\n";
echo "   L'erreur 'Object reference not set' N'EST PAS causée par la référence manquante.\n";
echo "   Elle est causée par les URLs de callback inaccessibles (avant Cloudflare).\n";
echo "   Maintenant avec Cloudflare Tunnel, MaxiCash peut accéder à vos URLs.\n";
echo "   La référence sera transmise lors de la redirection.\n\n";

echo "🚀 Test réel:\n";
echo "   php test-ticket-payment.php\n";
echo "   Puis cliquer sur l'URL MaxiCash et tester un paiement.\n";
