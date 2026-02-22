# Guide de Test - Notification Email (Windows)

## 🪟 Commandes PowerShell pour Windows

Sur Windows, `curl` est un alias pour `Invoke-WebRequest` qui a une syntaxe différente.

### ❌ Ne fonctionne PAS sur Windows:
```bash
curl -X POST http://localhost:8000/api/tickets/ABC123/send-notification
```

### ✅ Utilisez plutôt:

#### Option 1: PowerShell (Commande complète)
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/api/tickets/TKT-1771703593-H4WITL/send-notification" -Method POST -ContentType "application/json" | Select-Object -ExpandProperty Content
```

#### Option 2: Script PowerShell Interactif (Recommandé)
```powershell
.\test-notification-api.ps1
```

Ce script:
- Vérifie que le serveur Laravel est démarré
- Demande la référence du ticket
- Vérifie que le ticket existe et a un email
- Demande confirmation avant d'envoyer
- Affiche le résultat avec couleurs

#### Option 3: Script PHP Direct
```bash
php test-send-notification.php
```

Ce script:
- Trouve automatiquement un ticket avec email
- Affiche les détails du ticket
- Demande confirmation
- Envoie l'email directement (sans passer par l'API)

## 📋 Étapes Complètes

### 1. Démarrer le serveur Laravel

```bash
php artisan serve
```

Le serveur démarre sur `http://localhost:8000`

### 2. Vérifier qu'un ticket existe

```bash
php artisan tinker
```

Puis dans tinker:
```php
// Voir tous les tickets avec email
Ticket::whereNotNull('email')->get(['reference', 'full_name', 'email']);

// Ou créer un ticket de test
$ticket = Ticket::first();
$ticket->email = 'votre-email@example.com';
$ticket->save();
```

### 3. Tester l'envoi

#### Méthode A: Via le script PowerShell
```powershell
.\test-notification-api.ps1
```

#### Méthode B: Via le script PHP
```bash
php test-send-notification.php
```

#### Méthode C: Via PowerShell directement
```powershell
$reference = "TKT-1771703593-H4WITL"
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/tickets/$reference/send-notification" -Method POST -ContentType "application/json"
$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
```

## 🔧 Configuration Email Requise

Avant de tester, configurez votre `.env`:

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

### Pour Gmail:
1. Allez sur https://myaccount.google.com/security
2. Activez l'authentification à deux facteurs
3. Allez dans "Mots de passe des applications"
4. Générez un nouveau mot de passe pour "Mail"
5. Copiez ce mot de passe dans `MAIL_PASSWORD`

Puis:
```bash
php artisan config:clear
php artisan config:cache
```

## 📊 Réponses Attendues

### Succès
```json
{
  "success": true,
  "message": "Notification envoyée avec succès à john@example.com",
  "ticket": {
    "reference": "TKT-1771703593-H4WITL",
    "full_name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Erreur - Pas d'email
```json
{
  "success": false,
  "message": "Ce ticket n'a pas d'adresse email associée."
}
```

### Erreur - Ticket non trouvé
```json
{
  "message": "No query results for model [App\\Models\\Ticket]."
}
```

## 🐛 Dépannage

### Erreur: "Impossible de se connecter au serveur distant"

**Cause:** Le serveur Laravel n'est pas démarré

**Solution:**
```bash
php artisan serve
```

### Erreur: "Connection refused" lors de l'envoi

**Cause:** Configuration SMTP incorrecte

**Solution:**
1. Vérifiez `.env`
2. Testez avec tinker:
```bash
php artisan tinker
```
```php
Mail::raw('Test', function ($message) {
    $message->to('votre-email@example.com')->subject('Test');
});
```

### Erreur: "Authentication failed"

**Cause:** Mot de passe incorrect

**Solution:**
- Pour Gmail, utilisez un mot de passe d'application
- Vérifiez `MAIL_USERNAME` et `MAIL_PASSWORD`

### L'email n'arrive pas

**Solutions:**
1. Vérifiez le dossier spam
2. Vérifiez les logs:
```bash
Get-Content storage/logs/laravel.log -Tail 50
```
3. Testez avec une autre adresse email

## 📝 Exemples Complets

### Exemple 1: Test Rapide avec PowerShell

```powershell
# 1. Démarrer le serveur (dans un terminal)
php artisan serve

# 2. Dans un autre terminal PowerShell
.\test-notification-api.ps1

# 3. Suivre les instructions à l'écran
```

### Exemple 2: Test avec PHP Direct

```bash
# 1. Pas besoin de démarrer le serveur
php test-send-notification.php

# 2. Suivre les instructions à l'écran
```

### Exemple 3: Test Manuel avec PowerShell

```powershell
# 1. Démarrer le serveur
php artisan serve

# 2. Envoyer la requête
$uri = "http://localhost:8000/api/tickets/TKT-1771703593-H4WITL/send-notification"
$response = Invoke-WebRequest -Uri $uri -Method POST -ContentType "application/json"

# 3. Afficher le résultat
$result = $response.Content | ConvertFrom-Json
Write-Host "Succès: $($result.success)"
Write-Host "Message: $($result.message)"
```

## 🎯 Commandes Utiles

### Voir tous les tickets avec email
```bash
php artisan tinker
```
```php
Ticket::whereNotNull('email')->get(['reference', 'full_name', 'email']);
```

### Mettre à jour l'email d'un ticket
```bash
php artisan tinker
```
```php
$ticket = Ticket::where('reference', 'ABC123')->first();
$ticket->email = 'nouveau-email@example.com';
$ticket->save();
```

### Voir les logs en temps réel
```powershell
Get-Content storage/logs/laravel.log -Wait -Tail 20
```

### Nettoyer le cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📞 Support

Si vous rencontrez des problèmes:
1. Vérifiez `storage/logs/laravel.log`
2. Consultez `EMAIL_CONFIGURATION.md`
3. Testez avec `test-send-notification.php`
4. Contactez: support@nlcrdc.org
