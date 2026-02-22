# Configuration Email NLC - info@nlcrdc.org

## 📧 Informations du Serveur Mail

### Détails du Compte
- **Adresse email:** info@nlcrdc.org
- **Nom d'affichage:** Never Limit Children
- **Utilisation actuelle:** 1.40 GB
- **Mot de passe:** Tel que défini lors de la création

### Serveurs
- **Serveur entrant (IMAP/POP):** mail.nlcrdc.org
- **Serveur sortant (SMTP):** mail.nlcrdc.org

### Ports et Chiffrement

#### IMAP (Recommandé)
- **Port avec SSL:** 993 ✅ (Recommandé)
- **Port sans SSL:** 143

#### POP3
- **Port avec SSL:** 995
- **Port sans SSL:** 110

#### SMTP (Envoi)
- **Port avec SSL:** 465 ✅ (Recommandé)
- **Port sans SSL:** 587 ou 25

## ⚙️ Configuration Laravel (.env)

### Configuration Recommandée (SSL Port 465)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.nlcrdc.org
MAIL_PORT=465
MAIL_USERNAME=info@nlcrdc.org
MAIL_PASSWORD=votre_mot_de_passe_ici
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

### Configuration Alternative (TLS Port 587)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.nlcrdc.org
MAIL_PORT=587
MAIL_USERNAME=info@nlcrdc.org
MAIL_PASSWORD=votre_mot_de_passe_ici
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

## 🚀 Étapes de Configuration

### 1. Mettre à jour le fichier .env

```bash
# Ouvrir le fichier .env
nano .env

# Ou avec un éditeur de texte
notepad .env
```

Remplacer les valeurs:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.nlcrdc.org
MAIL_PORT=465
MAIL_USERNAME=info@nlcrdc.org
MAIL_PASSWORD=VOTRE_VRAI_MOT_DE_PASSE
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

⚠️ **Important:** Remplacez `VOTRE_VRAI_MOT_DE_PASSE` par le vrai mot de passe du compte info@nlcrdc.org

### 2. Nettoyer le cache Laravel

```bash
php artisan config:clear
php artisan config:cache
php artisan cache:clear
```

### 3. Tester la configuration

```bash
php artisan tinker
```

Puis dans tinker:
```php
Mail::raw('Test email depuis Laravel avec mail.nlcrdc.org', function ($message) {
    $message->to('votre-email-test@example.com')
            ->subject('Test Configuration Email NLC');
});
```

Si l'email est envoyé sans erreur, la configuration est correcte! ✅

## 🧪 Test avec le Script PHP

```bash
php test-send-notification.php
```

Le script va:
1. Trouver un ticket avec email
2. Afficher la configuration email
3. Demander confirmation
4. Envoyer l'email de test

## 📱 Test via l'API

### 1. Démarrer le serveur
```bash
php artisan serve
```

### 2. Envoyer une notification
```bash
# PowerShell
Invoke-WebRequest -Uri "http://localhost:8000/api/tickets/TKT-REFERENCE/send-notification" -Method POST -ContentType "application/json"

# Ou avec le script
.\test-notification-api.ps1
```

## 🔧 Configuration des Clients Email

### Microsoft Outlook

1. Ouvrir Outlook
2. Fichier > Ajouter un compte
3. Saisir: info@nlcrdc.org
4. Configuration manuelle:
   - **Type:** IMAP
   - **Serveur entrant:** mail.nlcrdc.org
   - **Port:** 993
   - **Chiffrement:** SSL/TLS
   - **Serveur sortant:** mail.nlcrdc.org
   - **Port:** 465
   - **Chiffrement:** SSL/TLS
   - **Nom d'utilisateur:** info@nlcrdc.org
   - **Mot de passe:** [votre mot de passe]

### Apple Mail (Mac/iPhone)

1. Réglages > Mail > Comptes > Ajouter un compte
2. Autre > Ajouter un compte Mail
3. Informations:
   - **Nom:** Never Limit Children
   - **Email:** info@nlcrdc.org
   - **Mot de passe:** [votre mot de passe]
4. Serveur de réception (IMAP):
   - **Nom d'hôte:** mail.nlcrdc.org
   - **Nom d'utilisateur:** info@nlcrdc.org
   - **Mot de passe:** [votre mot de passe]
5. Serveur d'envoi (SMTP):
   - **Nom d'hôte:** mail.nlcrdc.org
   - **Nom d'utilisateur:** info@nlcrdc.org
   - **Mot de passe:** [votre mot de passe]

### Thunderbird

