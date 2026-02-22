# QR Code : Local vs Production

## 🔍 Pourquoi ça ne compte pas en local ?

### Le Problème
Quand tu scannes le QR code généré avec `qr-code-generator.html`, il pointe vers :
```
https://www.nlcrdc.org/evenements/le-grand-salon-de-lautisme
```

Mais ton backend local est sur :
```
http://192.168.171.9:8000
```

Donc le scan va vers la **production**, pas vers ton **environnement local**.

## 📂 Deux Fichiers, Deux Usages

### 1. `qr-code-generator.html` - PRODUCTION ✅
**Utiliser pour :**
- Générer le QR code final pour la production
- Imprimer sur des affiches
- Partager sur les réseaux sociaux
- Distribution au public

**URL :**
```
https://www.nlcrdc.org/evenements/le-grand-salon-de-lautisme
```

### 2. `qr-code-generator-local.html` - DÉVELOPPEMENT 🔧
**Utiliser pour :**
- Tester le système de tracking en local
- Développement et débogage
- Vérifier que les scans sont bien enregistrés

**URL :**
```
http://192.168.171.9:3000/evenements/le-grand-salon-de-lautisme
```

## 🧪 Comment Tester en Local

### Étape 1 : Préparer l'Environnement
```bash
# Backend
php artisan serve --host=0.0.0.0 --port=8000

# Frontend (dans un autre terminal)
npm run dev -- --host
```

### Étape 2 : Générer le QR Code Local
1. Ouvrir `qr-code-generator-local.html`
2. Vérifier/modifier l'URL selon votre config
3. Cliquer sur "Régénérer QR"
4. Télécharger le QR code

### Étape 3 : Scanner avec le Téléphone
⚠️ **Important** : Votre téléphone doit être sur le **même réseau WiFi** que votre PC

1. Scanner le QR code avec votre téléphone
2. Vous serez redirigé vers votre frontend local
3. Le scan sera enregistré dans votre base de données locale

### Étape 4 : Vérifier le Dashboard
1. Aller sur `http://192.168.171.9:8000/admin`
2. Regarder la carte "QR Scans"
3. Le compteur devrait avoir augmenté ! 🎉

## 🔄 Flux Complet

### En Local (Développement)
```
QR Code Local
    ↓
http://192.168.171.9:3000/evenements/...
    ↓
Frontend Local charge
    ↓
Appel API: POST http://192.168.171.9:8000/api/events/{slug}/scan
    ↓
Backend Local enregistre le scan
    ↓
Dashboard Local affiche +1 scan
```

### En Production
```
QR Code Production
    ↓
https://www.nlcrdc.org/evenements/...
    ↓
Frontend Production charge
    ↓
Appel API: POST https://api.nlcrdc.org/api/events/{slug}/scan
    ↓
Backend Production enregistre le scan
    ↓
Dashboard Production affiche +1 scan
```

## 📱 Configuration du Frontend

Pour que le tracking fonctionne, ajoutez dans `EventDetailPage.tsx` :

```tsx
import { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

const EventDetailPage = () => {
  const { slug } = useParams();
  const API_URL = import.meta.env.VITE_API_URL;

  useEffect(() => {
    // Enregistrer le scan quand la page charge
    const recordScan = async () => {
      try {
        await axios.post(`${API_URL}/events/${slug}/scan`);
        console.log('✅ Scan enregistré');
      } catch (error) {
        console.error('❌ Erreur scan:', error);
      }
    };

    recordScan();
  }, [slug]);

  // ... reste du composant
};
```

## 🔧 Variables d'Environnement

### Local (.env.local)
```env
VITE_API_URL=http://192.168.171.9:8000/api
```

### Production (.env.production)
```env
VITE_API_URL=https://api.nlcrdc.org/api
```

## 🧪 Test Rapide

### Tester avec cURL
```bash
# Local
curl -X POST http://192.168.171.9:8000/api/events/le-grand-salon-de-lautisme/scan

# Production
curl -X POST https://api.nlcrdc.org/api/events/le-grand-salon-de-lautisme/scan
```

### Vérifier les Scans
```bash
# Local
curl http://192.168.171.9:8000/api/events/le-grand-salon-de-lautisme/scans

# Production
curl https://api.nlcrdc.org/api/events/le-grand-salon-de-lautisme/scans
```

## 📊 Résumé

| Aspect | Local | Production |
|--------|-------|------------|
| **Fichier QR** | qr-code-generator-local.html | qr-code-generator.html |
| **URL Frontend** | http://192.168.171.9:3000 | https://www.nlcrdc.org |
| **URL Backend** | http://192.168.171.9:8000 | https://api.nlcrdc.org |
| **Base de données** | MySQL local | MySQL production |
| **Réseau requis** | Même WiFi | Internet |
| **Usage** | Tests/Développement | Public/Production |

## ✅ Checklist de Test

- [ ] Backend local tourne sur port 8000
- [ ] Frontend local tourne sur port 3000
- [ ] Téléphone sur le même WiFi
- [ ] QR code local généré avec la bonne URL
- [ ] EventDetailPage.tsx a le code de tracking
- [ ] Variable VITE_API_URL correcte
- [ ] Scanner le QR avec le téléphone
- [ ] Vérifier le dashboard admin
- [ ] Compteur "QR Scans" a augmenté

## 🚀 Déploiement en Production

Quand vous déployez en production :
1. ✅ Utiliser `qr-code-generator.html` (URL production)
2. ✅ Vérifier que VITE_API_URL pointe vers l'API production
3. ✅ Tester un scan en production
4. ✅ Vérifier le dashboard production

---

**Astuce** : Gardez les deux fichiers HTML, ils sont utiles pour différents environnements !
