# Système Complet de Billets Physiques avec Catégories et Prix

## Vue d'ensemble

Ce système permet de générer des QR codes vierges pour des billets physiques qui seront imprimés. Une fois scannés dans l'application mobile, ces QR codes permettent de créer un ticket validé en sélectionnant la catégorie du participant et le type de durée.

## Structure des Prix

### Table `event_prices`

Chaque événement peut avoir plusieurs prix selon :
- **Catégorie** : medecin, etudiant, parent, enseignant
- **Type de durée** : per_day (par jour), full_event (événement complet)

Exemple de prix pour un événement :
```
| ID | Event | Category   | Duration    | Amount | Currency |
|----|-------|------------|-------------|--------|----------|
| 1  | 5     | medecin    | full_event  | 150.00 | USD      |
| 2  | 5     | medecin    | per_day     | 60.00  | USD      |
| 3  | 5     | etudiant   | full_event  | 80.00  | USD      |
| 4  | 5     | etudiant   | per_day     | 35.00  | USD      |
| 5  | 5     | parent     | full_event  | 100.00 | USD      |
| 6  | 5     | enseignant | full_event  | 120.00 | USD      |
```

## Flux de travail complet

### 1. Génération des QR Codes (Dashboard Admin)

1. Admin sélectionne un événement
2. Indique le nombre de QR codes à générer
3. Les QR codes sont générés avec la structure :
```json
{
  "id": "PHY-1234567890-ABC123XYZ",
  "event_id": "5",
  "type": "physical_ticket",
  "created_at": "2024-02-19T10:30:00.000Z"
}
```
4. Les QR codes sont téléchargés et envoyés au designer

### 2. Scan et Activation (Application Mobile)

1. Agent scanne le QR code physique
2. L'app détecte `type === 'physical_ticket'`
3. L'app charge les prix de l'événement via `GET /api/events/{eventId}/prices`
4. L'agent remplit le formulaire :
   - Nom complet
   - Email
   - Téléphone
   - **Catégorie** (médecin, étudiant, parent, enseignant)
   - **Type de durée** (événement complet, par jour)
5. Le prix est automatiquement sélectionné selon la catégorie et la durée
6. L'agent valide le formulaire

### 3. Création du Ticket (Backend)

1. Vérification du `physical_qr_id` (unique, non utilisé)
2. Vérification de l'`event_price_id` (correspond à l'événement, catégorie, durée)
3. Création d'un **Participant** :
```php
Participant::create([
    'event_id' => $eventId,
    'user_id' => auth()->id(), // Agent qui scanne
    'name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'category' => $category,
    'duration_type' => $durationType,
]);
```
4. Création du **Ticket** :
```php
Ticket::create([
    'reference' => 'TKT-xxx',
    'physical_qr_id' => 'PHY-xxx',
    'event_id' => $eventId,
    'participant_id' => $participantId,
    'event_price_id' => $eventPriceId,
    'amount' => $eventPrice->amount,
    'currency' => $eventPrice->currency,
    'pay_type' => 'cash',
    'payment_status' => 'completed',
    'qr_data' => json_encode([...]),
]);
```

## Relations de la base de données

```
events
  ├── event_prices (1:N)
  │     ├── category
  │     ├── duration_type
  │     ├── amount
  │     └── currency
  │
  ├── participants (1:N)
  │     ├── category
  │     ├── duration_type
  │     └── ticket (1:1)
  │
  └── tickets (1:N)
        ├── participant_id (FK)
        ├── event_price_id (FK)
        ├── physical_qr_id (unique)
        └── payment_status
```

## Exemple de flux complet

### Étape 1 : Admin génère 50 QR codes pour l'événement "Conférence Médicale 2024"

```javascript
// QR Code généré
{
  "id": "PHY-1708345200-XYZ789ABC",
  "event_id": "5",
  "type": "physical_ticket",
  "created_at": "2024-02-19T10:00:00.000Z"
}
```

### Étape 2 : Designer imprime les billets avec les QR codes

Les billets physiques sont distribués/vendus à la caisse.

### Étape 3 : Agent scanne le QR code

L'app mobile charge les prix :
```json
{
  "success": true,
  "event": {
    "id": 5,
    "title": "Conférence Médicale 2024",
    "date": "2024-03-15",
    "location": "Kinshasa"
  },
  "prices": [
    {
      "id": 1,
      "category": "medecin",
      "duration_type": "full_event",
      "amount": 150.00,
      "currency": "USD",
      "label": "Médecin - Événement Complet"
    },
    {
      "id": 2,
      "category": "medecin",
      "duration_type": "per_day",
      "amount": 60.00,
      "currency": "USD",
      "label": "Médecin - Par Jour"
    },
    {
      "id": 3,
      "category": "etudiant",
      "duration_type": "full_event",
      "amount": 80.00,
      "currency": "USD",
      "label": "Étudiant - Événement Complet"
    }
  ]
}
```

