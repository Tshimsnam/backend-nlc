# Déploiement du backend en production

Le backend Laravel est hébergé chez Hostinger, sur le domaine `www.nlcevents.org`. Le site public est déployé sur Vercel, sur le domaine `nlcrdc.org`.

Ce guide couvre notamment la première livraison des modules Orientation et Consortium. Pour le détail fonctionnel, voir `ORIENTATION.md` et `CONSORTIUM.md`.

## Architecture

```
Navigateur → nlcrdc.org/api/... → relais Vercel → www.nlcevents.org/api/...
```

Le relais est défini dans le `vercel.json` du frontend. Le navigateur ne voit que `nlcrdc.org` : le cookie de session y reste propre au site, ce qui évite les erreurs « CSRF token mismatch » liées aux cookies entre deux domaines.

L'administration et les notifications MaxiCash restent directement sur `www.nlcevents.org`.

Ne pas définir `SESSION_DOMAIN=.nlcrdc.org` : le serveur répond depuis `nlcevents.org`, et le navigateur refuserait ce cookie sur l'administration.

## Flux des branches

`franck_local` → `dev` → `main`

La production se déploie depuis `main`. Passer de `dev` à `main` par pull request.

## Prérequis sur l'hébergement

Vérifier avant de commencer :

- **PHP 8.2 ou plus** pour le site. Le PHP utilisé en ligne de commande et par le cron peut être différent : contrôler aussi sa version.
- **Extensions PHP** : `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `dom`, `pdo_mysql`.
- **Cron chaque minute** autorisé par l'hébergeur. Les e-mails, dont les liens de connexion des familles valables 30 minutes, partent par la file d'attente. Si la fréquence minimale est plus longue, revoir ce point avant d'ouvrir le service.
- **Dossier web** : `www.nlcevents.org` doit pointer sur le dossier `public/` du projet. Le reste du projet, dont `storage/app/private`, doit rester inaccessible depuis le web.

## 1. Sauvegarder

1. Exporter la base de données avec phpMyAdmin.
2. Télécharger le dossier `storage/` et le fichier `.env`.
3. Garder une copie de l'ancien code.
4. Noter le commit actuellement en production, s'il est connu.

Changer ou déconnecter le dépôt Git relié à Hostinger peut écraser les fichiers du dossier cible : sauvegarder d'abord.

## 2. Envoyer le code

**Avec le déploiement Git de Hostinger :** vérifier sur la page Git que le dépôt est relié, que la branche est `main` et que le dossier de destination est le bon. Git ne copie que les fichiers : `vendor/` n'est pas dans le dépôt.

**Avec un terminal ou SSH :**

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
```

**Sans terminal :** préparer sur un poste local une archive du code de `main`, avec un dossier `vendor` installé par `composer install --no-dev --optimize-autoloader`, puis l'envoyer par le gestionnaire de fichiers.

Dans tous les cas, ne jamais écraser `.env` ni `storage/` sur le serveur.

## 3. Configurer le `.env` de production

Ne pas copier un `.env` de développement : il peut contenir le mode debug, le sandbox MaxiCash et des adresses de tunnel.

**Conserver le même `APP_KEY`.** Les brouillons, messages et notes sont chiffrés avec cette clé : la régénérer les rendrait illisibles.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://www.nlcevents.org

FRONTEND_WEBSITE_URL=https://nlcrdc.org
FRONTEND_NLC=https://www.nlcrdc.org

SESSION_DRIVER=database
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database

ORIENTATION_FRONTEND_URL=https://nlcrdc.org
ORIENTATION_MAXICASH_ENABLED=false
ORIENTATION_CENTRE_ADDRESS=
ORIENTATION_PAYMENT_INSTRUCTIONS=
NLC_SECRETARIAT_EMAIL=

