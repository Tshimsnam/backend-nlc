# Test de la recherche de tickets par téléphone

## Route créée
✅ `GET /api/tickets/search?phone={phone}`

## Vérification
La route est bien enregistrée dans Laravel :
```
GET|HEAD  api/tickets/search  API\TicketController@searchByPhone
```

## Comportement

### Cas 1 : Aucun ticket trouvé (404)
Si aucun ticket n'existe avec le numéro de téléphone fourni :
```json
{
  "success": false,
  "message": "Aucun ticket trouvé pour ce numéro de téléphone.",
  "phone": "0827029543",
  "tickets": []
}
```

### Cas 2 : Tickets trouvés (200)
Si des tickets existent avec ce numéro :
```json
{
  "success": true,
  "message": "2 ticket(s) trouvé(s).",
  "phone": "0827029543",
  "count": 2,
  "tickets": [...]
}
```

## Test dans l'application mobile

### Avant de tester
1. Assurez-vous qu'il existe au moins un ticket avec le numéro de téléphone que vous recherchez
2. Vous pouvez créer un ticket de test via :
   - L'interface web d'inscription
   - L'activation d'un billet physique
   - La validation d'un paiement en caisse

### Exemple de test avec Postman
```
GET http://192.168.32.9:8080/api/tickets/search?phone=0827029543
Headers:
  Accept: application/json
```

### Exemple de test avec l'app mobile
```javascript
const searchTickets = async (phone) => {
  try {
    const response = await fetch(
      `${API_URL}/tickets/search?phone=${phone}`,
      {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      }
    );
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      console.log(`${data.count} ticket(s) trouvé(s)`);
      return data.tickets;
    } else {
      console.log('Aucun ticket trouvé');
      return [];
    }
  } catch (error) {
    console.error('Erreur:', error);
    return [];
  }
};
```

## Données retournées
Pour chaque ticket trouvé, vous recevez :
- Informations du ticket (référence, montant, statut de paiement)
- Informations de l'événement (titre, date, lieu)
- Informations du prix (catégorie, montant)
- Informations du participant (nom, email)

## Notes importantes
1. La recherche est sensible à la casse et doit correspondre exactement au numéro enregistré
2. Tous les tickets sont retournés (peu importe leur statut)
3. Les tickets sont triés du plus récent au plus ancien
4. Aucune authentification n'est requise
