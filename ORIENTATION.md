# Parcours d’orientation des familles

Cette version fait évoluer les demandes de consultation existantes. Elle accueille les enfants de moins de 18 ans, pour un premier entretien d’orientation sans avis psychiatrique obligatoire. Elle ne produit aucun diagnostic ni score automatique. Le tarif est fixé par proposition en USD, de 0 à 100 000 USD, avec deux décimales maximum.

## Installation sur la base cible

Sauvegarder la base et les documents, puis appliquer **uniquement la nouvelle migration** si les deux migrations de consultation historiques sont déjà appliquées :

```powershell
php artisan migrate --path=database/migrations/2026_10_31_010000_extend_consultation_orientation_workflow.php
```

Sur une installation sans tables de consultation, appliquer d’abord, dans cet ordre :

```powershell
php artisan migrate --path=database/migrations/2026_10_26_000000_create_consultation_requests_table.php
php artisan migrate --path=database/migrations/2026_10_27_000000_add_scheduling_fields_to_consultation_requests_table.php
```

La nouvelle migration ajoute des colonnes à `consultation_requests` et des tables `orientation_*`. Elle conserve les dossiers et fichiers historiques. Elle ne crée aucun compte de démonstration et n’applique pas les anciennes migrations des autres modules.

Les anciens dossiers sont consultables par l’administrateur. Leur accès famille doit être activé explicitement après vérification du responsable légal ; aucune possession d’une ancienne référence ou d’un ancien jeton ne donne accès au nouveau portail. Un dossier adulte ne peut pas être activé dans ce parcours.

## Configuration et points d’entrée

Backend :

```dotenv
ORIENTATION_FRONTEND_URL=https://www.example.org
ORIENTATION_MAXICASH_ENABLED=false
ORIENTATION_CENTRE_ADDRESS=
ORIENTATION_PAYMENT_INSTRUCTIONS=
NLC_SECRETARIAT_EMAIL=secretariat@example.org
```

Le site utilise `VITE_API_URL` suivi de `/orientation`, ou une URL dédiée :

```dotenv
VITE_ORIENTATION_API_URL=https://api.example.org/api/orientation
```

- Formulaire : `/consultation`.
- Suivi famille : `/espace-famille`.
- Affectations et notes professionnelles : `/espace-professionnel`.
- Administration : `/admin/consultations`.
- Intervenants : `/admin/consultations/professionnels`.
- Configuration métier : `/admin/consultations/configuration`.

L’ancien chemin `POST /api/consultations` reste disponible mais utilise désormais la session famille vérifiée et la protection CSRF, comme `POST /api/orientation/requests`. Les anciens clients sans session doivent migrer vers le nouveau parcours. Les anciens contrôleurs de dépôt et de programmation directe ne sont plus routés.

L’administrateur configure l’adresse du centre, le secrétariat, la durée proposée par défaut et les instructions de règlement électronique. La durée par défaut est de 45 minutes, modifiable ; elle n’est pas une durée clinique imposée.

Conserver `APP_TIMEZONE=UTC` pour le stockage. Les dates de rendez-vous sont saisies avec un fuseau explicite, stockées en UTC et affichées dans le fuseau de la famille. L’administration affiche les horaires à Kinshasa. Utiliser `APP_DEBUG=false` en production.

Le frontend et le backend doivent utiliser HTTPS et, de préférence, le même domaine parent. Le portail utilise la session HttpOnly du backend et la protection CSRF du middleware web. Autoriser l’origine exacte du site via `FRONTEND_WEBSITE_URL` dans la configuration CORS et conserver les credentials ; configurer les cookies sécurisés et SameSite selon l’hébergement. Après modification des variables, reconstruire les caches du backend et le build Vite selon le processus habituel.

## Parcours et autorisations