CONSORTIUM_FRONTEND_URL=https://nlcrdc.org
CONSORTIUM_SECRETARIAT_EMAIL=
CONSORTIUM_AGREEMENT_URL=
CONSORTIUM_AGREEMENT_VERSION=
```

Garder `ORIENTATION_MAXICASH_ENABLED=false` tant que le contrat de paiement n'a pas été validé en sandbox (voir `ORIENTATION.md`).

## 4. Appliquer les migrations

Vérifier d'abord l'état de la base :

```bash
php artisan migrate:status
```

Appliquer ensuite **uniquement les migrations manquantes**, dans cet ordre. L'option `--force` est obligatoire en production.

```bash
php artisan migrate --force --path=database/migrations/2026_10_26_000000_create_consultation_requests_table.php
```

```bash
php artisan migrate --force --path=database/migrations/2026_10_27_000000_add_scheduling_fields_to_consultation_requests_table.php
```

```bash
php artisan migrate --force --path=database/migrations/2026_10_31_000000_create_consortium_tables.php
```

```bash
php artisan migrate --force --path=database/migrations/2026_10_31_010000_extend_consultation_orientation_workflow.php
```

Sans terminal, lancer chaque commande par une tâche cron unique, puis la supprimer juste après.

**À ne jamais lancer en production :** `php artisan migrate:fresh`, qui efface toutes les données, même s'il figure comme dernier recours dans `MIGRATION_PRODUCTION.txt`. Ne pas lancer non plus `clean-and-migrate.php` ni `fix-migrations.php` sans les avoir relus.

## 5. Reconstruire les caches

```bash
php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

À relancer après chaque modification du `.env` ou chaque envoi de code.

## 6. Configurer les tâches cron

L'hébergement ne garde pas de worker permanent : deux tâches cron chaque minute le remplacent. Adapter le chemin du projet et celui de PHP, qui varie selon l'hébergeur.

Planificateur :

```bash
* * * * * cd /CHEMIN_DU_BACKEND && /CHEMIN_DE_PHP artisan schedule:run >/dev/null 2>&1
```

File d'attente :

```bash
* * * * * cd /CHEMIN_DU_BACKEND && /CHEMIN_DE_PHP artisan queue:work --stop-when-empty --tries=3 --timeout=60 --max-time=50 >/dev/null 2>&1
```

Surveiller les jobs en échec :

```bash
php artisan queue:failed
```

## 7. Déployer le frontend

Déployer le backend d'abord. Ensuite, sur Vercel :

1. Définir `VITE_API_URL=/api`. Cette valeur est fixée au build : un redéploiement est nécessaire.
2. Déployer une version dont le `vercel.json` contient le relais `/api`.

## 8. Vérifier

- [ ] Les événements, les billets et le paiement MaxiCash existants fonctionnent toujours.
- [ ] La connexion à `https://www.nlcevents.org/login` fonctionne, ainsi que `/admin/consultations` et `/admin/consortium`.
- [ ] Un parcours famille complet passe sur `nlcrdc.org` : e-mail de connexion, session, dépôt d'une demande.
- [ ] Un document de 10 Mo passe à travers le relais.
- [ ] Les limites de débit visent chaque visiteur, et non tous les visiteurs ensemble.
- [ ] Les e-mails partent, et les tâches cron s'exécutent.

## Quand revenir en arrière

- Les événements, les billets ou la connexion à l'administration ne fonctionnent plus.
- Une migration échoue en cours de route.
- Les erreurs 500 se répètent dans `storage/logs/laravel.log`.

Remettre l'ancien code et l'ancien `.env`, puis restaurer la sauvegarde de la base si elle a déjà changé. Sur Vercel, revenir au déploiement précédent.

## Limite connue

Le backend fait confiance à l'en-tête `X-Forwarded-For` de tout appelant, car les adresses de Vercel ne sont pas fixes. Quelqu'un qui appelle `www.nlcevents.org` directement peut donc falsifier son adresse et contourner les limites de débit par IP. Les autres contrôles (session, CSRF, autorisations) ne dépendent pas de cet en-tête.

## Avant l'ouverture au public

Ces points ne bloquent pas le déploiement technique, mais ils bloquent l'ouverture des modules aux familles et aux partenaires. Ils relèvent de NLC :

- [ ] Enregistrer les professionnels réels et définir les conditions tarifaires.
- [ ] Faire valider les contenus par le référent clinique.
- [ ] Fournir l'accord officiel du consortium et sa version.
- [ ] Fixer la durée de conservation des dossiers d'enfants.
