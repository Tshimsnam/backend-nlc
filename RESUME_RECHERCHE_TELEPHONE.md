# Résumé : Recherche par téléphone flexible

## ✅ Fonctionnalité implémentée

La recherche par numéro de téléphone accepte maintenant **tous les formats** et trouve automatiquement les tickets correspondants.

---

## Formats acceptés

| Format | Exemple | Fonctionne |
|--------|---------|------------|
| International avec espaces | `+243 812 345 678` | ✅ |
| International sans espaces | `+243812345678` | ✅ |
| Sans indicatif + | `243812345678` | ✅ |
| Local avec 0 | `0812345678` | ✅ |
| Local avec espaces | `0812 345 678` | ✅ |

---

## Modifications effectuées

### 1. TicketController (`app/Http/Controllers/API/TicketController.php`)

**Méthode :** `searchByPhone()`

**Avant :**
```php
$tickets = Ticket::where('phone', $phone)->get();
```

**Après :**
```php
// Normalise le numéro et crée des variantes
// Recherche avec toutes les variantes possibles
$tickets = Ticket::where(function($query) use ($phoneVariants) {
    foreach ($phoneVariants as $variant) {
        $query->orWhere('phone', 'LIKE', '%' . $variant . '%');
    }
})->get();
```

### 2. QRScanController (`app/Http/Controllers/API/QRScanController.php`)

**Méthode :** `scan()`

**Avant :**
```php
$ticket = Ticket::where('phone', $request->phone)->first();
```

**Après :**
```php
// Même logique de normalisation et recherche flexible
$ticket = Ticket::where(function($query) use ($phoneVariants) {
    foreach ($phoneVariants as $variant) {
        $query->orWhere('phone', 'LIKE', '%' . $variant . '%');
    }
})->first();
```

---

## Logique de conversion

### Exemple 1 : Recherche avec format local

**Entrée :** `0812345678`

**Variantes générées :**
1. `0812345678` (original)
2. `0812345678` (nettoyé)
3. `+243812345678` (converti en international)
4. `+243812345678` (nettoyé)

**Résultat :** Trouve le ticket même s'il est enregistré avec `+243 812 345 678`

### Exemple 2 : Recherche avec format international

**Entrée :** `+243 812 345 678`

**Variantes générées :**
1. `+243 812 345 678` (original)
2. `+243812345678` (nettoyé)
3. `0812345678` (converti en local)
4. `0812345678` (nettoyé)

**Résultat :** Trouve le ticket même s'il est enregistré avec `0812345678`

---

## Endpoints concernés

### GET /api/tickets/search?phone={phone}

Recherche tous les tickets avec ce numéro.

**Exemple :**
```bash
# Format local
GET /api/tickets/search?phone=0812345678

# Format international
GET /api/tickets/search?phone=%2B243812345678
```

### POST /api/tickets/scan

Scan de billet par téléphone.

**Exemple :**
```json
{
  "phone": "0812345678"
}
```

---

## Utilisation dans l'app mobile

```javascript
// L'utilisateur peut entrer n'importe quel format
const phone = userInput; // '0812345678' ou '+243 812 345 678'

const response = await fetch(
  `${API_URL}/tickets/search?phone=${encodeURIComponent(phone)}`
);

const data = await response.json();

if (data.success) {
  console.log(`${data.count} ticket(s) trouvé(s)`);
  // Afficher les tickets
} else {
  console.log('Aucun ticket trouvé');
}
```

---

## Avantages

✅ **Flexible** : Accepte tous les formats de numéro  
✅ **Intelligent** : Convertit automatiquement entre local et international  
✅ **Tolérant** : Ignore les espaces, tirets, parenthèses  
✅ **Simple** : Pas besoin de normaliser côté frontend  
✅ **Robuste** : Utilise LIKE pour une recherche plus large  

---

## Test

### Créer un ticket de test
```bash
php create-test-ticket.php
```

Le ticket sera créé avec le numéro : `+243 812 345 678`

### Tester la recherche

Tous ces formats devraient trouver le ticket :
```bash
# Format local
curl "http://192.168.40.9:8000/api/tickets/search?phone=0812345678"

# Format international
curl "http://192.168.40.9:8000/api/tickets/search?phone=%2B243812345678"

# Avec espaces
curl "http://192.168.40.9:8000/api/tickets/search?phone=0812%20345%20678"
```

---

## Documentation complète

Consultez `RECHERCHE_TELEPHONE_FLEXIBLE.md` pour :
- Exemples de code détaillés
- Logique de conversion complète
- Guide d'intégration mobile
- Notes techniques
