# 🎨 Guide Rapide - Landing Page

## 🚀 Accès Rapide

### URL
```
http://localhost:8000/
```

### Bouton d'Inscription
Redirige vers:
```
{FRONTEND_URL}/evenements
```

---

## ✅ Vérification

### 1. Vérifier la Configuration
```bash
# Vérifier le .env
cat .env | grep FRONTEND_WEBSITE_URL
```

Doit afficher:
```
FRONTEND_WEBSITE_URL=http://localhost:8080
```

### 2. Démarrer le Serveur
```bash
php artisan serve
```

### 3. Accéder à la Page
```
http://localhost:8000/
```

### 4. Tester le Bouton
- Cliquer sur "Je m'inscris"
- Doit rediriger vers: `http://localhost:8080/evenements`

---

## 🎨 Ce Que Vous Verrez

### Hero Section
```
┌─────────────────────────────────────────┐
│  [NLC Logo]                             │
│                                         │
│         Le                              │
│      Grand                              │
│    Salon de                             │
│   L'AUTISME                             │
│                                         │
│  ┌───────────────────────────────────┐ │
│  │  15 › 16 Avril 2026               │ │
│  │  08H - 16H                        │ │
│  │                                   │ │
│  │  📍 Fleuve Congo Hôtel           │ │
│  │  📞 +243 844 338 747             │ │
│  │  📧 info@nlcrdc.org              │ │
│  │                                   │ │
│  │  [Je m'inscris →]                │ │
│  └───────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### Programme
```
┌──────────────────┐  ┌──────────────────┐
│ Jour 1           │  │ Jour 2           │
│ 15 Avril 2026    │  │ 16 Avril 2026    │
│                  │  │                  │
│ ✓ Conférences    │  │ ✓ Ateliers       │
│ ✓ Ateliers       │  │ ✓ Études de cas  │
│ ✓ Networking     │  │ ✓ Table ronde    │
└──────────────────┘  └──────────────────┘
```

### Sponsors
```
[AGEPE] [SOFIBANQUE] [TIJE] [Vodacom] [Ecobank]
+ 5 autres partenaires
```

---

## 🔧 Personnalisation

### Changer l'URL de Redirection

**Fichier**: `.env`
```env
FRONTEND_WEBSITE_URL=https://votre-frontend.com
```

### Changer le Texte

**Fichier**: `resources/views/welcome.blade.php`

Chercher et modifier:
```html
<!-- Titre -->
<h1>Votre Nouveau Titre</h1>

<!-- Description -->
<p>Votre nouvelle description</p>

<!-- Dates -->
<div>15 › 16</div>
<div>Avril</div>
<div>2026</div>
```

### Changer les Couleurs

**Fichier**: `resources/views/welcome.blade.php`

Dans la section `<style>`:
```css
.hero-pattern {
    background-color: #VOTRE_COULEUR;
}
```

---

## 📱 Responsive

### Desktop
- Titre en très grand (8xl)
- 2 colonnes pour date/lieu
- 2 colonnes pour le programme
- 5 colonnes pour les sponsors

### Mobile
- Titre en grand (6xl)
- 1 colonne pour tout
- Padding réduit
- Bouton pleine largeur

---

## 🎯 Éléments Clés

### Logo NLC
- Cercle avec dégradé bleu-purple
- Texte "NLC" en blanc
- Sous-titre "Never Limit Children"

### Titre Principal
- Très grand et bold
- "L'AUTISME" en jaune
- Animation slide-in

### Carte Date
- Fond jaune dégradé
- Texte bleu foncé
- Format: JJ › JJ Mois AAAA

### Bouton CTA
- Dégradé bleu-purple
- Icônes de billet et flèche
- Animation scale au survol
- Texte: "Je m'inscris"

---

## 🔍 Dépannage

### Problème: Page Blanche

**Solution**:
```bash
# Vider le cache
php artisan view:clear
php artisan cache:clear

# Redémarrer le serveur
php artisan serve
```

### Problème: Styles Non Appliqués

**Cause**: Tailwind CSS CDN non chargé

**Solution**:
- Vérifier la connexion internet
- Ouvrir la console du navigateur (F12)
- Vérifier les erreurs

### Problème: Redirection Ne Fonctionne Pas

**Cause**: Variable d'environnement incorrecte

**Solution**:
```bash
# Vérifier le .env
cat .env | grep FRONTEND_WEBSITE_URL

