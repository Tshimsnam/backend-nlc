# Configuration Email pour l'Envoi de Notifications de Billets

## 📧 Configuration Actuelle

L'application utilise **info@nlcrdc.org** comme adresse d'expéditeur pour tous les emails de notification de billets.

## ⚙️ Configuration dans .env

Pour envoyer des emails, vous devez configurer les paramètres SMTP dans votre fichier `.env`:

### Option 1: Gmail (Configuration Actuelle)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

**Important pour Gmail:**
1. Activez l'authentification à deux facteurs sur votre compte Gmail
2. Générez un "Mot de passe d'application" depuis les paramètres de sécurité Google
3. Utilisez ce mot de passe d'application dans `MAIL_PASSWORD`

### Option 2: Serveur SMTP Personnalisé (Recommandé pour Production)

Si vous avez un serveur email pour le domaine `nlcrdc.org`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.nlcrdc.org
MAIL_PORT=587
MAIL_USERNAME=info@nlcrdc.org
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

### Option 3: Services Email Tiers

#### Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.nlcrdc.org
MAILGUN_SECRET=votre-cle-api
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

#### SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=votre-cle-api-sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

## 🚀 Utilisation de la Route

### Endpoint
```
POST /api/tickets/{reference}/send-notification
```

### Exemple de Requête

```bash
curl -X POST http://localhost:8000/api/tickets/TKT-1771703593-H4WITL/send-notification \
  -H "Content-Type: application/json"
```

### Réponse Succès

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

### Réponse Erreur (Pas d'email)

```json
{
  "success": false,
  "message": "Ce ticket n'a pas d'adresse email associée."
}
```

### Réponse Erreur (Ticket non trouvé)

```json
{
  "message": "No query results for model [App\\Models\\Ticket]."
}
```

## 📧 Contenu de l'Email

L'email envoyé contient:

1. **Header avec logo NLC** et titre de l'événement
2. **Informations du billet:**
   - Référence
   - Nom du participant
   - Email et téléphone
   - Catégorie
   - Montant
   - Statut du paiement

3. **Détails de l'événement:**
   - Titre
   - Date(s)
   - Horaire
   - Lieu
   - Détails du lieu

4. **QR Code:**
   - Image du QR code (généré via API externe)
   - Référence du billet

5. **Note importante** (si paiement en attente):
   - Instructions pour finaliser le paiement en caisse

6. **Informations de contact:**
   - Email de l'événement
   - Téléphone de l'événement

7. **Footer NLC:**
   - Logo et slogan
   - Email de contact: info@nlcrdc.org

## 🧪 Test de Configuration Email

Pour tester votre configuration email:

```bash
php artisan tinker
```

Puis dans tinker:

```php
Mail::raw('Test email depuis Laravel', function ($message) {
    $message->to('votre-email@example.com')
            ->subject('Test Email Configuration');
});
```

Si l'email est envoyé avec succès, votre configuration est correcte.

## 🔧 Dépannage

### Erreur: "Connection could not be established"

**Solution:**
- Vérifiez que `MAIL_HOST` et `MAIL_PORT` sont corrects
- Vérifiez que votre pare-feu autorise les connexions sortantes sur le port SMTP
- Pour Gmail, assurez-vous d'utiliser un mot de passe d'application

### Erreur: "Authentication failed"

**Solution:**
- Vérifiez `MAIL_USERNAME` et `MAIL_PASSWORD`
- Pour Gmail, utilisez un mot de passe d'application, pas votre mot de passe normal

### L'email n'arrive pas

**Solution:**
- Vérifiez le dossier spam/courrier indésirable
- Vérifiez les logs Laravel: `storage/logs/laravel.log`
- Testez avec une autre adresse email

### Erreur: "Address in mailbox given [] does not comply with RFC 2822"

**Solution:**
- Vérifiez que `MAIL_FROM_ADDRESS` est une adresse email valide
- Assurez-vous qu'il n'y a pas d'espaces ou de caractères spéciaux

## 📝 Notes Importantes

1. **Production:** Utilisez toujours un service email professionnel (pas Gmail) pour la production
2. **Limite d'envoi:** Gmail limite à 500 emails/jour pour les comptes gratuits
3. **SPF/DKIM:** Configurez les enregistrements DNS SPF et DKIM pour éviter le spam
4. **Queue:** Pour de gros volumes, utilisez les queues Laravel:

```php
Mail::to($ticket->email)->queue(new TicketNotificationMail($ticket));
```

## 🔄 Envoi Automatique

Pour envoyer automatiquement l'email après création du ticket, ajoutez dans `TicketController::store()`:

```php
// Après la création du ticket
if ($ticket->email) {
    Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
}
```

Ou utilisez un Event/Listener Laravel pour une meilleure architecture.

## 📞 Support

Pour toute question sur la configuration email:
- Email: support@nlcrdc.org
- Documentation Laravel Mail: https://laravel.com/docs/mail
