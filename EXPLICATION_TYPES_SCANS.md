# Types de Scans QR - Explication

## 🔍 Deux Systèmes de Scan Différents

L'application utilise deux types de scans QR distincts avec des objectifs différents.

## 📊 1. Event Scans (Scans d'Événements)

### Table: `event_scans`

### Objectif
Suivre les **consultations des pages d'événements** via QR code.

### Utilisation
Quand quelqu'un scanne le QR code d'un événement (affiché sur une affiche, un flyer, etc.), cela enregistre:
- Combien de personnes ont consulté la page de l'événement
- D'où viennent les visiteurs (IP, user agent)
- Quel type d'appareil ils utilisent

### Données Enregistrées
```php
[
    'event_id' => 1,
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'device_type' => 'mobile',
    'scanned_at' => '2024-01-15 14:30:00',
]
```

### Cas d'Usage
- **Marketing**: Mesurer l'efficacité des affiches/flyers
- **Statistiques**: Savoir combien de personnes s'intéressent à l'événement
- **Analyse**: Comprendre d'où viennent les visiteurs

### Route API
```http
POST /api/events/{slug}/scan
```

### Contrôleur
`EventScanController.php`

### Pas d'authentification requise
N'importe qui peut scanner le QR code d'un événement.

---

## 🎫 2. Ticket Scans (Scans de Billets)

### Table: `ticket_scans`

### Objectif
**Valider l'entrée des participants** à l'événement en scannant leur billet.

### Utilisation
Quand un agent à l'entrée scanne le QR code du billet d'un participant, cela:
- Valide que le billet est authentique
- Enregistre l'heure d'entrée
- Compte le nombre de fois qu'un billet a été scanné
- Identifie qui a scanné le billet

### Données Enregistrées
```php
[
    'ticket_id' => 123,
    'event_id' => 1,
    'scanned_by' => 5, // ID de l'agent
    'scan_location' => 'Entrée principale',
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'scanned_at' => '2024-01-15 18:00:00',
]
```

### Cas d'Usage
- **Contrôle d'accès**: Vérifier que le participant a un billet valide
- **Traçabilité**: Savoir qui est entré et quand
- **Sécurité**: Détecter les tentatives de fraude (billet scanné plusieurs fois)
- **Statistiques**: Taux de présence réel vs billets vendus

### Route API
```http
POST /api/tickets/scan
Authorization: Bearer {token}
```

### Contrôleur
`QRScanController.php`

### Authentification requise
Seuls les utilisateurs connectés peuvent scanner des billets.

---

## 📈 Statistiques dans le Dashboard

### Avant (Problème)
Le dashboard affichait uniquement `total_qr_scans` qui comptait seulement les `TicketScan`.

Si vous aviez 2 enregistrements dans `event_scans`, ils n'étaient pas comptés.

### Après (Solution)
Le dashboard affiche maintenant les deux types de scans séparément:

```typescript
interface DashboardStats {
  // ... autres stats
  total_ticket_scans: number;  // Scans de billets (validation entrée)
  total_event_scans: number;   // Scans d'événements (consultation page)
  tickets_scanned: number;     // Billets uniques scannés
}
```

### Cartes Affichées

1. **Scans de billets (entrées)**
   - Nombre total de fois qu'un billet a été scanné
   - Inclut les scans multiples du même billet
   - Icône: Ticket (indigo)

2. **Scans d'événements (pages vues)**
   - Nombre de fois que la page d'un événement a été consultée via QR
   - Mesure l'intérêt pour l'événement
   - Icône: Calendar (cyan)

3. **Billets uniques scannés**
   - Nombre de billets différents qui ont été scannés au moins une fois
   - Mesure le taux de présence réel
   - Icône: CheckCircle (teal)

---

## 🔄 Flux de Données

### Flux Event Scan

```
┌─────────────────┐
│ Utilisateur     │
│ (non connecté)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Scanne QR code  │
│ sur affiche     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ POST /events/   │
│ {slug}/scan     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Enregistrement  │
│ dans event_scans│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Redirection     │
│ vers page event │
└─────────────────┘
```

### Flux Ticket Scan

