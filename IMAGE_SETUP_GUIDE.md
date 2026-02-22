# Guide de configuration de l'image de l'événement

## Image de couverture actuelle

L'image "Le Grand Salon de l'Autisme" que vous avez fournie doit être placée dans le dossier public du projet.

## Emplacement de l'image

### Option 1: Dossier public/galery (Recommandé)

```
backend-nlc/
├── public/
│   ├── galery/
│   │   └── grand-salon-autisme-2026.jpg  ← Placer l'image ici
```

**Chemin dans le seeder:**
```php
'image' => '/galery/grand-salon-autisme-2026.jpg',
```

### Option 2: Dossier storage/app/public

Si vous préférez utiliser le système de storage de Laravel:

```
backend-nlc/
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── events/
│   │   │   │   └── grand-salon-autisme-2026.jpg
```

**Commande à exécuter:**
```bash
php artisan storage:link
```

**Chemin dans le seeder:**
```php
'image' => '/storage/events/grand-salon-autisme-2026.jpg',
```

## Mise à jour du seeder

Le seeder actuel utilise:
```php
'image' => '/galery/NLC images15.jpg',
```

### Recommandation

Renommez l'image pour éviter les espaces dans le nom de fichier:
- ❌ `NLC images15.jpg` (espaces problématiques)
- ✅ `grand-salon-autisme-2026.jpg` (sans espaces)

## Instructions étape par étape

### 1. Créer le dossier galery

```bash
# Depuis la racine du projet backend-nlc
mkdir public/galery
```

### 2. Placer l'image

Copiez l'image de l'événement dans `public/galery/` et renommez-la:
```
public/galery/grand-salon-autisme-2026.jpg
```

### 3. Mettre à jour le seeder

Le seeder a déjà été mis à jour avec le bon chemin. Si vous voulez changer le nom:

```php
'image' => '/galery/grand-salon-autisme-2026.jpg',
```

### 4. Re-seeder la base de données

```bash
php artisan db:seed --class=EventSeeder
```

Ou pour tout réinitialiser:
```bash
php artisan migrate:fresh --seed
```

## Vérification

### Vérifier que l'image est accessible

1. Démarrez le serveur Laravel:
```bash
php artisan serve
```

2. Ouvrez dans le navigateur:
```
http://localhost:8000/galery/grand-salon-autisme-2026.jpg
```

Vous devriez voir l'image s'afficher.

### Vérifier dans l'application

1. Accédez à la page des événements
2. L'image devrait s'afficher sur la carte de l'événement
3. Sur la page de détail, l'image devrait être en grand format

## Structure recommandée pour les images

```
public/
├── galery/
│   ├── events/
│   │   ├── grand-salon-autisme-2026.jpg
│   │   ├── autre-evenement.jpg
│   ├── sponsors/
│   │   ├── agepe.png
│   │   ├── sofibanque.png
│   │   ├── vodacom.png
│   ├── logos/
│   │   └── nlc-logo.png
```

## Optimisation de l'image

Pour de meilleures performances, optimisez l'image:

### Dimensions recommandées
- **Largeur**: 1200px - 1920px
- **Hauteur**: 630px - 1080px (ratio 16:9 ou 1.91:1)
- **Format**: JPG ou WebP
- **Qualité**: 80-85%
- **Poids**: < 500KB

### Outils d'optimisation
- **En ligne**: TinyPNG, Squoosh
- **Ligne de commande**: ImageMagick

```bash
# Exemple avec ImageMagick
convert grand-salon-autisme-2026.jpg -resize 1920x1080 -quality 85 grand-salon-autisme-2026-optimized.jpg
```

## Problèmes courants

### L'image ne s'affiche pas

1. **Vérifier les permissions**:
```bash
chmod 755 public/galery
chmod 644 public/galery/*.jpg
```

2. **Vérifier le chemin**:
- Le chemin doit commencer par `/` (ex: `/galery/image.jpg`)
- Pas d'espaces dans le nom de fichier
- Extension en minuscules (.jpg, pas .JPG)

3. **Vérifier le .htaccess** (si Apache):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### L'image est trop lourde

Si l'image est trop lourde (> 1MB), elle peut ralentir le chargement:
1. Compressez-la avec TinyPNG
2. Réduisez les dimensions si nécessaire
3. Utilisez le format WebP pour une meilleure compression

## Frontend (React)

Le frontend affichera l'image avec:

```tsx
<img 
  src={`${API_URL}${event.image}`} 
  alt={event.title} 
/>
```

Où `API_URL` est configuré dans `.env`:
```
VITE_API_URL=http://localhost:8000
```

L'URL complète sera donc:
```
http://localhost:8000/galery/grand-salon-autisme-2026.jpg
```

## Checklist finale

- [ ] Dossier `public/galery` créé
- [ ] Image placée dans `public/galery/`
- [ ] Image renommée sans espaces
- [ ] Seeder mis à jour avec le bon chemin
- [ ] Base de données re-seedée
- [ ] Image accessible via navigateur
- [ ] Image s'affiche dans l'application frontend
- [ ] Image optimisée (< 500KB)
