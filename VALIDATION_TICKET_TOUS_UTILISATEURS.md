# Validation de Tickets - Accessible à Tous les Utilisateurs Connectés

## 🔄 Modification Effectuée

Les fonctionnalités de validation de tickets sont maintenant accessibles à **tous les utilisateurs connectés** (pas seulement les administrateurs).

## 📋 Fonctionnalités Accessibles

### 1. Scan de Billets (QR Code)

Tous les utilisateurs connectés peuvent maintenant:

#### Scanner un billet
```http
POST /api/tickets/scan
Authorization: Bearer {token}

{
  "qr_data": "{\"reference\":\"ABC123\",\"event_id\":1,...}",
  "scan_location": "Entrée principale"
}
```

#### Voir l'historique des scans d'un billet
```http
GET /api/tickets/{reference}/scans
Authorization: Bearer {token}
```

#### Voir les statistiques de scan d'un événement
```http
GET /api/events/{eventId}/scan-stats
Authorization: Bearer {token}
```

### 2. Validation des Paiements en Caisse

#### Lister les tickets en attente de paiement
```http
GET /api/tickets/pending-cash
Authorization: Bearer {token}
```

#### Valider un paiement en caisse
```http
POST /api/tickets/{ticketNumber}/validate-cash
Authorization: Bearer {token}
```

## 🔐 Sécurité

### Authentification Requise

Toutes ces routes nécessitent une authentification via Sanctum:
- L'utilisateur doit être connecté
- Un token Bearer valide doit être fourni dans les headers

### Qui Peut Valider ?

- ✅ **Administrateurs** - Accès complet
- ✅ **Éducateurs** - Peuvent scanner et valider
- ✅ **Parents** - Peuvent scanner et valider
- ✅ **Super Teachers** - Peuvent scanner et valider
- ✅ **Tous les utilisateurs connectés** - Peuvent scanner et valider

### Traçabilité

Chaque scan est enregistré avec:
- L'ID de l'utilisateur qui a scanné (`scanned_by`)
- La date et l'heure du scan (`scanned_at`)
- Le lieu du scan (`scan_location`)
- L'adresse IP (`ip_address`)
- Le user agent (`user_agent`)

## 📁 Fichiers Modifiés

### routes/api.php

**AVANT:**
```php
// Scan de billets - Admin uniquement
Route::post('/tickets/scan', [QRScanController::class, 'scan'])
    ->middleware('auth:sanctum');

// Validation en caisse - Admin uniquement
Route::post('/tickets/{ticketNumber}/validate-cash', [TicketController::class, 'validateCashPayment'])
    ->middleware('admin.only');
```

**APRÈS:**
```php
// Scan de billets - Tous les utilisateurs connectés
Route::post('/tickets/scan', [QRScanController::class, 'scan'])
    ->middleware('auth:sanctum');

// Validation en caisse - Tous les utilisateurs connectés
Route::post('/tickets/{ticketNumber}/validate-cash', [TicketController::class, 'validateCashPayment']);
```

## 🎯 Cas d'Usage

### Scénario 1: Agent à l'Entrée
Un agent (éducateur) scanne les billets à l'entrée de l'événement:
1. Se connecte à l'application
2. Scanne le QR code du billet
3. Le système enregistre le scan avec son ID
4. Le participant peut entrer

### Scénario 2: Caissier
Un caissier (parent bénévole) valide les paiements en caisse:
1. Se connecte à l'application
2. Voit la liste des tickets en attente
3. Reçoit le paiement du participant
4. Valide le ticket
5. Le système enregistre la validation avec son ID

### Scénario 3: Superviseur
Un superviseur (super teacher) vérifie les statistiques:
1. Se connecte à l'application
2. Consulte les statistiques de scan
3. Voit combien de personnes sont entrées
4. Peut scanner des billets si nécessaire

## 🔄 Flux de Validation

### Scan de Billet

```
┌─────────────────┐
│ Utilisateur     │
│ Connecté        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Scanne QR Code  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ POST /tickets/  │
│ scan            │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Enregistrement  │
│ dans DB         │
│ - ticket_id     │
│ - scanned_by    │
│ - scanned_at    │
│ - scan_location │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Réponse avec    │
│ infos du billet │
└─────────────────┘
```