```
┌─────────────────┐
│ Agent           │
│ (connecté)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Scanne QR code  │
│ du billet       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ POST /tickets/  │
│ scan            │
│ + Bearer token  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Vérification    │
│ du billet       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Enregistrement  │
│ dans            │
│ ticket_scans    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Mise à jour     │
│ scan_count      │
│ du ticket       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Réponse avec    │
│ infos du billet │
└─────────────────┘
```

---

## 🗄️ Structure des Tables

### Table: event_scans

```sql
CREATE TABLE event_scans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    scanned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);
```

### Table: ticket_scans

```sql
CREATE TABLE ticket_scans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ticket_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    scanned_by BIGINT, -- ID de l'utilisateur
    scan_location VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    scanned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (scanned_by) REFERENCES users(id)
);
```

---

## 📊 Exemples de Requêtes

### Compter les Event Scans

```php
$eventScans = EventScan::count();
// Résultat: 2 (vos 2 enregistrements)
```

### Compter les Ticket Scans

```php
$ticketScans = TicketScan::count();
// Résultat: nombre de fois qu'un billet a été scanné
```

### Compter les Billets Uniques Scannés

```php
$uniqueTicketsScanned = Ticket::where('scan_count', '>', 0)->count();
// Résultat: nombre de billets différents scannés
```

### Event Scans par Événement

```php
$scansPerEvent = EventScan::select('event_id', DB::raw('COUNT(*) as count'))
    ->groupBy('event_id')
    ->get();
```

### Ticket Scans par Événement

```php
$scansPerEvent = TicketScan::select('event_id', DB::raw('COUNT(*) as count'))
    ->groupBy('event_id')
    ->get();
```

---

## 🎯 Cas d'Usage Pratiques

### Scénario 1: Campagne Marketing

**Objectif**: Mesurer l'efficacité d'une campagne d'affichage

1. Créer un événement
2. Générer un QR code pour l'événement
3. Imprimer des affiches avec le QR code
4. Distribuer les affiches dans différents lieux
5. Suivre les `event_scans` pour voir combien de personnes scannent
6. Analyser d'où viennent les scans (IP, device_type)

**Résultat**: Vous savez quels lieux génèrent le plus d'intérêt.

### Scénario 2: Contrôle d'Accès

**Objectif**: Valider l'entrée des participants

1. Participant achète un billet
2. Participant reçoit un QR code unique
3. À l'entrée, l'agent scanne le QR code
4. Le système vérifie que le billet est valide
5. Enregistrement dans `ticket_scans`
6. Le participant peut entrer

**Résultat**: Vous savez exactement qui est entré et quand.

### Scénario 3: Détection de Fraude

**Objectif**: Détecter les billets scannés plusieurs fois

1. Agent scanne un billet
2. Le système vérifie `scan_count` du ticket
3. Si `scan_count > 0`, alerte "Billet déjà scanné"
4. L'agent peut refuser l'entrée ou enquêter

**Résultat**: Protection contre les billets dupliqués.

---

## 🔧 Modifications Apportées

### Backend (DashboardController.php)

**Avant:**
```php
'total_qr_scans' => TicketScan::count(),
```

**Après:**
```php
'total_ticket_scans' => TicketScan::count(), // Scans de billets
'total_event_scans' => EventScan::count(),   // Scans d'événements
'tickets_scanned' => Ticket::where('scan_count', '>', 0)->count(),
```

### Frontend (AdminDashboard.tsx)

**Ajout de 3 nouvelles cartes:**

1. Scans de billets (entrées)
2. Scans d'événements (pages vues)
3. Billets uniques scannés

---

## 📝 Résumé

| Caractéristique | Event Scans | Ticket Scans |
|----------------|-------------|--------------|
| **Table** | `event_scans` | `ticket_scans` |
| **Objectif** | Mesurer l'intérêt | Valider l'entrée |
| **Authentification** | Non requise | Requise |
| **QR Code** | QR de l'événement | QR du billet |
| **Utilisateur** | Tout le monde | Agents connectés |
| **Traçabilité** | IP, device | IP, device, agent |
| **Contrôleur** | EventScanController | QRScanController |
| **Route** | `/events/{slug}/scan` | `/tickets/scan` |

---

## ✅ Vérification

Pour vérifier que tout fonctionne:

1. **Event Scans**: Scannez le QR code d'un événement depuis la page publique
2. **Ticket Scans**: Connectez-vous et scannez un billet
3. **Dashboard**: Vérifiez que les deux compteurs s'incrémentent séparément

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
