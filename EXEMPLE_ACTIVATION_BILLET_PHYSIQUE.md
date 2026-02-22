# Exemple Complet d'Activation de Billet Physique

## Contexte

Événement : **"Le trouble du spectre autistique et la scolarité"**
- Date : 03-04 Avril 2025
- Lieu : Kitumaini, Paris
- Type : Séminaire (2 jours)

## Prix disponibles

Basé sur le seeder `EventSeeder.php` :

| ID | Catégorie | Durée | Montant | Label | Description |
|----|-----------|-------|---------|-------|-------------|
| 1  | medecin | full_event | $50 | Médecin | - |
| 2  | etudiant | per_day | $15 | Étudiants | 15$/jour |
| 3  | etudiant | full_event | $20 | Étudiants | 20$ deux jours |
| 4  | parent | per_day | $15 | Parents | 15$/jour |
| 5  | enseignant | per_day | $20 | Enseignants | 20$/jour |

## Flux complet d'activation

### Étape 1 : Admin génère les QR codes

L'admin accède au dashboard et génère 100 QR codes pour l'événement.

**QR Code généré (exemple) :**
```json
{
  "id": "PHY-1708345200-XYZ789ABC",
  "event_id": "1",
  "type": "physical_ticket",
  "created_at": "2024-02-19T10:00:00.000Z"
}
```

### Étape 2 : Designer imprime les billets

Les 100 QR codes sont envoyés au designer qui crée des billets physiques élégants avec les QR codes imprimés dessus.

### Étape 3 : Vente à la caisse

Les billets physiques sont vendus à la caisse. Le client achète un billet mais ne remplit pas encore ses informations.

### Étape 4 : Agent scanne le QR code

Un agent avec l'application mobile scanne le QR code du billet physique.

**L'app détecte :** `type === 'physical_ticket'`

**L'app charge les prix :**
```
GET /api/events/1/prices
```

**Réponse :**
```json
{
  "success": true,
  "event": {
    "id": 1,
    "title": "Le trouble du spectre autistique et la scolarité",
    "date": "2025-04-03",
    "location": "Kitumaini, Paris"
  },
  "prices": [
    {
      "id": 1,
      "category": "medecin",
      "duration_type": "full_event",
      "amount": 50.00,
      "currency": "USD",
      "label": "Médecin",
      "description": null,
      "display_label": "Médecin"
    },
    {
      "id": 2,
      "category": "etudiant",
      "duration_type": "per_day",
      "amount": 15.00,
      "currency": "USD",
      "label": "Étudiants",
      "description": "15$/jour",
      "display_label": "Étudiants - 15$/jour"
    },
    {
      "id": 3,
      "category": "etudiant",
      "duration_type": "full_event",
      "amount": 20.00,
      "currency": "USD",
      "label": "Étudiants",
      "description": "20$ deux jours",
      "display_label": "Étudiants - 20$ deux jours"
    },
    {
      "id": 4,
      "category": "parent",
      "duration_type": "per_day",
      "amount": 15.00,
      "currency": "USD",
      "label": "Parents",
      "description": "15$/jour",
      "display_label": "Parents - 15$/jour"
    },
    {
      "id": 5,
      "category": "enseignant",
      "duration_type": "per_day",
      "amount": 20.00,
      "currency": "USD",
      "label": "Enseignants",
      "description": "20$/jour",
      "display_label": "Enseignants - 20$/jour"
    }
  ]
}
```

### Étape 5 : Interface mobile affichée

L'agent voit un formulaire avec :

