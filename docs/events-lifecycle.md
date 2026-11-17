# Statut et clôture des événements

Dans **Administration → Événements**, sélectionner **À venir** (`upcoming`) ou
**Terminé** (`past`), puis **Enregistrer**. Le changement conserve les billets,
participants, paiements, quiz et évaluations. Un événement dont la date de fin
est dépassée doit être reprogrammé avant d'être rouvert dans ce formulaire.

L'API administrateur accepte aussi une modification partielle :

```http
PUT /api/events/123
Content-Type: application/json
Authorization: Bearer <token administrateur>

{"status":"past"}
```

## Clôture automatique

La commande `php artisan events:close-finished` passe uniquement les événements
`upcoming` déjà terminés à `past`. Elle est planifiée chaque minute dans
`routes/console.php`. Elle peut être rejouée sans effet sur les événements déjà
archivés. Aucune migration de base n'est nécessaire.

- Fuseau : `Africa/Kinshasa`, dans `config/events.php`.
- Dernier jour : `end_date`, ou `date` si la date de fin est absente.
- Heure : `end_time`, ou la fin du dernier jour si l'heure est absente.
- Formats : dates `YYYY-MM-DD`, heures `16h00`, `16:00` ou `16:00:00`.
- Les anciennes valeurs invalides sont signalées dans la sortie de la commande
  et ignorées ; corriger ces événements ou les marquer manuellement comme terminés.

Prévisualiser les événements qui seront clôturés, sans modifier la base :

```sh
php artisan events:close-finished --dry-run
```

Sur le serveur, le scheduler Laravel doit être lancé. Si le projet dispose déjà
d'une tâche `schedule:run`, la conserver sans en créer une deuxième. Sinon,
configurer une tâche cron exécutée chaque minute (adapter les chemins) :

```cron
* * * * * cd /chemin/backend-nlc && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

En développement, utiliser `php artisan schedule:work`.

## Inscriptions et affichage

Le serveur refuse immédiatement les nouvelles inscriptions, les nouvelles
réservations, leur finalisation et l'activation de nouveaux billets physiques
si le statut est `past` **ou** si la fin est dépassée, même si le scheduler
n'est pas encore passé. Réponse HTTP 409, code `event_registration_closed`.
Les données et règlements des billets existants restent conservés.

Les réponses API des événements exposent `registration_open`. Le site masque
les boutons d'inscription lorsque cette valeur est fausse et affiche un message
de clôture sur la page d'inscription. Le statut `past` pilote le filtre « Passés ».

Les annonces éditoriales (formation de décembre 2026, GSA 2027) restent des
contenus locaux, indépendants de `events`. Leurs dates de fin sont déclarées
dans `src/data/announcements.ts` du site. Elles affichent automatiquement
« Événement terminé » ; le lien Google Forms disparaît de l'annonce et de
l'article après la formation. Cela ne ferme pas le formulaire chez Google :
la réception des réponses doit aussi être désactivée dans Google Forms.

## Vérification isolée

```sh
php vendor/bin/phpunit tests/Feature/EventLifecycleTest.php
```

Ces tests créent une base SQLite en mémoire et n'utilisent pas les données réelles.