# Modifier si nécessaire
nano .env

# Vider le cache
php artisan config:clear
```

### Problème: Animations Ne Fonctionnent Pas

**Cause**: JavaScript désactivé ou CSS non chargé

**Solution**:
- Activer JavaScript dans le navigateur
- Rafraîchir la page (Ctrl+F5)
- Vérifier la console

---

## 📊 Contenu Affiché

### Informations Principales
- **Titre**: Le Grand Salon de l'Autisme
- **Dates**: 15-16 Avril 2026
- **Horaires**: 08H - 16H
- **Lieu**: Fleuve Congo Hôtel Kinshasa
- **Contact**: +243 844 338 747 / info@nlcrdc.org
- **Date limite**: 10 Avril 2026

### Programme
- **Jour 1**: Conférences, ateliers, networking
- **Jour 2**: Ateliers spécialisés, études de cas

### Sponsors
- 5 sponsors principaux affichés
- Mention de 5 autres partenaires

---

## 🎨 Design

### Couleurs
- **Bleu foncé**: Fond hero
- **Jaune**: Accents et date
- **Blanc**: Cartes et texte
- **Dégradés**: Boutons et éléments

### Animations
- **Slide-in**: Entrée progressive
- **Pulse**: Éléments décoratifs
- **Scale**: Bouton au survol

### Typographie
- **Police**: Poppins (Google Fonts)
- **Tailles**: Responsive
- **Poids**: 300 à 800

---

## 🌐 URLs

### Développement
```
Page: http://localhost:8000/
Redirection: http://localhost:8080/evenements
```

### Production
```
Page: https://votre-domaine.com/
Redirection: https://frontend.com/evenements
```

---

## 📝 Maintenance

### Mise à Jour du Contenu

1. **Ouvrir le fichier**:
   ```bash
   nano resources/views/welcome.blade.php
   ```

2. **Modifier le contenu**:
   - Chercher le texte à modifier
   - Remplacer par le nouveau texte
   - Sauvegarder (Ctrl+O, Enter, Ctrl+X)

3. **Rafraîchir la page**:
   - Ouvrir le navigateur
   - Rafraîchir (F5 ou Ctrl+F5)

### Mise à Jour des Styles

1. **Modifier les classes Tailwind**:
   ```html
   <!-- Avant -->
   <div class="bg-blue-600">
   
   <!-- Après -->
   <div class="bg-red-600">
   ```

2. **Ou ajouter du CSS personnalisé**:
   ```css
   <style>
   .ma-classe {
       color: red;
   }
   </style>
   ```

---

## ✅ Checklist

- [ ] Serveur démarré (`php artisan serve`)
- [ ] Page accessible (`http://localhost:8000/`)
- [ ] Design affiché correctement
- [ ] Animations fonctionnelles
- [ ] Bouton "Je m'inscris" visible
- [ ] Redirection fonctionne
- [ ] Responsive sur mobile
- [ ] Toutes les informations affichées

---

## 🎯 Prochaines Actions

1. **Tester la Page**:
   ```bash
   php artisan serve
   # Ouvrir http://localhost:8000/
   ```

2. **Vérifier la Redirection**:
   - Cliquer sur "Je m'inscris"
   - Vérifier l'URL de destination

3. **Tester sur Mobile**:
   - Ouvrir les DevTools (F12)
   - Mode responsive
   - Tester différentes tailles

4. **Personnaliser si Nécessaire**:
   - Modifier le texte
   - Changer les couleurs
   - Ajuster les styles

---

## 📞 Support

### Documentation
- **LANDING_PAGE_FEATURE.md** - Documentation technique complète

### Fichiers
- **Vue**: `resources/views/welcome.blade.php`
- **Route**: `routes/web.php`
- **Config**: `.env`

### Logs
- Laravel: `storage/logs/laravel.log`
- Navigateur: Console (F12)

---

**Astuce**: Pour un chargement plus rapide, les styles et scripts sont inline dans la page!

---

**Date**: 21 Février 2026  
**Version**: 1.0.0  
**Status**: ✅ Prêt à l'Emploi
