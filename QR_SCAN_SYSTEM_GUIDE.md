# Guide du Système de Scan QR Code

## Vue d'ensemble

Le système de scan QR code permet de suivre et enregistrer tous les scans de billets pour les événements. Chaque fois qu'un billet est scanné, les informations sont enregistrées dans la base de données.

## Architecture

### Tables de base de données

#### Table `ticket_scans`
Enregistre chaque scan individuel d'un billet.

**Colonnes:**
- `id` - Identifiant unique du scan
- `ticket_id` - ID du billet scanné
- `event_id` - ID de l'événement
- `scanned_by` - ID de l'utilisateur qui a scanné (agent)
- `scan_location` - Lieu du scan (Entrée, Sortie, etc.)
- `ip_address` - Adresse IP du scan
- `user_agent` - Navigateur/appareil utilisé
- `scanned_at` - Date et heure du scan
- `created_at`, `updated_at` - Timestamps

#### Colonnes ajoutées à la table `tickets`
- `scan_count` - Nombre total de fois que le billet a été scanné
- `first_scanned_at` - Date du premier scan
- `last_scanned_at` - Date du dernier scan

## API Endpoints

### 1. Scanner un billet

**Endpoint:** `POST /api/tickets/scan`

**Authentification:** Requise (Bearer token)

**Méthodes de scan:**

#### Option A: Scan via QR code (recommandé)
```json
{
  "qr_data": "{\"reference\":\"REF123\",\"event\":\"Mon Événement\",\"participant\":\"Jean Dupont\",\"email\":\"jean@example.com\",\"phone\":\"+243123456789\",\"amount\":\"50.00\",\"currency\":\"USD\",\"category\":\"medecin\",\"date\":\"2026-03-15\",\"location\":\"Kinshasa\"}",
  "scan_location": "Entrée principale"
}
```

#### Option B: Scan via référence
```json
{
  "reference": "REF123",
  "scan_location": "Entrée VIP"
}
```

#### Option C: Scan via téléphone
```json
{
  "phone": "+243123456789",
  "scan_location": "Entrée"
}
```

**Réponse succès (200):**
```json
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "id": 1,
    "reference": "REF123",
    "full_name": "Jean Dupont",
    "email": "jean@example.com",
    "phone": "+243123456789",
    "category": "medecin",
    "amount": "50.00",
    "currency": "USD",
    "payment_status": "completed",
    "scan_count": 3,
    "first_scanned_at": "2026-02-18T10:00:00.000000Z",
    "last_scanned_at": "2026-02-18T14:30:00.000000Z",
    "event": {
      "id": 1,
      "title": "Mon Événement",
      "date": "2026-03-15",
      "time": "09:00:00",
      "location": "Kinshasa"
    },
    "price": {
      "label": "Médecin - Événement complet",
      "category": "medecin",
      "duration_type": "full_event"
    }
  },
  "scan_info": {
    "scan_count": 3,
    "is_first_scan": false,
    "last_scanned_at": "2026-02-18T14:30:00.000000Z"
  }
}
```

**Réponse erreur (404):**
```json
{
  "success": false,
  "message": "Billet introuvable"
}
```

### 2. Historique des scans d'un billet

**Endpoint:** `GET /api/tickets/{reference}/scans`

**Authentification:** Requise

**Exemple:** `GET /api/tickets/REF123/scans`

**Réponse:**
```json
{
  "success": true,
  "ticket_reference": "REF123",
  "total_scans": 3,
  "scans": [
    {
      "id": 3,
      "ticket_id": 1,
      "event_id": 1,
      "scanned_by": 5,
      "scan_location": "Entrée principale",
      "scanned_at": "2026-02-18T14:30:00.000000Z",
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont",
        "email": "agent@example.com"
      }
    },
    {
      "id": 2,
      "ticket_id": 1,
      "event_id": 1,
      "scanned_by": 5,
      "scan_location": "Entrée VIP",
      "scanned_at": "2026-02-18T12:00:00.000000Z",
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont",
        "email": "agent@example.com"
      }
    }
  ]
}
```

### 3. Statistiques de scan pour un événement

**Endpoint:** `GET /api/events/{eventId}/scan-stats`

**Authentification:** Requise

**Exemple:** `GET /api/events/1/scan-stats`

**Réponse:**
```json
{
  "success": true,
  "stats": {
    "event_id": 1,
    "event_title": "Mon Événement",
    "total_tickets": 150,
    "total_scans": 180,
    "unique_tickets_scanned": 120,
    "tickets_not_scanned": 30
  },
  "scans_by_day": [
    {
      "date": "2026-02-18",
      "count": 45
    },
    {
      "date": "2026-02-19",
      "count": 67
    }
  ],
  "scans_by_location": [
    {
      "scan_location": "Entrée principale",
      "count": 120
    },
    {
      "scan_location": "Entrée VIP",
      "count": 60
    }
  ],
  "recent_scans": [
    {
      "id": 180,
      "scanned_at": "2026-02-19T16:45:00.000000Z",
      "ticket": {
        "id": 45,
        "reference": "REF456",
        "full_name": "Marie Martin"
      },
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont"
      }
    }
  ]
}
```

### 4. Statistiques globales des scans (Admin)

