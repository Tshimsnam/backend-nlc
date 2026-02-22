# Recherche par téléphone flexible

## Fonctionnalité

La recherche par numéro de téléphone accepte maintenant plusieurs formats et trouve automatiquement les tickets correspondants.

---

## Formats acceptés

### Format international
- `+243 812 345 678` (avec espaces)
- `+243812345678` (sans espaces)
- `243812345678` (sans le +)

### Format local
- `0812345678` (sans espaces)
- `0812 345 678` (avec espaces)
- `0 812 345 678` (avec espaces)

---

## Comment ça fonctionne

### Normalisation automatique

Quand vous recherchez avec `0812345678`, le système cherche automatiquement :
1. `0812345678` (format exact)
2. `+243812345678` (conversion en international)
3. `+243 812 345 678` (avec espaces)
4. Toutes les variantes avec/sans espaces

### Exemple

**Ticket enregistré avec :** `+243 812 345 678`

**Recherches qui fonctionnent :**
- ✅ `+243 812 345 678`
- ✅ `+243812345678`
- ✅ `0812345678`
- ✅ `0812 345 678`
- ✅ `243812345678`

---

## Endpoints concernés

### 1. GET /api/tickets/search?phone={phone}

Recherche tous les tickets avec ce numéro.

**Exemple :**
```bash
GET /api/tickets/search?phone=0812345678
```

**Réponse (succès) :**
```json
{
  "success": true,
  "message": "2 ticket(s) trouvé(s).",
  "phone": "0812345678",
  "count": 2,
  "tickets": [
    {
      "reference": "TKT-123",
      "full_name": "Jean Dupont",
      "phone": "+243 812 345 678",
      ...
    }
  ]
}
```

**Réponse (aucun résultat) :**
```json
{
  "success": false,
  "message": "Aucun ticket trouvé pour ce numéro de téléphone.",
  "phone": "0812345678",
  "searched_variants": [
    "0812345678",
    "+243812345678",
    "+243 812 345 678"
  ],
  "tickets": []
}
```

### 2. POST /api/tickets/scan

Scan de billet par téléphone.

**Exemple :**
```bash
POST /api/tickets/scan
Content-Type: application/json

{
  "phone": "0812345678"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "reference": "TKT-123",
    "phone": "+243 812 345 678",
    ...
  }
}
```

---

## Code pour l'application mobile

### React Native / JavaScript

```javascript
const searchTicketsByPhone = async (phone) => {
  try {
    // Pas besoin de normaliser, l'API le fait automatiquement
    const response = await fetch(
      `${API_URL}/tickets/search?phone=${encodeURIComponent(phone)}`,
      {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      console.log(`${data.count} ticket(s) trouvé(s)`);
      return {
        success: true,
        tickets: data.tickets,
        count: data.count,
      };
    } else {
      console.log('Aucun ticket trouvé');
      return {
        success: false,
        message: data.message,
      };
    }
  } catch (error) {
    console.error('Erreur:', error);
    return {
      success: false,
      message: 'Erreur de connexion',
    };
  }
};
```

### Utilisation

```javascript
// L'utilisateur peut entrer n'importe quel format
const phone = '0812345678'; // ou '+243 812 345 678'

const result = await searchTicketsByPhone(phone);

if (result.success) {
  // Afficher les tickets trouvés
  result.tickets.forEach(ticket => {
    console.log(`Ticket: ${ticket.reference}`);
    console.log(`Nom: ${ticket.full_name}`);
  });
} else {
  // Afficher le message d'erreur
  Alert.alert('Aucun résultat', result.message);
}
```

---

## Logique de conversion

### De local (0) vers international (+243)

```
0812345678 → +243812345678
```

1. Enlever le `0` au début
2. Ajouter `+243` au début

### D'international (+243) vers local (0)

```
+243812345678 → 0812345678
```

1. Enlever `+243` au début
2. Ajouter `0` au début

---

## Avantages

✅ L'utilisateur peut entrer le numéro dans n'importe quel format  
✅ Pas besoin de normaliser côté frontend  
✅ Recherche plus flexible et tolérante  
✅ Fonctionne avec les espaces, tirets, parenthèses  
✅ Compatible avec les deux formats (local et international)  

---

## Test

### Créer un ticket de test
```bash
php create-test-ticket.php
```

### Tester la recherche
```bash
# Format local
curl "http://192.168.40.9:8000/api/tickets/search?phone=0812345678"

# Format international
curl "http://192.168.40.9:8000/api/tickets/search?phone=%2B243812345678"
```

### Vérifier les variantes recherchées

En cas d'échec, la réponse inclut les variantes recherchées :
```json
{
  "success": false,
  "searched_variants": [
    "0812345678",
    "0812345678",
    "+243812345678",
    "+243812345678"
  ]
}
```

---

## Notes techniques

### Nettoyage du numéro

Le système enlève automatiquement :
- Espaces : `+243 812 345 678` → `+243812345678`
- Tirets : `+243-812-345-678` → `+243812345678`
- Parenthèses : `+243 (812) 345 678` → `+243812345678`

### Recherche LIKE

La recherche utilise `LIKE` pour être plus flexible :
```sql
WHERE phone LIKE '%0812345678%'
   OR phone LIKE '%+243812345678%'
   OR phone LIKE '%+243 812 345 678%'
```

Cela permet de trouver le numéro même s'il est enregistré avec des espaces ou d'autres caractères.

---

## Compatibilité

✅ Fonctionne avec tous les opérateurs congolais :
- Vodacom : 081, 082, 089, 097, 098
- Airtel : 084, 085, 089, 099
- Orange : 083, 084, 089, 090
- Africell : 088, 089

✅ Fonctionne avec les formats :
- Local : 0XXXXXXXXX
- International : +243XXXXXXXXX
- Sans indicatif : 243XXXXXXXXX
