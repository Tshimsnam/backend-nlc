# Guide de test depuis le Frontend

## 🎯 Objectif

Tester l'inscription à un événement depuis votre application frontend React/Vue.

## ✅ Prérequis

1. Backend Laravel en cours d'exécution sur `http://192.168.58.9:8000`
2. Frontend en cours d'exécution sur `http://192.168.58.9:8080`
3. Configuration `.env` corrigée (déjà fait ✅)

## 🚀 Étapes de test

### 1. Démarrer le backend

```bash
cd D:\choupole\Projects\Website\backend-nlc
php artisan serve --host=192.168.58.9 --port=8000
```

Vous devriez voir:
```
INFO  Server running on [http://192.168.58.9:8000].
```

### 2. Démarrer le frontend

```bash
cd D:\choupole\Projects\Website\frontend-nlc  # Ajustez le chemin
npm run dev
```

### 3. Accéder à la page d'inscription

Ouvrez votre navigateur et allez sur:
```
http://192.168.58.9:8080/evenements/1
```

Ou la page d'inscription de votre événement.

### 4. Remplir le formulaire

Utilisez ces données de test:

- **Nom complet**: Franck Kapuya
- **Email**: franckkapuya13@gmail.com
- **Téléphone**: +243822902681
- **Catégorie**: Étudiant (ou autre selon votre événement)
- **Mode de paiement**: Paiement en ligne

### 5. Soumettre le formulaire

Cliquez sur "S'inscrire" ou "Payer en ligne".

### 6. Vérifier la redirection

Vous devriez être redirigé vers une URL MaxiCash comme:
```
https://api-testbed.maxicashapp.com/payentryweb?logid=97761
```

### 7. Page MaxiCash

Sur la page MaxiCash, vous verrez:
- Le montant à payer (15.00 USD)
- Les options de paiement:
  - MaxiCash Wallet
  - Mobile Money (Airtel, Orange, Vodacom)
  - Carte bancaire (Visa, Mastercard)
  - PayPal

### 8. Effectuer le paiement (mode test)

En mode sandbox, vous pouvez:
- Utiliser des cartes de test
- Simuler un paiement Mobile Money
- Annuler le paiement pour tester le flux d'annulation

### 9. Après le paiement

Selon le résultat, vous serez redirigé vers:

**Succès**:
```
http://192.168.58.9:8080/paiement/success?reference=T5AECQ2T4W
```

**Échec**:
```
http://192.168.58.9:8080/paiement/failure?reference=T5AECQ2T4W
```

**Annulation**:
```
http://192.168.58.9:8080/paiement/cancel?reference=T5AECQ2T4W
```

## 🔍 Débogage

### Vérifier la requête dans le navigateur

Ouvrez les DevTools (F12) → Onglet Network → Filtrer par "register"

Vous devriez voir:
- **URL**: `http://192.168.58.9:8000/api/events/1/register`
- **Méthode**: POST
- **Status**: 201 Created
- **Réponse**:
```json
{
  "success": true,
  "payment_mode": "online",
  "reference": "T5AECQ2T4W",
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97761",
  "log_id": "97761",
  "message": "Redirection vers MaxiCash..."
}
```

### Vérifier les logs backend

Dans un autre terminal:
```bash
tail -f storage/logs/laravel.log
```

Vous devriez voir:
```
[INFO] MaxiCash PayEntryWeb request
[INFO] MaxiCash sandbox: payment initiated
```

### Erreurs courantes

#### Erreur 422 - Validation échouée

**Cause**: Données du formulaire invalides

**Solution**: Vérifiez que:
- `event_price_id` existe dans la base de données
- `full_name` a au moins 3 caractères
- `email` est un email valide
- `phone` a au moins 9 caractères
- `pay_type` est "online" ou "cash"

#### Erreur 500 - Erreur serveur

**Cause**: Problème de configuration ou de base de données

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
```

Vérifiez les logs: `storage/logs/laravel.log`

#### Pas de redirection vers MaxiCash

**Cause**: Le frontend ne gère pas la réponse correctement

**Solution**: Vérifiez votre code frontend:

```javascript
// Exemple React/Vue
const response = await fetch('http://192.168.58.9:8000/api/events/1/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(formData),
});

const data = await response.json();

if (data.success && data.redirect_url) {
  // Rediriger vers MaxiCash
  window.location.href = data.redirect_url;
} else {
  // Afficher l'erreur
  console.error(data.message);
}
```

## 📊 Vérifier les données en base

### Vérifier le ticket créé

```bash
php artisan tinker
```

```php
// Voir le dernier ticket créé
\App\Models\Ticket::latest()->first();

// Voir tous les tickets
\App\Models\Ticket::all();

// Chercher par référence
\App\Models\Ticket::where('reference', 'T5AECQ2T4W')->first();
```

### Vérifier les prix disponibles

```php
// Voir les prix de l'événement 1
\App\Models\EventPrice::where('event_id', 1)->get();
```

## 🧪 Test avec cURL (sans frontend)

Si vous voulez tester sans le frontend:

```bash
curl -X POST http://192.168.58.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{
    "event_price_id": 2,
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "days": 1,
    "pay_type": "online"
  }'
```

Ou utilisez le script PHP:
```bash
php test-api-inscription.php
```

## 📱 Test sur mobile

Pour tester depuis votre téléphone:

1. Assurez-vous que votre téléphone est sur le même réseau WiFi
2. Trouvez l'IP de votre ordinateur: `ipconfig` (Windows) ou `ifconfig` (Mac/Linux)
3. Accédez à `http://[VOTRE_IP]:8080` depuis votre téléphone
4. Suivez les mêmes étapes que ci-dessus

## ✅ Checklist de test

- [ ] Backend démarré sur `192.168.58.9:8000`
- [ ] Frontend démarré sur `192.168.58.9:8080`
- [ ] Page d'inscription accessible
- [ ] Formulaire rempli avec des données valides
- [ ] Soumission du formulaire réussie (HTTP 201)
- [ ] Redirection vers MaxiCash effectuée
- [ ] Page MaxiCash affichée correctement
- [ ] Montant correct affiché (15.00 USD)
- [ ] Options de paiement visibles
- [ ] Test de paiement réussi
- [ ] Redirection vers page de succès
- [ ] Référence du ticket affichée

## 🎉 Résultat attendu

Après avoir suivi toutes ces étapes, vous devriez avoir:

1. ✅ Un ticket créé dans la base de données
2. ✅ Une redirection vers MaxiCash
3. ✅ Une page de paiement MaxiCash fonctionnelle
4. ✅ Une redirection vers votre page de succès après paiement
5. ✅ La référence du ticket disponible pour téléchargement/affichage

## 📞 Support

Si vous rencontrez des problèmes:

1. **Vérifiez les logs**: `tail -f storage/logs/laravel.log`
2. **Testez l'API**: `php test-api-inscription.php`
3. **Vérifiez la config**: `php test-inscription-debug.php`
4. **Consultez la doc**: `PROBLEME_RESOLU.md`

---

**Bonne chance avec vos tests!** 🚀