### Validation Paiement en Caisse

```
┌─────────────────┐
│ Utilisateur     │
│ Connecté        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Reçoit paiement │
│ du participant  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ POST /tickets/  │
│ {ref}/validate- │
│ cash            │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Mise à jour     │
│ payment_status  │
│ → 'completed'   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Réponse succès  │
└─────────────────┘
```

## 📊 Contrôleurs Concernés

### QRScanController.php

**Méthodes accessibles:**
- `scan()` - Scanner un billet
- `getScanHistory()` - Historique des scans
- `getEventScanStats()` - Statistiques de scan

**Authentification:** `auth:sanctum` (tous les utilisateurs connectés)

### TicketController.php

**Méthodes accessibles:**
- `pendingCashPayments()` - Liste des tickets en attente
- `validateCashPayment()` - Valider un paiement en caisse

**Authentification:** `auth:sanctum` (tous les utilisateurs connectés)

## ⚠️ Points d'Attention

### 1. Responsabilité

Tous les utilisateurs connectés peuvent maintenant valider des tickets. Assurez-vous que:
- Les utilisateurs comprennent leur responsabilité
- Les actions sont tracées (via `scanned_by`)
- Un audit régulier est effectué

### 2. Formation

Formez les utilisateurs sur:
- Comment scanner correctement un QR code
- Comment valider un paiement en caisse
- Que faire en cas de problème (billet invalide, déjà scanné, etc.)

### 3. Monitoring

Surveillez:
- Le nombre de scans par utilisateur
- Les scans multiples du même billet
- Les validations de paiement suspectes

## 🧪 Test des Modifications

### 1. Tester le Scan de Billet

```bash
# Se connecter en tant qu'utilisateur (non-admin)
POST /api/login
{
  "email": "educateur@example.com",
  "password": "password"
}

# Scanner un billet
POST /api/tickets/scan
Authorization: Bearer {token}
{
  "qr_data": "{\"reference\":\"ABC123\",\"event_id\":1}",
  "scan_location": "Entrée"
}

# Vérifier que le scan est enregistré
GET /api/tickets/ABC123/scans
Authorization: Bearer {token}
```

### 2. Tester la Validation en Caisse

```bash
# Lister les tickets en attente
GET /api/tickets/pending-cash
Authorization: Bearer {token}

# Valider un paiement
POST /api/tickets/ABC123/validate-cash
Authorization: Bearer {token}

# Vérifier que le statut est mis à jour
GET /api/tickets/ABC123
```

## 📝 Recommandations

### Pour les Administrateurs

1. **Créez des comptes dédiés** pour les agents de scan
2. **Formez les utilisateurs** sur l'utilisation correcte
3. **Surveillez les logs** régulièrement
4. **Définissez des procédures** en cas de problème

### Pour les Développeurs

1. **Ajoutez des logs** pour tracer toutes les actions
2. **Implémentez des alertes** pour les comportements suspects
3. **Créez un dashboard** pour visualiser les scans en temps réel
4. **Ajoutez des tests** pour valider le comportement

## 🔮 Évolutions Futures

### Possibles Améliorations

1. **Rôles Granulaires**
   - Créer un rôle "Agent de Scan" spécifique
   - Limiter certaines actions selon le rôle

2. **Limites de Scan**
   - Limiter le nombre de scans par utilisateur/heure
   - Alerter en cas de scan excessif

3. **Validation à Deux Facteurs**
   - Demander une confirmation pour les validations de paiement
   - Ajouter une signature numérique

4. **Audit Trail**
   - Dashboard d'audit complet
   - Export des logs de scan
   - Rapports automatiques

## 🆘 Support

En cas de problème:
1. Vérifier que l'utilisateur est bien connecté
2. Vérifier que le token est valide
3. Consulter les logs du serveur
4. Vérifier les permissions de l'utilisateur

## ✅ Checklist de Déploiement

- [x] Modifier les routes API
- [ ] Tester avec différents rôles d'utilisateurs
- [ ] Former les utilisateurs
- [ ] Mettre à jour la documentation
- [ ] Déployer en production
- [ ] Surveiller les premiers jours
