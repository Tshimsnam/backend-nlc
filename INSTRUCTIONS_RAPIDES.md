# Instructions rapides - Configuration de l'événement

## 🚀 Démarrage rapide (3 étapes)

### Étape 1: Placer l'image
```powershell
# Option A: Avec le script PowerShell
.\setup-event-image.ps1 -ImagePath "C:\chemin\vers\votre\image.jpg"

# Option B: Manuellement
# 1. Créer le dossier: public\galery
# 2. Copier votre image dans ce dossier
# 3. Renommer en: grand-salon-autisme-2026.jpg
```

### Étape 2: Appliquer les migrations
```bash
php artisan migrate
```

### Étape 3: Créer l'événement
```bash
php artisan db:seed --class=EventSeeder
```

## ✅ Vérification

### Vérifier l'image
```bash
php artisan serve
```
Ouvrir: http://localhost:8000/galery/grand-salon-autisme-2026.jpg

### Vérifier l'événement
```bash
php artisan tinker --execute="echo json_encode(App\Models\Event::with('event_prices')->first(), JSON_PRETTY_PRINT);"
```

## 🎯 Résultat attendu

Vous devriez voir:
- ✅ Titre: "Le Grand Salon de l'Autiste"
- ✅ Dates: 15-16 Avril 2026
- ✅ Lieu: Fleuve Congo Hôtel Kinshasa
- ✅ Contact: +243 844 338 747
- ✅ 10 sponsors
- ✅ 5 tarifs

## 🔧 En cas de problème

### L'image ne s'affiche pas
```bash
# Vérifier les permissions
chmod 755 public/galery
chmod 644 public/galery/*.jpg
```

### Erreur de migration
```bash
# Réinitialiser tout
php artisan migrate:fresh --seed
```

### Voir les logs
```bash
tail -f storage/logs/laravel.log
```

## 📱 Frontend

Le frontend affichera automatiquement toutes les nouvelles informations:
- Page de détail: http://localhost:5173/evenements/le-grand-salon-de-lautisme
- Page d'inscription: http://localhost:5173/evenements/le-grand-salon-de-lautisme/inscription

## 📚 Documentation complète

Pour plus de détails, consultez:
- `RESUME_MISE_A_JOUR_COMPLETE.md` - Vue d'ensemble complète
- `IMAGE_SETUP_GUIDE.md` - Guide détaillé pour l'image
- `EVENT_FIELDS_UPDATE.md` - Détails des champs backend
- `EVENTINSCRIPTION_V2_UPDATE.md` - Mise à jour du formulaire
- `EVENTDETAIL_UPDATE.md` - Mise à jour de la page de détail

---

**C'est tout!** 🎉

Votre événement est maintenant configuré avec toutes les informations de l'affiche.
