# Recherche de tickets par numéro de téléphone

## Endpoint
```
GET /api/tickets/search?phone={phone}
```

## Description
Permet de rechercher tous les tickets associés à un numéro de téléphone donné.

## Paramètres

### Query Parameters
- `phone` (required, string) : Le numéro de téléphone à rechercher

## Réponses

### Succès (200)
```json
{
  "success": true,
  "message": "2 ticket(s) trouvé(s).",
  "phone": "0827029543",
  "count": 2,
  "tickets": [
    {
      "id": 1,
      "reference": "TKT-123456",
      "physical_qr_id": null,
      "event_id": 1,
      "participant_id": 1,
      "event_price_id": 1,
      "full_name": "John Doe",
      "email": "john@example.com",
      "phone": "0827029543",
      "category": "student_1day",
      "amount": "50.00",
      "currency": "USD",
      "payment_status": "completed",
      "created_at": "2024-02-22T10:30:00.000000Z",
      "event": {
        "id": 1,
        "title": "Conférence 2024",
        "date": "2024-03-15",
        "location": "Kinshasa"
      },
      "price": {
        "id": 1,
        "category": "student_1day",
        "amount": "50.00",
        "label": "Étudiant - 1 jour"
      },
      "participant": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ]
}
```

### Aucun ticket trouvé (404)
```json
{
  "success": false,
  "message": "Aucun ticket trouvé pour ce numéro de téléphone.",
  "phone": "0827029543",
  "tickets": []
}
```

### Erreur de validation (422)
```json
{
  "message": "The phone field is required.",
  "errors": {
    "phone": [
      "The phone field is required."
    ]
  }
}
```

## Exemples d'utilisation

### cURL
```bash
curl -X GET "http://localhost:8000/api/tickets/search?phone=0827029543" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)
```javascript
const phone = '0827029543';
const response = await fetch(`/api/tickets/search?phone=${phone}`, {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
  }
});

const data = await response.json();
console.log(data);
```

### PHP
```php
$phone = '0827029543';
$url = "http://localhost:8000/api/tickets/search?phone=" . urlencode($phone);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);
```

## Notes
- La recherche retourne tous les tickets (peu importe leur statut de paiement)
- Les tickets sont triés par date de création (du plus récent au plus ancien)
- Les relations `event`, `price` et `participant` sont chargées automatiquement
- Aucune authentification n'est requise pour cette route