1. Le demandeur vérifie son e-mail par un lien personnel à usage unique, valable 30 minutes, confirmé par un bouton. La session dure au maximum huit heures et respecte aussi l’expiration de session du serveur.
2. Il remplit cinq étapes, enregistre explicitement son brouillon sur le serveur, puis dépose une demande pour un seul enfant. Une clé de soumission évite les doublons en cas de nouvelle tentative réseau.
3. Une mère, un père ou un tuteur doit déclarer être habilité à agir. Pour un tiers ou un professionnel orientant, un représentant légal avec un e-mail distinct doit autoriser la démarche. Avant cet accord, les détails, pièces et propositions sont bloqués. Le représentant reçoit seulement les éléments d’identification nécessaires à sa décision avant de l’autoriser.
4. L’administration vérifie le dossier, peut demander des compléments et sollicite un professionnel actif dont les compétences et le cadre de confidentialité ont été validés par NLC. Les coordonnées d’un représentant encore en attente peuvent être corrigées ; les anciens liens correspondants sont invalidés.
5. Le professionnel voit d’abord une sollicitation sans dossier clinique. Il accepte ou décline sa disponibilité. Après acceptation, la proposition est transmise à la famille et le professionnel accède au dossier affecté.
6. La famille accepte le créneau, le professionnel et le tarif. Une séance à 0 USD est confirmée immédiatement. Une séance payante attend la vérification du règlement.
7. Les propositions ont une échéance antérieure au rendez-vous. Les réservations empêchent les chevauchements pour le même professionnel ; les propositions expirées libèrent le créneau. Une annulation suivie d’un report crée une nouvelle proposition nécessitant une nouvelle acceptation.
8. Le professionnel peut enregistrer des notes privées chiffrées. La synthèse destinée à la famille est publiée explicitement lors de la clôture de l’entretien, disponible à partir de son début. Les notes privées restent exclues du portail famille et de la vue administrative ordinaire.

Le personnel de secrétariat utilise ici les comptes administrateurs existants, avec vérification de l’identité, de l’activité et du rôle en base. Cette première version ne crée pas de rôle distinct de secrétariat. Les comptes professionnels, internes ou consultants, sont séparés du consortium et limités aux dossiers affectés. Une annulation ou un refus retire l’accès professionnel au dossier concerné, sauf autre affectation valide. Une désactivation ou un changement d’e-mail invalide l’accès ; aucun consultant ne parcourt les dossiers des autres.

Les fichiers PDF/JPG/PNG sont privés : 10 Mo par fichier, cinq à la soumission, dix et 100 Mo au total par dossier. Les téléchargements sont autorisés par dossier et tracés. Les brouillons et messages du portail sont chiffrés au repos ; les champs historiques des demandes restent dans la base existante. Protéger la base, ses sauvegardes, le stockage et `APP_KEY`. Ne jamais régénérer cette clé sur des données chiffrées existantes.

Le nouveau formulaire ne sauvegarde plus de données de santé dans le stockage navigateur et supprime l’ancien brouillon local lors de son ouverture. Aucun jeton de session n’est conservé dans localStorage. Les fichiers sélectionnés sont envoyés à la soumission, pas avec le brouillon. Les changements d’identité dans un autre onglet exigent une actualisation avant une action.

Le récapitulatif et les messages se consultent dans l’espace ; la messagerie et les notifications s’actualisent manuellement. L’interface du parcours est en français dans cette version ; les liens du site restent compatibles français/anglais.

## Paiements électroniques

Les consultations possèdent leurs propres propositions et transactions. Elles ne créent pas de billet, ne modifient pas les inscriptions aux événements et ne réutilisent pas automatiquement le numéro marchand des dons.

**Vérification administrative :** renseigner les instructions et le libellé dans Configuration. La famille ouvre les instructions du compte dédié, effectue son paiement électronique et transmet la référence. Le secrétariat contrôle la réception sur le compte marchand, saisit la référence et une justification, puis confirme le règlement. Une déclaration ou une capture seule ne confirme jamais la séance.

**MaxiCash :** l’intégration est désactivée par défaut. Pour l’activer, renseigner les identifiants et endpoints `services.maxicash` existants et `ORIENTATION_MAXICASH_ENABLED=true`. Aucun mode de simulation ne crée un faux succès en l’absence d’identifiants. Les requêtes utilisent TLS vérifié et ne transmettent ni nom d’enfant ni information clinique.

Le flux utilise `PayEntryWeb` avec montant en cents et `maxiDollar`. Les URLs de retour sont construites par le serveur. Le callback dédié `/api/orientation/payment/callback` ne croit pas son statut reçu : il déclenche une requête authentifiée vers `CheckPaymentStatusByReference`. Le simple retour sur le site ou `ResponseStatus=success` n’est jamais considéré comme un règlement.

L’adaptateur confirme uniquement si la réponse de vérification contient dans `ResponseData` un objet JSON avec `Reference`, `Status` (`paid` ou `completed`), `Amount` en cents, `Currency=maxiDollar` et `TransactionID`, tous concordants. La documentation publique n’explicite pas le contenu réel de `ResponseData` pour tous les comptes : **valider ce contrat avec une réponse sandbox du compte marchand avant l’ouverture réelle**. Une réponse incomplète ou différente passe en vérification administrative ; elle ne confirme pas le rendez-vous.