**Endpoint:** `GET /api/admin/dashboard/scan-stats`

**Authentification:** Requise (Admin uniquement)

**Réponse:**
```json
{
  "global_stats": {
    "total_scans": 450,
    "unique_tickets_scanned": 320,
    "total_tickets": 400,
    "scan_rate": 80.00
  },
  "scans_by_event": [
    {
      "id": 1,
      "title": "Événement A",
      "date": "2026-03-15",
      "total_tickets": 150,
      "scanned_tickets": 120,
      "total_scans": 180,
      "scan_rate": 80.00
    },
    {
      "id": 2,
      "title": "Événement B",
      "date": "2026-04-20",
      "total_tickets": 250,
      "scanned_tickets": 200,
      "total_scans": 270,
      "scan_rate": 80.00
    }
  ],
  "recent_scans": [...],
  "scans_by_day": [...]
}
```

## Utilisation dans l'application mobile

### Flux de scan

1. **Authentification**
   - L'agent se connecte avec son compte
   - Récupère le token Bearer

2. **Scanner le QR code**
   - L'application mobile scanne le QR code du billet
   - Le QR code contient un JSON avec toutes les informations du billet
   - L'application envoie les données à `POST /api/tickets/scan`

3. **Affichage du résultat**
   - Si succès: Afficher les informations du participant et le nombre de scans
   - Si échec: Afficher le message d'erreur

4. **Méthodes alternatives**
   - Si le QR code ne fonctionne pas, l'agent peut:
     - Entrer manuellement la référence du billet
     - Rechercher par numéro de téléphone

### Exemple de code (React Native / Expo)

```typescript
import { Camera } from 'expo-camera';
import axios from 'axios';

const scanTicket = async (qrData: string, token: string) => {
  try {
    const response = await axios.post(
      'https://api.example.com/api/tickets/scan',
      {
        qr_data: qrData,
        scan_location: 'Entrée principale'
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );

    if (response.data.success) {
      // Afficher les informations du billet
      console.log('Billet scanné:', response.data.ticket);
      console.log('Nombre de scans:', response.data.scan_info.scan_count);
      
      // Afficher une alerte si c'est le premier scan
      if (response.data.scan_info.is_first_scan) {
        alert('Premier scan de ce billet!');
      }
    }
  } catch (error) {
    console.error('Erreur lors du scan:', error);
    alert('Erreur: Billet introuvable ou invalide');
  }
};
```

## Structure du QR Code

Le QR code contient un JSON stringifié avec les informations suivantes:

```json
{
  "reference": "REF123",
  "event": "Nom de l'événement",
  "participant": "Nom complet",
  "email": "email@example.com",
  "phone": "+243123456789",
  "amount": "50.00",
  "currency": "USD",
  "category": "medecin",
  "date": "2026-03-15",
  "location": "Kinshasa"
}
```

## Modèles Laravel

### TicketScan Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketScan extends Model
{
    protected $fillable = [
        'ticket_id',
        'event_id',
        'scanned_by',
        'scan_location',
        'ip_address',
        'user_agent',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    // Relations
    public function ticket() { ... }
    public function event() { ... }
    public function scannedBy() { ... }
}
```

### Ticket Model (mis à jour)

```php
// Nouveaux champs fillable
protected $fillable = [
    // ... autres champs
    'scan_count',
    'first_scanned_at',
    'last_scanned_at',
];

// Nouveaux casts
protected $casts = [
    // ... autres casts
    'scan_count' => 'integer',
    'first_scanned_at' => 'datetime',
    'last_scanned_at' => 'datetime',
];

// Nouvelle relation
public function scans()
{
    return $this->hasMany(TicketScan::class);
}
```

## Dashboard Admin

Le dashboard admin affiche maintenant:

- **Statistiques globales:**
  - Nombre total de scans
  - Nombre de billets scannés (uniques)
  - Taux de scan (pourcentage)

- **Statistiques par événement:**
  - Nombre de billets vendus
  - Nombre de billets scannés
  - Nombre total de scans
  - Taux de scan

- **Scans récents:**
  - Liste des 20 derniers scans
  - Informations du participant
  - Agent qui a scanné
  - Heure du scan

- **Graphiques:**
  - Évolution des scans par jour
  - Scans par lieu (entrée principale, VIP, etc.)

## Sécurité

- Tous les endpoints de scan nécessitent une authentification
- Seuls les utilisateurs authentifiés peuvent scanner des billets
- L'ID de l'agent qui scanne est enregistré automatiquement
- L'adresse IP et le user agent sont enregistrés pour audit

## Notes importantes

1. **Scans multiples:** Un billet peut être scanné plusieurs fois (le compteur s'incrémente)
2. **Premier scan:** Le champ `first_scanned_at` est défini uniquement lors du premier scan
3. **Dernier scan:** Le champ `last_scanned_at` est mis à jour à chaque scan
4. **Historique complet:** Tous les scans sont conservés dans la table `ticket_scans`
5. **Statistiques en temps réel:** Les statistiques sont calculées en temps réel à partir de la base de données

## Prochaines étapes

Pour l'application mobile, voir le fichier `README_APPLICATION_MOBILE.md` pour les détails d'implémentation.