1. Menu > Nouveau > Compte de courrier existant
2. Informations:
   - **Nom:** Never Limit Children
   - **Email:** info@nlcrdc.org
   - **Mot de passe:** [votre mot de passe]
3. Configuration manuelle:
   - **Entrant:** IMAP, mail.nlcrdc.org, 993, SSL/TLS
   - **Sortant:** SMTP, mail.nlcrdc.org, 465, SSL/TLS
   - **Nom d'utilisateur:** info@nlcrdc.org

## 🌐 Accès Webmail

Vous pouvez accéder au webmail via:
- https://mail.nlcrdc.org
- https://webmail.nlcrdc.org
- https://nlcrdc.org/webmail

**Identifiants:**
- **Email:** info@nlcrdc.org
- **Mot de passe:** [votre mot de passe]

## 🔐 Sécurité

### Recommandations

1. **Mot de passe fort:** Utilisez un mot de passe complexe
2. **Ne pas partager:** Le mot de passe ne doit pas être partagé
3. **HTTPS uniquement:** Toujours utiliser HTTPS en production
4. **Surveillance:** Vérifier régulièrement les logs d'envoi
5. **Limite d'envoi:** Respecter les limites du serveur

### Fichier .env

⚠️ **IMPORTANT:** Le fichier `.env` contient des informations sensibles!

- Ne JAMAIS commiter `.env` dans Git
- Ajouter `.env` dans `.gitignore`
- Utiliser `.env.example` comme modèle
- Chaque environnement a son propre `.env`

## 📊 Monitoring

### Vérifier les logs Laravel

```bash
# Voir les derniers logs
tail -f storage/logs/laravel.log

# Ou sur Windows
Get-Content storage/logs/laravel.log -Tail 50 -Wait
```

### Vérifier les emails envoyés

Les emails envoyés sont loggés dans `storage/logs/laravel.log`:
```
[2026-02-21 14:30:00] local.INFO: Mail sent to: john@example.com
```

## 🐛 Dépannage

### Erreur: "Connection refused"

**Cause:** Le serveur mail n'est pas accessible

**Solutions:**
1. Vérifier que `MAIL_HOST=mail.nlcrdc.org` est correct
2. Vérifier que le port 465 est ouvert
3. Tester la connexion:
```bash
telnet mail.nlcrdc.org 465
```

### Erreur: "Authentication failed"

**Cause:** Identifiants incorrects

**Solutions:**
1. Vérifier `MAIL_USERNAME=info@nlcrdc.org`
2. Vérifier le mot de passe dans `MAIL_PASSWORD`
3. Tester la connexion via webmail

### Erreur: "SSL certificate problem"

**Cause:** Problème de certificat SSL

**Solutions:**
1. Vérifier que `MAIL_ENCRYPTION=ssl` (pas `tls`)
2. Essayer avec le port 587 et `MAIL_ENCRYPTION=tls`
3. Vérifier le certificat SSL du serveur

### L'email n'arrive pas

**Solutions:**
1. Vérifier le dossier spam
2. Vérifier les logs Laravel
3. Tester avec une autre adresse email
4. Vérifier les limites d'envoi du serveur

### Erreur: "Could not parse time string"

**Cause:** Format de date/heure invalide dans le template

**Solution:** Déjà corrigé dans le template `ticket-notification.blade.php`

## 📝 Checklist de Déploiement

Avant de déployer en production:

- [ ] Mot de passe configuré dans `.env`
- [ ] Configuration testée avec `php artisan tinker`
- [ ] Email de test envoyé et reçu
- [ ] Logs vérifiés (pas d'erreurs)
- [ ] `.env` ajouté dans `.gitignore`
- [ ] Cache Laravel nettoyé
- [ ] Certificat SSL vérifié
- [ ] Limites d'envoi vérifiées
- [ ] Monitoring configuré

## 📞 Support

### Contact Hébergeur
Si vous rencontrez des problèmes avec le serveur mail:
- Contacter votre hébergeur
- Vérifier les paramètres du compte info@nlcrdc.org
- Demander les logs du serveur mail

### Support Laravel
- Documentation: https://laravel.com/docs/mail
- Email: support@nlcrdc.org

## 🎯 Résumé Rapide

```env
# Configuration à utiliser dans .env
MAIL_MAILER=smtp
MAIL_HOST=mail.nlcrdc.org
MAIL_PORT=465
MAIL_USERNAME=info@nlcrdc.org
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@nlcrdc.org
MAIL_FROM_NAME="Never Limit Children"
```

Puis:
```bash
php artisan config:clear
php artisan config:cache
php test-send-notification.php
```

✅ **Configuration prête pour la production!**