Sources de l’adaptateur : [documentation d’intégration](https://developer.maxicashapp.com/), [PayEntryWeb](https://webapi.maxicashapp.com/Help/Api/POST-Integration-PayEntryWeb), [vérification par référence, documentation sandbox](https://webapi-test.maxicashapp.com/Help/Api/POST-Integration-CheckPaymentStatusByReference).

Une seule tentative ouverte est autorisée par proposition. Une initialisation ambiguë reste à vérifier avant un nouvel essai, pour limiter les doubles débits. Les références de transaction sont uniques. Un règlement tardif ou supplémentaire est enregistré et déclenche une régularisation, sans récupérer automatiquement un créneau expiré ou réattribué.

Les remboursements et avoirs sont décidés et effectués hors de l’application par NLC, puis enregistrés avec justification. L’application ne déclenche aucun transfert ni remboursement automatique. Un avoir d’un ancien rendez-vous n’est pas automatiquement appliqué à une nouvelle proposition : le secrétariat doit convenir de sa régularisation et établir le nouveau tarif en conséquence. Les règles d’annulation, de remboursement et les comptes marchands restent à valider par NLC.

## Mails, échéances et exploitation

Les notifications sont persistées avant l’envoi. Elles contiennent une référence et des informations pratiques, sans questionnaire médical ni pièces jointes. Le mail de confirmation ouvre le bon dossier dans l’espace sécurisé, où se trouve le bouton de séance. Le lien de visioconférence est renseigné par l’administration ; aucune salle Meet/Zoom n’est créée automatiquement.

Configurer le transport mail réel, le cache et la file existants, avec leurs tables si le driver choisi les nécessite :

```powershell
php artisan queue:work --tries=3 --timeout=60
php artisan schedule:run
```

Déclencher `schedule:run` chaque minute avec le mécanisme du serveur. La commande `orientation:maintain` s’exécute toutes les cinq minutes, sans chevauchement : elle expire les propositions, alerte une fois le secrétariat après 72 heures sans première réponse, rappelle une fois les rendez-vous dans les prochaines 24 heures, remet en file les mails en attente/échec depuis plus d’une heure et relance les vérifications MaxiCash en attente des sept derniers jours.

Une première réponse utile est enregistrée lors d’un message administratif, d’une demande de compléments, d’une sollicitation professionnelle annoncée à la famille ou d’une clôture motivée. L’accusé de réception automatique ne la remplace pas. Les 72 heures sont calendaires après dépôt, y compris si une autorisation est encore attendue ; elles ne garantissent pas la tenue de la séance dans ce délai.

Les mails de connexion expirés et les confirmations/propositions devenues obsolètes avant envoi sont ignorés. Une panne de mail conserve le dossier et son alerte. Un statut SMTP « envoyé » ne signifie pas que le destinataire a lu le message. Surveiller les jobs en échec et les alertes de l’administration.

## Vérification

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'Consultation|OrientationWorkflowTest|ConsortiumTest'
```

Les tests de consultation imposent SQLite en mémoire avant toute migration, chargent seulement les migrations du module et simulent stockage, file, mails et prestataire. Ils ne lancent aucun `migrate:fresh`, ne modifient pas `.env` et n’utilisent pas la base réelle. Les essais navigateur utilisent également une base de démonstration distincte, des adresses `example.test` et un transport mail en mémoire.

Le build Vite est vérifié. Le contrôle TypeScript global signale une incompatibilité préexistante dans `src/lib/motion.tsx:37` (style Framer Motion propagé à un `div` natif), indépendante du parcours d’orientation ; aucun nouveau diagnostic ne concerne ce module.

Recette finale : 51 tests réussis, 437 assertions (orientation et régressions consortium), parcours navigateur du dépôt jusqu’à la confirmation d’un paiement simulé, lien de séance et absence de débordement horizontal à 390 pixels pour le suivi et le formulaire. Aucun paiement ni e-mail externe n’a été effectué pour ces essais.

Avant ouverture : appliquer la migration sur la base cible, enregistrer les professionnels réels et leurs compétences, renseigner les lieux et le secrétariat, activer le mail/worker/planificateur, vérifier les paiements sur le compte marchand, définir les conditions tarifaires et faire valider les contenus par le référent clinique. La durée de conservation des dossiers, les demandes d’accès/rectification et la procédure de suppression doivent être fixées par NLC ; cette version n’efface aucun dossier automatiquement. Le questionnaire clinique de dix pages n’est pas présenté comme un formulaire diagnostique validé : cette version met à disposition des rubriques professionnelles de recueil et une synthèse, sans reproduire ni calculer les critères DSM.