### Étape 4 : Agent remplit le formulaire

```
Nom: Dr. Jean Dupont
Email: jean.dupont@example.com
Téléphone: +243 XXX XXX XXX
Catégorie: Médecin
Durée: Événement Complet
Prix affiché: $150.00 USD
```

### Étape 5 : Soumission et création

**Requête API :**
```json
POST /api/tickets/physical
{
  "physical_qr_id": "PHY-1708345200-XYZ789ABC",
  "event_id": "5",
  "full_name": "Dr. Jean Dupont",
  "email": "jean.dupont@example.com",
  "phone": "+243 XXX XXX XXX",
  "category": "medecin",
  "duration_type": "full_event",
  "event_price_id": "1"
}
```

**Réponse :**
```json
{
  "success": true,
  "ticket": {
    "id": 123,
    "reference": "TKT-1708345300-ABC123",
    "physical_qr_id": "PHY-1708345200-XYZ789ABC",
    "event_id": 5,
    "participant_id": 45,
    "event_price_id": 1,
    "amount": 150.00,
    "currency": "USD",
    "pay_type": "cash",
    "payment_status": "completed",
    "qr_data": "{...}"
  },
  "participant": {
    "id": 45,
    "name": "Dr. Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243 XXX XXX XXX",
    "category": "medecin",
    "duration_type": "full_event"
  }
}
```

## Avantages du système

1. **Flexibilité des prix** : Différents tarifs selon la catégorie et la durée
2. **Traçabilité complète** : Participant + Ticket + Prix
3. **Contrôle** : Chaque QR physique ne peut être utilisé qu'une fois
4. **Simplicité** : L'agent sélectionne juste la catégorie, le prix est automatique
5. **Validation immédiate** : Le ticket est créé avec `payment_status = 'completed'`

## Sécurité

- ✅ QR code unique (physical_qr_id)
- ✅ Vérification de l'event_price_id
- ✅ Authentification requise (Bearer token)
- ✅ Validation des catégories et durées
- ✅ Traçabilité (qui a activé, quand)

## Statistiques possibles

Avec ce système, on peut facilement générer des statistiques :

```sql
-- Nombre de participants par catégorie
SELECT category, COUNT(*) as total
FROM participants
WHERE event_id = 5
GROUP BY category;

-- Revenus par catégorie
SELECT p.category, SUM(t.amount) as revenue
FROM tickets t
JOIN participants p ON t.participant_id = p.id
WHERE t.event_id = 5 AND t.payment_status = 'completed'
GROUP BY p.category;

-- Répartition par type de durée
SELECT duration_type, COUNT(*) as total
FROM participants
WHERE event_id = 5
GROUP BY duration_type;
```

## Workflow visuel

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN DASHBOARD                          │
│  1. Sélectionner événement                                  │
│  2. Générer 50 QR codes                                     │
│  3. Télécharger/Imprimer                                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    DESIGNER                                 │
│  Créer billets physiques avec QR codes                      │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    CAISSE / VENTE                           │
│  Distribuer les billets physiques                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    AGENT MOBILE                             │
│  1. Scanner QR code physique                                │
│  2. Charger les prix de l'événement                         │
│  3. Remplir formulaire :                                    │
│     - Nom, Email, Téléphone                                 │
│     - Catégorie (médecin/étudiant/parent/enseignant)       │
│     - Durée (événement complet / par jour)                  │
│  4. Prix affiché automatiquement                            │
│  5. Valider                                                 │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND API                              │
│  1. Vérifier physical_qr_id (unique)                        │
│  2. Vérifier event_price_id                                 │
│  3. Créer Participant                                       │
│  4. Créer Ticket (validé)                                   │
│  5. Retourner ticket + participant                          │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    ENTRÉE ÉVÉNEMENT                         │
│  Scanner le QR code du ticket pour valider l'entrée         │
└─────────────────────────────────────────────────────────────┘
```

## Fichiers de documentation

- `QR_BILLETS_PHYSIQUES_SYSTEME.md` : Documentation backend complète
- `MOBILE_BILLETS_PHYSIQUES_IMPLEMENTATION.md` : Guide d'implémentation mobile
- `SYSTEME_BILLETS_PHYSIQUES_COMPLET.md` : Ce fichier (vue d'ensemble)
