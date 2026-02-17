# Frontend - Intégration des Nouveaux Modes de Paiement

## Changements apportés à EventInscriptionPage.tsx

### 1. Endpoint API mis à jour

**Avant :**
```typescript
const modesRes = await axios.get(`${API_URL}/payment-modes`);
```

**Après :**
```typescript
const modesRes = await axios.get(`${API_URL}/events/${eventData.id}/tickets/payment-modes`);
```

L'endpoint est maintenant spécifique à chaque événement, permettant une configuration flexible des modes de paiement par événement.

---

## 2. Affichage des modes de paiement

### Structure des données reçues

```typescript
interface PaymentMode {
  id: string;              // 'cash', 'maxicash', 'mpesa', 'orange_money'
  label: string;           // Nom affiché
  description: string;     // Description du mode
  requires_phone: boolean; // Si un téléphone est requis
}
```

### Exemple de réponse API

```json
[
  {
    "id": "cash",
    "label": "Paiement en caisse",
    "description": "Générez votre QR code et payez directement à la caisse.",
    "requires_phone": false
  },
  {
    "id": "maxicash",
    "label": "MaxiCash",
    "description": "Payez via MaxiCash (Mobile Money, Carte bancaire, PayPal, etc.)",
    "requires_phone": false
  },
  {
    "id": "mpesa",
    "label": "M-Pesa",
    "description": "Payez via M-Pesa (Safaricom - Kenya)",
    "requires_phone": true
  },
  {
    "id": "orange_money",
    "label": "Orange Money",
    "description": "Payez via Orange Money",
    "requires_phone": true
  }
]
```

---

## 3. Améliorations visuelles

### Badge "Téléphone requis"

Pour les modes M-Pesa et Orange Money qui nécessitent un numéro de téléphone :

```tsx
{mode.requires_phone && (
  <div className="mt-3 flex items-center gap-2 text-xs text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg w-fit">
    <Phone className="w-3.5 h-3.5" />
    <span>Numéro de téléphone requis</span>
  </div>
)}
```

### Messages d'information contextuels

Selon le mode sélectionné, un message explicatif s'affiche :

```tsx
{formData.pay_type === 'cash' && (
  <p className="text-sm">
    💵 Vous recevrez un QR code à présenter à la caisse pour finaliser votre paiement.
  </p>
)}
{formData.pay_type === 'maxicash' && (
  <p className="text-sm">
    💳 Vous serez redirigé vers MaxiCash pour payer par Mobile Money, Carte bancaire ou PayPal.
  </p>
)}
{formData.pay_type === 'mpesa' && (
  <p className="text-sm">
    📱 Vous recevrez une notification M-Pesa sur votre téléphone pour confirmer le paiement.
  </p>
)}
{formData.pay_type === 'orange_money' && (
  <p className="text-sm">
    🍊 Vous serez redirigé vers Orange Money pour finaliser votre paiement.
  </p>
)}
```

---

## 4. Gestion des erreurs

Ajout d'un meilleur logging pour déboguer :

```typescript
try {
  const eventRes = await axios.get(`${API_URL}/events/${slug}`);
  const eventData = eventRes.data;
  setEvent(eventData);

  const modesRes = await axios.get(`${API_URL}/events/${eventData.id}/tickets/payment-modes`);
  setPaymentModes(modesRes.data);
} catch (err) {
  console.error("Erreur lors du chargement:", err);
  setError("Impossible de charger les données");
}
```

---

## 5. Flux utilisateur

### Étape 3 : Sélection du mode de paiement

1. L'utilisateur voit tous les modes disponibles
2. Chaque mode affiche :
   - Son nom
   - Sa description
   - Un badge si un téléphone est requis
3. Quand l'utilisateur sélectionne un mode :
   - Le mode est mis en surbrillance
   - Un message contextuel s'affiche
   - Un indicateur de sélection apparaît

### Étape 4 : Confirmation

L'utilisateur voit un récapitulatif incluant :
- Le mode de paiement sélectionné
- Sa description

