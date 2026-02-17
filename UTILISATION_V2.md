# Utilisation du nouveau fichier EventInscriptionPage-v2.tsx

## ✅ Fichier créé avec succès !

Le fichier `EventInscriptionPage-v2.tsx` contient le flux simplifié sans l'étape de sélection du mode de paiement.

---

## 🎯 Nouveau flux (4 étapes)

1. **Étape 1** : Choisir le tarif
2. **Étape 2** : Remplir les informations personnelles
3. **Étape 3** : Confirmer les informations
4. **Étape 4** : Voir la référence + 2 cartes de paiement (M-Pesa et Orange Money) + Billet avec QR code

---

## 📦 Installation

### Option 1 : Remplacer le fichier actuel

```bash
# Sauvegarder l'ancien fichier
mv EventInscriptionPage.tsx EventInscriptionPage-old.tsx

# Renommer le nouveau fichier
mv EventInscriptionPage-v2.tsx EventInscriptionPage.tsx
```

### Option 2 : Tester d'abord

Gardez les deux fichiers et modifiez vos routes pour pointer vers la v2 :

```typescript
// Dans votre fichier de routes
import EventInscriptionPage from './EventInscriptionPage-v2';
```

---

## 🎨 Caractéristiques

### Étape 4 : Affichage des instructions

Après la confirmation, l'utilisateur voit :

1. **Sa référence** en gros (ex: `ABC123XYZ`)
2. **2 cartes côte à côte** :
   - **Carte M-Pesa** (verte) avec les 5 étapes :
     - Composez `*1122#`
     - Choisissez `5 - Mes paiements`
     - Entrez `097435`
     - Entrez le montant
     - Validez avec PIN
   - **Carte Orange Money** (orange) avec les 5 étapes :
     - Composez `#144#`
     - Sélectionnez `Paiement marchand`
     - Entrez le numéro marchand `[À VENIR]`
     - Entrez le montant
     - Validez avec PIN

3. **Billet avec QR code** (comme avant)
4. **Boutons** : Imprimer et Télécharger

---

## 🔧 Personnalisation

### Modifier le numéro M-Pesa

Ligne ~1050 :
```typescript
<p className="text-2xl md:text-3xl font-bold text-green-600 font-mono">097435</p>
```

### Modifier le numéro Orange Money

Ligne ~1120 :
```typescript
<p className="text-lg md:text-xl font-bold text-orange-600 font-mono">[À VENIR]</p>
```

Remplacez par votre numéro marchand Orange Money.

### Ajouter d'autres modes de paiement

Pour ajouter Airtel Money par exemple, dupliquez une des cartes et modifiez :
- Les couleurs (ex: `from-red-50 to-red-100`)
- Le titre
- Les instructions

---

## 🧪 Tests

### Test 1 : Flux complet

1. Accédez à la page d'inscription
2. Choisissez un tarif
3. Remplissez vos informations
4. Confirmez
5. Vérifiez que vous voyez :
   - Votre référence
   - Les 2 cartes M-Pesa et Orange Money
   - Le billet avec QR code

### Test 2 : Impression

1. Cliquez sur "Imprimer le billet"
2. Vérifiez que seul le billet s'imprime (pas les cartes de paiement)

### Test 3 : Téléchargement PDF

1. Cliquez sur "Télécharger le Billet"
2. Vérifiez que le PDF contient le billet

### Test 4 : Responsive

1. Testez sur mobile
2. Vérifiez que les 2 cartes s'empilent verticalement
3. Vérifiez que tout est lisible

---

## 🐛 Dépannage

### Les cartes ne s'affichent pas côte à côte

**Problème** : Les cartes sont empilées même sur desktop

**Solution** : Vérifiez que Tailwind CSS est bien configuré et que la classe `md:grid-cols-2` fonctionne.

### Le QR code ne se génère pas

**Problème** : Le QR code est vide

**Solution** : Vérifiez que `qrData` contient bien les données et que `qrcode.react` est installé :
```bash
npm install qrcode.react
```

### Les couleurs ne s'affichent pas

**Problème** : Les cartes sont grises

**Solution** : Vérifiez votre configuration Tailwind pour les couleurs `green` et `orange`.

---

## 📱 Responsive

Le design est entièrement responsive :

- **Mobile** : Les cartes s'empilent verticalement
- **Tablet** : Les cartes commencent à se mettre côte à côte
- **Desktop** : Les cartes sont côte à côte avec un bel espacement

---

## 🎨 Personnalisation avancée

### Changer les couleurs des cartes

M-Pesa (vert) :
```typescript
className="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300"
```

Orange Money (orange) :
```typescript
className="bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-300"
```

### Ajouter des animations

Les cartes ont déjà des animations au survol :
```typescript
className="... hover:shadow-2xl transition-shadow"
```

Vous pouvez ajouter plus d'animations avec Framer Motion.

---

## 📊 Comparaison avec l'ancienne version

| Fonctionnalité | Ancienne version | Nouvelle version (v2) |
|----------------|------------------|----------------------|
| Nombre d'étapes | 5 | 4 |
| Sélection mode paiement | Oui (étape 3) | Non |
| Instructions M-Pesa | Conditionnelles | Toujours affichées |
| Instructions Orange Money | Conditionnelles | Toujours affichées |
| Cartes de paiement | 1 à la fois | 2 côte à côte |
| Complexité du code | Élevée | Simplifiée |

---

## 🚀 Prochaines étapes

1. **Tester** le nouveau fichier
2. **Personnaliser** les numéros de paiement
3. **Ajouter** d'autres modes si nécessaire (Airtel Money, etc.)
4. **Déployer** en production

---

## 💡 Conseils

- Gardez l'ancien fichier en backup pendant quelques jours
- Testez sur différents navigateurs
- Demandez des retours utilisateurs
- Ajustez les instructions selon vos besoins

---

## 📞 Support

Si vous avez des questions ou des problèmes :
1. Vérifiez que toutes les dépendances sont installées
2. Consultez les logs de la console
3. Testez d'abord en local avant de déployer

Bon courage ! 🎉
