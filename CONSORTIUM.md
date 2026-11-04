# Module consortium NLC

Première version : comptes partenaires, vérification d'e-mail, brouillon d'adhésion, documents privés et versions, suivi, échanges, compléments, décisions et notifications. L'attribution des financements reste un module distinct.

## Installation

Les dépendances PHP existantes suffisent. Appliquer uniquement la migration du nouveau module sur la base cible après sauvegarde :

```powershell
php artisan migrate --path=database/migrations/2026_10_31_000000_create_consortium_tables.php
```

Cette migration ne modifie pas les tables historiques. Les comptes partenaires sont dans `consortium_accounts`, avec un guard de session `consortium`, et sont reliés à leur structure. Ils n'ajoutent pas de rôle à l'enum historique `users.role`. Plusieurs comptes peuvent être reliés à une structure ; l'invitation de collaborateurs n'est pas ouverte dans cette version.

Configurer dans l'environnement du backend (sans exposer les valeurs dans le client) :

```dotenv
CONSORTIUM_FRONTEND_URL=https://www.example.org
CONSORTIUM_SECRETARIAT_EMAIL=consortium@example.org
CONSORTIUM_AGREEMENT_URL=https://www.example.org/documents/accord-consortium.pdf
CONSORTIUM_AGREEMENT_VERSION=version-officielle
```

Les deux paramètres d'accord restent vides tant que le document officiel n'est pas fourni. Aucun accord fictif n'est présenté : NLC peut demander le document signé pendant l'instruction. L'acceptation exige les dernières versions validées des statuts, du règlement intérieur, de l'autorisation et de l'accord signé.

Le site utilise `VITE_API_URL` existant suivi de `/consortium`. Il est aussi possible de définir une URL spécifique :

```dotenv
VITE_CONSORTIUM_API_URL=https://api.example.org/api/consortium
```

Le portail utilise des cookies de session HttpOnly et un jeton CSRF obtenu via `/api/consortium/csrf` puis envoyé dans `X-CSRF-TOKEN`. Ne placer aucune clé `API-SECRET`, aucun mot de passe ni token de session dans les variables Vite ou le stockage navigateur. Autoriser l'origine exacte du site dans `config/cors.php` via `FRONTEND_WEBSITE_URL` ; conserver `supports_credentials=true`.

Pour la production, servir le site et le backend sur le même domaine parent, utiliser HTTPS, `SESSION_SECURE_COOKIE=true`, et vérifier `SESSION_SAME_SITE` ainsi que le proxy éventuel. Des domaines distincts (par exemple un domaine Vercel et un autre domaine API) peuvent entraîner le blocage des cookies tiers. Les comptes historiques et la connexion administrateur gardent leur fonctionnement.

Après modification d'une configuration mise en cache, la reconstruire selon le processus de déploiement existant.

## Entrées

- Site public : `/consortium` (liens ajoutés au menu et au pied de page).
- Portail : `/espace-partenaire`, inscription, connexion, vérification et récupération de mot de passe.
- Administration Blade : `/admin/consortium`, accessible depuis la barre latérale.
- API : routes dans `routes/consortium.php`, chargées par `web.php` pour bénéficier des sessions et de la protection CSRF.

L'administration du consortium vérifie l'identité réelle et le rôle administrateur en base, en complément de la session historique `admin_user`.

## Règles de cette version

- Une candidature par structure ; compte référent créé à l'inscription, puis adresse vérifiée avant accès au dossier.
- Brouillons incomplets autorisés. Sauvegarde explicite via « Enregistrer » ou « Enregistrer et continuer ».
- Statuts : `draft`, `submitted`, `under_review`, `needs_information`, `accepted`, `rejected`, `withdrawn`.
- Soumission : validation des champs et des pièces obligatoires côté serveur, verrou transactionnel, snapshot des données et identifiants de pièces, incrément de révision.
- Documents PDF/JPG/PNG, 10 Mo maximum par fichier ; quota cumulé de 100 Mo et 50 versions par dossier. Stockage sur le disque `local` privé. Aucun chemin serveur exposé dans l'API.
- Après soumission, les informations sont en lecture seule. Une demande de compléments rouvre le brouillon ; les types de documents demandés exigent une nouvelle version et une réponse écrite lors de la retransmission.
- Acceptation et refus sont motivés. Les notes internes restent absentes des réponses partenaires. La publication de la structure n'est pas automatique.
- Récapitulatif du dossier téléchargeable en texte UTF-8.
- Messagerie par dossier, sans chat temps réel. L'actualisation du suivi et des échanges se fait à l'ouverture de l'espace ou avec le bouton Actualiser.
- Portail consortium en français dans cette première version ; les liens de navigation restent compatibles avec le système français/anglais du site.
- Pas d'attribution ni de versement automatique de financement, de cotisation, de signature électronique qualifiée ou d'annuaire public automatique.

## E-mails et rappels

Les alertes du portail sont persistées dans `consortium_notifications`. Les e-mails sont envoyés par `SendConsortiumMail` après validation de la transaction, avec trois tentatives et une clé d'unicité par notification. Les liens de vérification et de récupération sont chiffrés au repos, expirent après une heure et ne sont pas renvoyés par l'API des notifications.

Configurer le transport mail existant et démarrer un worker en production :

```powershell
php artisan queue:work --tries=3 --timeout=30
```

Le système existant doit disposer des tables `jobs`/`failed_jobs` et du cache configuré. Les commandes suivantes sont intégrées au planificateur Laravel :

- `consortium:remind` : chaque jour à 08:00, heure de Kinshasa. Un rappel par demande encore ouverte, à partir de deux jours avant l'échéance.
- `consortium:retry-mail --limit=100` : chaque heure, remet en file les notifications en attente/échec depuis plus d'une heure. Les liens expirés sont exclus.

Activer le déclenchement du planificateur (`schedule:run` chaque minute, ou le mécanisme du serveur) et surveiller les jobs en échec. La réussite SMTP signifie une remise au transport, pas une lecture par le destinataire. Une panne mail ne supprime pas le dossier ni sa notification dans le portail.

## Vérification

```powershell
php vendor/phpunit/phpunit/phpunit --filter ConsortiumTest
```

Ces tests imposent SQLite `:memory:`, une session et un cache de test, des fichiers simulés et des jobs simulés. Ils ne lancent pas `migrate:fresh` et ne modifient pas la base configurée dans `.env`.

Couverture : inscription et vérification à usage unique, expiration, connexion, récupération de mot de passe, modification du compte, isolation entre structures, validation des pièces, versions, snapshot, double soumission, contrôles administrateur, compléments, acceptation conditionnée aux pièces, notes privées, notifications, rappels uniques, retrait et récapitulatif.

Avant ouverture réelle : renseigner l'accord officiel et sa version, confirmer les règles d'admission/documentation avec NLC, vérifier le domaine/cookies et le transport mail, tester le stockage/sauvegarde/restauration des documents, puis effectuer une recette avec des structures pilotes. Aucun compte de démonstration n'est créé par la migration.

État des contrôles lors de l'implémentation : build Vite réussi et 30 tests consortium/consultations réussis. Le contrôle TypeScript global relève une incompatibilité déjà présente dans `src/lib/motion.tsx:37` (style Framer Motion propagé à un `div` natif) ; aucun nouveau diagnostic TypeScript ne concerne le module consortium.