### Étape 5 : Traitement

Selon le mode sélectionné :

**Cash :**
- Affichage du billet avec QR code
- Options d'impression et téléchargement

**MaxiCash / M-Pesa / Orange Money :**
- Redirection vers la plateforme de paiement
- L'URL de redirection est fournie par le backend

---

## 6. Variables d'environnement

Assurez-vous que votre fichier `.env` contient :

```env
VITE_API_URL=http://localhost:8000/api
```

---

## 7. Tests

### Test 1 : Vérifier que les modes s'affichent

1. Accédez à la page d'inscription d'un événement
2. Arrivez à l'étape 3 (Mode de paiement)
3. Vérifiez que les 4 modes s'affichent :
   - Paiement en caisse
   - MaxiCash
   - M-Pesa
   - Orange Money

### Test 2 : Vérifier les badges "Téléphone requis"

1. Vérifiez que M-Pesa et Orange Money affichent le badge
2. Vérifiez que Cash et MaxiCash n'affichent pas le badge

### Test 3 : Vérifier les messages contextuels

1. Sélectionnez chaque mode
2. Vérifiez qu'un message approprié s'affiche

### Test 4 : Tester le paiement en caisse

1. Sélectionnez "Paiement en caisse"
2. Complétez l'inscription
3. Vérifiez que le billet avec QR code s'affiche
4. Testez l'impression et le téléchargement

### Test 5 : Tester les paiements en ligne

1. Sélectionnez MaxiCash / M-Pesa / Orange Money
2. Complétez l'inscription
3. Vérifiez la redirection (en mode sandbox, cela devrait simuler)

---

## 8. Dépannage

### Les modes de paiement ne s'affichent pas

**Problème :** L'API ne retourne pas de données

**Solution :**
1. Vérifiez que le backend est démarré
2. Vérifiez l'URL de l'API dans `.env`
3. Ouvrez la console du navigateur pour voir les erreurs
4. Testez l'endpoint directement :
   ```bash
   curl http://localhost:8000/api/events/1/tickets/payment-modes
   ```

### Erreur CORS

**Problème :** Erreur "Access-Control-Allow-Origin"

**Solution :**
1. Vérifiez `config/cors.php` dans le backend
2. Assurez-vous que votre frontend est dans `allowed_origins`
3. Redémarrez le serveur Laravel

### Le mode sélectionné ne s'affiche pas dans la confirmation

**Problème :** `paymentModes.find()` retourne `undefined`

**Solution :**
1. Vérifiez que `paymentModes` est bien chargé
2. Ajoutez un fallback :
   ```typescript
   {paymentModes.find((m) => m.id === formData.pay_type)?.label || 'Non sélectionné'}
   ```

---

## 9. Prochaines étapes

### Améliorations possibles

1. **Validation du téléphone** : Vérifier le format selon le mode (M-Pesa, Orange Money)
2. **Icônes personnalisées** : Ajouter des logos pour chaque mode de paiement
3. **Historique** : Sauvegarder les modes préférés de l'utilisateur
4. **Multi-langue** : Traduire les messages en plusieurs langues
5. **Analytics** : Tracker quel mode est le plus utilisé

### Intégration mobile

Pour l'application mobile, utilisez la même API :

```typescript
// React Native
const response = await fetch(`${API_URL}/events/${eventId}/tickets/payment-modes`);
const modes = await response.json();
```

---

## 10. Ressources

- [Documentation API Backend](./API_DOCUMENTATION.md)
- [Guide des modes de paiement](./PAYMENT_GATEWAYS_GUIDE.md)
- [Nouveaux modes de paiement](./NOUVEAUX_MODES_PAIEMENT.md)
- [README Application Mobile](./README_APPLICATION_MOBILE.md)

---

## Support

Pour toute question ou problème :
1. Vérifiez les logs du navigateur (F12 > Console)
2. Vérifiez les logs Laravel (`storage/logs/laravel.log`)
3. Testez les endpoints API avec Postman ou curl
