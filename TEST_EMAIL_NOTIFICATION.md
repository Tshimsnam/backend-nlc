# Test d'Envoi de Notification Email pour un Billet

## 📋 Prérequis

1. Configuration email dans `.env` (voir `EMAIL_CONFIGURATION.md`)
2. Un ticket existant dans la base de données avec un email valide

## 🧪 Test avec Postman

### 1. Envoyer une Notification

**Endpoint:**
```
POST http://localhost:8000/api/tickets/TKT-1771703593-H4WITL/send-notification
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:** Aucun (la route n'a pas besoin de body)

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Notification envoyée avec succès à participant@example.com",
  "ticket": {
    "reference": "TKT-1771703593-H4WITL",
    "full_name": "John Doe",
    "email": "participant@example.com"
  }
}
```

**Réponse Erreur - Pas d'email (400):**
```json
{
  "success": false,
  "message": "Ce ticket n'a pas d'adresse email associée."
}
```

**Réponse Erreur - Ticket non trouvé (404):**
```json
{
  "message": "No query results for model [App\\Models\\Ticket]."
}
```

**Réponse Erreur - Problème d'envoi (500):**
```json
{
  "success": false,
  "message": "Erreur lors de l'envoi de la notification : [détails de l'erreur]"
}
```

## 🧪 Test avec cURL

### Envoyer une notification

```bash
curl -X POST http://localhost:8000/api/tickets/TKT-1771703593-H4WITL/send-notification \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

### Avec une autre référence

```bash
curl -X POST http://localhost:8000/api/tickets/VOTRE-REFERENCE/send-notification \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

## 🧪 Test avec PHP Artisan Tinker

```bash
php artisan tinker
```

Puis dans tinker:

```php
// Récupérer un ticket
$ticket = \App\Models\Ticket::where('reference', 'TKT-1771703593-H4WITL')->first();

// Vérifier que le ticket existe et a un email
$ticket->email;

// Envoyer l'email manuellement
Mail::to($ticket->email)->send(new \App\Mail\TicketNotificationMail($ticket));
```

## 📧 Vérification de l'Email Reçu

L'email reçu devrait contenir:

### Header
- Logo NLC avec dégradé violet/bleu
- Titre: "🎫 Votre Billet"
- Sous-titre: Nom de l'événement

### Section Informations du Billet
- Référence (en grand, violet)
- Nom du participant
- Email
- Téléphone
- Catégorie
- Montant
- Statut (badge vert si payé, jaune si en attente)

### Section Détails de l'Événement
- Titre de l'événement
- Date(s)
- Horaire
- Lieu
- Détails du lieu

### Section QR Code
- Image du QR code (200x200px)
- Référence du billet en dessous

### Note Importante (si paiement en attente)
- Encadré jaune avec instructions pour payer en caisse

### Section Contact
- Email de l'événement
- Téléphone de l'événement

### Footer
- Logo NLC
- Slogan: "Ensemble pour l'inclusion"
- Email: info@nlcrdc.org
- Note: "Cet email a été envoyé automatiquement"

## 🔍 Vérification des Logs

Si l'email n'est pas envoyé, vérifiez les logs:

```bash
# Voir les dernières lignes du log
tail -f storage/logs/laravel.log

# Ou sur Windows
Get-Content storage/logs/laravel.log -Tail 50
```

## 🐛 Dépannage

### L'email n'est pas envoyé

1. **Vérifier la configuration email:**
```bash
php artisan config:clear
php artisan config:cache
```

2. **Tester la connexion SMTP:**
```bash
php artisan tinker
```
```php
Mail::raw('Test', function ($message) {
    $message->to('votre-email@example.com')->subject('Test');
});
```

3. **Vérifier les logs:**
```bash
tail -f storage/logs/laravel.log
```

### Erreur "Connection refused"

- Vérifiez que `MAIL_HOST` et `MAIL_PORT` sont corrects
- Vérifiez que votre pare-feu autorise les connexions sortantes

### Erreur "Authentication failed"

- Pour Gmail, utilisez un mot de passe d'application
- Vérifiez `MAIL_USERNAME` et `MAIL_PASSWORD`

### L'email arrive dans les spams

- Configurez SPF et DKIM pour votre domaine
- Utilisez un service email professionnel (pas Gmail)
- Vérifiez que `MAIL_FROM_ADDRESS` correspond à votre domaine

## 📝 Intégration dans l'Application

### Envoyer automatiquement après création du ticket

Dans `TicketController::store()`, ajoutez:

```php
// Après la création du ticket
if ($ticket->email) {
    try {
        Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
    } catch (\Exception $e) {
        // Log l'erreur mais ne pas bloquer la création du ticket
        \Log::error('Erreur envoi email ticket: ' . $e->getMessage());
    }
}
```

### Envoyer après validation du paiement

Dans `TicketController::validateCashPayment()`, ajoutez:

```php
// Après la validation du paiement
if ($ticket->email) {
    try {
        Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
    } catch (\Exception $e) {
        \Log::error('Erreur envoi email validation: ' . $e->getMessage());
    }
}
```

### Utiliser les Queues (Recommandé pour Production)

Pour ne pas bloquer la requête HTTP:

```php
Mail::to($ticket->email)->queue(new TicketNotificationMail($ticket));
```

Configuration des queues dans `.env`:
```env
QUEUE_CONNECTION=database
```

Puis lancer le worker:
```bash
php artisan queue:work
```

## 🎯 Cas d'Usage

### 1. Renvoyer un email perdu

Un participant a perdu son email de confirmation:
```bash
POST /api/tickets/ABC123XYZ/send-notification
```

### 2. Envoyer après validation manuelle

Après avoir validé un paiement en caisse:
```bash
# 1. Valider le paiement
POST /api/tickets/ABC123XYZ/validate-cash

# 2. Envoyer la notification
POST /api/tickets/ABC123XYZ/send-notification
```

### 3. Envoyer en masse (script)

Pour envoyer à tous les tickets d'un événement:

```php
$tickets = Ticket::where('event_id', 1)
    ->whereNotNull('email')
    ->where('payment_status', 'completed')
    ->get();

foreach ($tickets as $ticket) {
    Mail::to($ticket->email)->queue(new TicketNotificationMail($ticket));
}
```

## 📊 Statistiques d'Envoi

Pour tracker les emails envoyés, vous pouvez ajouter un champ dans la table `tickets`:

```php
// Migration
Schema::table('tickets', function (Blueprint $table) {
    $table->timestamp('notification_sent_at')->nullable();
    $table->integer('notification_count')->default(0);
});

// Dans le controller
$ticket->update([
    'notification_sent_at' => now(),
    'notification_count' => $ticket->notification_count + 1,
]);
```

## 🔐 Sécurité

### Limiter les envois

Pour éviter le spam, ajoutez une limite:

```php
// Vérifier le dernier envoi
if ($ticket->notification_sent_at && $ticket->notification_sent_at->diffInMinutes(now()) < 5) {
    return response()->json([
        'success' => false,
        'message' => 'Veuillez attendre 5 minutes avant de renvoyer la notification.',
    ], 429);
}
```

### Authentification

Pour protéger la route, ajoutez le middleware auth:

```php
Route::post('/tickets/{ticketNumber}/send-notification', [TicketController::class, 'sendNotification'])
    ->middleware('auth:sanctum');
```

## 📞 Support

Pour toute question:
- Email: support@nlcrdc.org
- Documentation: EMAIL_CONFIGURATION.md