```
┌─────────────────────────────────────────────────────┐
│  Activer Billet Physique                            │
├─────────────────────────────────────────────────────┤
│                                                      │
│  📅 Le trouble du spectre autistique et la scolarité│
│     03 avril 2025                                   │
│     Kitumaini, Paris                                │
│                                                      │
│  🔲 ID Billet Physique                              │
│     PHY-1708345200-XYZ789ABC                        │
│                                                      │
│  ─────────────────────────────────────────────────  │
│                                                      │
│  Informations du Participant                        │
│                                                      │
│  Nom complet *                                      │
│  [                                    ]             │
│                                                      │
│  Email *                                            │
│  [                                    ]             │
│                                                      │
│  Numéro de téléphone *                              │
│  [                                    ]             │
│                                                      │
│  Catégorie et Prix *                                │
│  Sélectionnez la catégorie du participant           │
│                                                      │
│  ┌─────────────────────────────────────────┐       │
│  │ Médecin                          ✓      │       │
│  │ $50.00 USD                              │       │
│  └─────────────────────────────────────────┘       │
│                                                      │
│  ┌─────────────────────────────────────────┐       │
│  │ Étudiants - 15$/jour                    │       │
│  │ $15.00 USD                              │       │
│  └─────────────────────────────────────────┘       │
│                                                      │
│  ┌─────────────────────────────────────────┐       │
│  │ Étudiants - 20$ deux jours              │       │
│  │ $20.00 USD                              │       │
│  └─────────────────────────────────────────┘       │
│                                                      │
│  ┌─────────────────────────────────────────┐       │
│  │ Parents - 15$/jour                      │       │
│  │ $15.00 USD                              │       │
│  └─────────────────────────────────────────┘       │
│                                                      │
│  ┌─────────────────────────────────────────┐       │
│  │ Enseignants - 20$/jour                  │       │
│  │ $20.00 USD                              │       │
│  └─────────────────────────────────────────┘       │
│                                                      │
│  💵 Mode de paiement                                │
│     Paiement en Caisse                    [CASH]   │
│                                                      │
│  [  ✓  Activer le Billet  ]                        │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### Étape 6 : Agent remplit le formulaire

**Exemple : Étudiant pour 2 jours**

```
Nom complet: Marie Dupont
Email: marie.dupont@example.com
Téléphone: +243 XXX XXX XXX
Prix sélectionné: Étudiants - 20$ deux jours ($20.00 USD)
```

### Étape 7 : Soumission

**Requête API :**
```
POST /api/tickets/physical
Authorization: Bearer {token}
Content-Type: application/json

{
  "physical_qr_id": "PHY-1708345200-XYZ789ABC",
  "event_id": "1",
  "full_name": "Marie Dupont",
  "email": "marie.dupont@example.com",
  "phone": "+243 XXX XXX XXX",
  "event_price_id": "3"
}
```

### Étape 8 : Backend traite la requête

1. ✅ Vérifie que `PHY-1708345200-XYZ789ABC` n'est pas déjà utilisé
2. ✅ Vérifie que l'événement ID 1 existe
3. ✅ Vérifie que le prix ID 3 existe et appartient à l'événement 1
4. ✅ Récupère les infos du prix :
   - category: "etudiant"
   - duration_type: "full_event"
   - amount: 20.00
   - currency: "USD"

5. ✅ Crée le participant :
```sql
INSERT INTO participants (event_id, user_id, name, email, phone, category, duration_type)
VALUES (1, 5, 'Marie Dupont', 'marie.dupont@example.com', '+243 XXX XXX XXX', 'etudiant', 'full_event');
-- ID généré: 45
-- user_id = 5 : C'est l'ID de l'agent connecté qui a activé le billet
```

6. ✅ Crée le ticket :
```sql
INSERT INTO tickets (
  reference, physical_qr_id, event_id, participant_id, event_price_id,
  full_name, email, phone, amount, currency, pay_type, payment_status, qr_data
)
VALUES (
  'TKT-1708345300-ABC123',
  'PHY-1708345200-XYZ789ABC',
  1,
  45,
  3,
  'Marie Dupont',
  'marie.dupont@example.com',
  '+243 XXX XXX XXX',
  20.00,
  'USD',
  'cash',
  'completed',
  '{"reference":"TKT-1708345300-ABC123","event_id":1,"amount":20.00,"currency":"USD","payment_mode":"cash","category":"etudiant","duration_type":"full_event"}'
);
-- ID généré: 123
```

### Étape 9 : Réponse API

```json
{
  "success": true,
  "ticket": {
    "id": 123,
    "reference": "TKT-1708345300-ABC123",
    "physical_qr_id": "PHY-1708345200-XYZ789ABC",
    "event_id": 1,
    "participant_id": 45,
    "event_price_id": 3,
    "full_name": "Marie Dupont",
    "email": "marie.dupont@example.com",
    "phone": "+243 XXX XXX XXX",
    "amount": 20.00,
    "currency": "USD",
    "pay_type": "cash",
    "payment_status": "completed",
    "qr_data": "{\"reference\":\"TKT-1708345300-ABC123\",\"event_id\":1,\"amount\":20.00,\"currency\":\"USD\",\"payment_mode\":\"cash\",\"category\":\"etudiant\",\"duration_type\":\"full_event\"}",
    "created_at": "2024-02-19T10:05:00.000Z",
    "event": {
      "id": 1,
      "title": "Le trouble du spectre autistique et la scolarité"
    }
  },
  "participant": {
    "id": 45,
    "name": "Marie Dupont",
    "email": "marie.dupont@example.com",
    "phone": "+243 XXX XXX XXX",
    "category": "etudiant",
    "duration_type": "full_event"
  },
  "message": "Billet physique activé avec succès"
}
```

### Étape 10 : Affichage du ticket

L'app mobile affiche le ticket activé avec :
- Référence : TKT-1708345300-ABC123
- Participant : Marie Dupont
- Catégorie : Étudiants - 20$ deux jours
- QR code pour l'entrée

### Étape 11 : Entrée à l'événement

Le jour de l'événement, Marie scanne son ticket (TKT-1708345300-ABC123) à l'entrée pour valider sa présence.

## Cas d'usage multiples

### Cas 1 : Médecin pour l'événement complet
```
Prix sélectionné: Médecin ($50.00)
→ Participant créé avec category="medecin", duration_type="full_event"
→ Ticket créé avec amount=50.00
```

### Cas 2 : Parent pour 1 jour
```
Prix sélectionné: Parents - 15$/jour ($15.00)
→ Participant créé avec category="parent", duration_type="per_day"
→ Ticket créé avec amount=15.00
```

### Cas 3 : Enseignant pour 1 jour
```
Prix sélectionné: Enseignants - 20$/jour ($20.00)
→ Participant créé avec category="enseignant", duration_type="per_day"
→ Ticket créé avec amount=20.00
```

## Avantages

1. **Flexibilité** : Un seul QR code physique peut être utilisé pour n'importe quelle catégorie
2. **Simplicité** : L'agent sélectionne juste le prix dans une liste
3. **Traçabilité** : Chaque activation est enregistrée avec participant + ticket
4. **Sécurité** : Chaque QR physique ne peut être utilisé qu'une seule fois
5. **Validation immédiate** : Le ticket est créé avec `payment_status = 'completed'`

## Statistiques possibles

Après l'événement, on peut générer des statistiques :

### Revenus par catégorie

```sql
-- Nombre de participants par catégorie
SELECT 
  CONCAT(ep.label, COALESCE(CONCAT(' - ', ep.description), '')) as categorie,
  COUNT(*) as total,
  SUM(t.amount) as revenus
