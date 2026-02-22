# Script PowerShell pour configurer l'image de l'événement
# Usage: .\setup-event-image.ps1 -ImagePath "C:\chemin\vers\votre\image.jpg"

param(
    [Parameter(Mandatory=$false)]
    [string]$ImagePath = ""
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Configuration de l'image de l'événement" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier que nous sommes dans le bon dossier
if (-not (Test-Path "public")) {
    Write-Host "❌ Erreur: Le dossier 'public' n'existe pas." -ForegroundColor Red
    Write-Host "   Assurez-vous d'exécuter ce script depuis la racine du projet backend-nlc" -ForegroundColor Yellow
    exit 1
}

# Créer le dossier galery s'il n'existe pas
$galeryPath = "public\galery"
if (-not (Test-Path $galeryPath)) {
    Write-Host "📁 Création du dossier $galeryPath..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $galeryPath -Force | Out-Null
    Write-Host "✅ Dossier créé avec succès" -ForegroundColor Green
} else {
    Write-Host "✅ Le dossier $galeryPath existe déjà" -ForegroundColor Green
}

Write-Host ""

# Si un chemin d'image est fourni, copier l'image
if ($ImagePath -ne "") {
    if (Test-Path $ImagePath) {
        $destinationPath = "$galeryPath\grand-salon-autisme-2026.jpg"
        Write-Host "📋 Copie de l'image..." -ForegroundColor Yellow
        Write-Host "   Source: $ImagePath" -ForegroundColor Gray
        Write-Host "   Destination: $destinationPath" -ForegroundColor Gray
        
        Copy-Item -Path $ImagePath -Destination $destinationPath -Force
        
        if (Test-Path $destinationPath) {
            Write-Host "✅ Image copiée avec succès" -ForegroundColor Green
            
            # Afficher la taille du fichier
            $fileSize = (Get-Item $destinationPath).Length
            $fileSizeKB = [math]::Round($fileSize / 1KB, 2)
            $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
            
            Write-Host ""
            Write-Host "📊 Informations sur l'image:" -ForegroundColor Cyan
            Write-Host "   Taille: $fileSizeKB KB ($fileSizeMB MB)" -ForegroundColor Gray
            
            if ($fileSizeMB -gt 1) {
                Write-Host "   ⚠️  L'image est assez lourde (> 1MB). Considérez l'optimiser." -ForegroundColor Yellow
            } elseif ($fileSizeKB -gt 500) {
                Write-Host "   ⚠️  L'image est un peu lourde (> 500KB). L'optimisation est recommandée." -ForegroundColor Yellow
            } else {
                Write-Host "   ✅ Taille optimale" -ForegroundColor Green
            }
        } else {
            Write-Host "❌ Erreur lors de la copie de l'image" -ForegroundColor Red
            exit 1
        }
    } else {
        Write-Host "❌ Erreur: Le fichier $ImagePath n'existe pas" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "ℹ️  Aucun chemin d'image fourni" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Pour copier automatiquement l'image, utilisez:" -ForegroundColor Cyan
    Write-Host '   .\setup-event-image.ps1 -ImagePath "C:\chemin\vers\votre\image.jpg"' -ForegroundColor Gray
    Write-Host ""
    Write-Host "Ou copiez manuellement votre image dans:" -ForegroundColor Cyan
    Write-Host "   $galeryPath\grand-salon-autisme-2026.jpg" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Prochaines étapes:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si l'image existe
$imagePath = "$galeryPath\grand-salon-autisme-2026.jpg"
if (Test-Path $imagePath) {
    Write-Host "✅ 1. Image placée dans le bon dossier" -ForegroundColor Green
    Write-Host "✅ 2. Le seeder est déjà configuré" -ForegroundColor Green
    Write-Host ""
    Write-Host "📝 Exécutez maintenant:" -ForegroundColor Yellow
    Write-Host "   php artisan db:seed --class=EventSeeder" -ForegroundColor White
    Write-Host ""
    Write-Host "Ou pour tout réinitialiser:" -ForegroundColor Yellow
    Write-Host "   php artisan migrate:fresh --seed" -ForegroundColor White
    Write-Host ""
    Write-Host "🌐 Pour vérifier que l'image est accessible:" -ForegroundColor Yellow
    Write-Host "   1. Démarrez le serveur: php artisan serve" -ForegroundColor White
    Write-Host "   2. Ouvrez: http://localhost:8000/galery/grand-salon-autisme-2026.jpg" -ForegroundColor White
} else {
    Write-Host "⏳ 1. Placez votre image dans:" -ForegroundColor Yellow
    Write-Host "      $imagePath" -ForegroundColor White
    Write-Host "✅ 2. Le seeder est déjà configuré" -ForegroundColor Green
    Write-Host "⏳ 3. Exécutez: php artisan db:seed --class=EventSeeder" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Configuration terminée!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