FROM tickets t
JOIN event_prices ep ON t.event_price_id = ep.id
WHERE t.event_id = 1 AND t.payment_status = 'completed'
GROUP BY ep.id, ep.label, ep.description
ORDER BY total DESC;
```

**Résultat exemple :**
```
| Catégorie                    | Total | Revenus |
|------------------------------|-------|---------|
| Étudiants - 20$ deux jours   | 45    | $900    |
| Parents - 15$/jour           | 30    | $450    |
| Enseignants - 20$/jour       | 15    | $300    |
| Médecin                      | 8     | $400    |
| Étudiants - 15$/jour         | 2     | $30     |
|------------------------------|-------|---------|
| TOTAL                        | 100   | $2,080  |
```

### Traçabilité : Qui a activé les billets ?

```sql
-- Nombre de billets activés par agent
SELECT 
  u.name as agent,
  u.email as agent_email,
  COUNT(p.id) as billets_actives,
  SUM(t.amount) as total_montant
FROM participants p
JOIN users u ON p.user_id = u.id
JOIN tickets t ON t.participant_id = p.id
WHERE p.event_id = 1
GROUP BY u.id, u.name, u.email
ORDER BY billets_actives DESC;
```

**Résultat exemple :**
```
| Agent          | Email                  | Billets Activés | Total Montant |
|----------------|------------------------|-----------------|---------------|
| Jean Martin    | jean.martin@nlc.com    | 45              | $950          |
| Sophie Dubois  | sophie.dubois@nlc.com  | 35              | $680          |
| Pierre Lefebvre| pierre.l@nlc.com       | 20              | $450          |
|----------------|------------------------|-----------------|---------------|
| TOTAL          |                        | 100             | $2,080        |
```

### Billets physiques activés vs non activés

```sql
-- Statistiques des QR codes physiques
SELECT 
  COUNT(DISTINCT physical_qr_id) as total_qr_generes,
  COUNT(DISTINCT CASE WHEN physical_qr_id IS NOT NULL THEN physical_qr_id END) as qr_utilises,
  (100 - COUNT(DISTINCT CASE WHEN physical_qr_id IS NOT NULL THEN physical_qr_id END)) as qr_non_utilises
FROM tickets
WHERE event_id = 1;
```

**Note :** Cette requête suppose que vous avez généré 100 QR codes. Pour un suivi précis, vous devriez créer une table `physical_qr_codes` pour enregistrer tous les QR générés.
