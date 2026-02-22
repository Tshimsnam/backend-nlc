# Script PowerShell pour tester l'API de notification email
# Usage: .\test-notification-api.ps1

Write-Host "=== Test API Notification Email ===" -ForegroundColor Cyan
Write-Host ""

# Configuration
$baseUrl = "http://localhost:8000"
$ticketReference = "TKT-1771703593-H4WITL"

# Vérifier si le serveur est accessible
Write-Host "1. Vérification du serveur Laravel..." -ForegroundColor Yellow
try {
    $testResponse = Invoke-WebRequest -Uri "$baseUrl/api/test" -Method GET -ErrorAction Stop
    Write-Host "   ✓ Serveur accessible" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Serveur non accessible" -ForegroundColor Red
    Write-Host "   Démarrez le serveur avec: php artisan serve" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Demander la référence du ticket
Write-Host "2. Référence du ticket" -ForegroundColor Yellow
$inputReference = Read-Host "   Entrez la référence du ticket (ou appuyez sur Entrée pour utiliser: $ticketReference)"
if ($inputReference) {
    $ticketReference = $inputReference
}
Write-Host "   Utilisation de: $ticketReference" -ForegroundColor Cyan

Write-Host ""

# Vérifier que le ticket existe
Write-Host "3. Vérification du ticket..." -ForegroundColor Yellow
try {
    $ticketResponse = Invoke-WebRequest -Uri "$baseUrl/api/tickets/$ticketReference" -Method GET -ErrorAction Stop
    $ticket = $ticketResponse.Content | ConvertFrom-Json
    
    Write-Host "   ✓ Ticket trouvé:" -ForegroundColor Green
    Write-Host "     - Référence: $($ticket.reference)" -ForegroundColor White
    Write-Host "     - Participant: $($ticket.full_name)" -ForegroundColor White
    Write-Host "     - Email: $($ticket.email)" -ForegroundColor White
    Write-Host "     - Événement: $($ticket.event.title)" -ForegroundColor White
    Write-Host "     - Montant: $($ticket.amount) $($ticket.currency)" -ForegroundColor White
    Write-Host "     - Statut: $($ticket.payment_status)" -ForegroundColor White
    
    if (-not $ticket.email) {
        Write-Host "   ✗ Ce ticket n'a pas d'email" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "   ✗ Ticket non trouvé" -ForegroundColor Red
    Write-Host "   Erreur: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Demander confirmation
Write-Host "4. Confirmation" -ForegroundColor Yellow
$confirmation = Read-Host "   Envoyer l'email à $($ticket.email)? (y/n)"
if ($confirmation -ne "y") {
    Write-Host "   ✗ Envoi annulé" -ForegroundColor Red
    exit 0
}

Write-Host ""

# Envoyer la notification
Write-Host "5. Envoi de la notification..." -ForegroundColor Yellow
try {
    $notificationResponse = Invoke-WebRequest `
        -Uri "$baseUrl/api/tickets/$ticketReference/send-notification" `
        -Method POST `
        -ContentType "application/json" `
        -ErrorAction Stop
    
    $result = $notificationResponse.Content | ConvertFrom-Json
    
    if ($result.success) {
        Write-Host "   ✓ Email envoyé avec succès!" -ForegroundColor Green
        Write-Host ""
        Write-Host "   Message: $($result.message)" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   Vérifiez la boîte de réception de: $($result.ticket.email)" -ForegroundColor Yellow
        Write-Host "   (N'oubliez pas de vérifier le dossier spam)" -ForegroundColor Yellow
    } else {
        Write-Host "   ✗ Erreur: $($result.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "   ✗ Erreur lors de l'envoi" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        $errorData = $responseBody | ConvertFrom-Json
        Write-Host "   Message: $($errorData.message)" -ForegroundColor Red
    } else {
        Write-Host "   Erreur: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "   Vérifiez:" -ForegroundColor Yellow
    Write-Host "   1. La configuration SMTP dans .env" -ForegroundColor White
    Write-Host "   2. Les logs: storage/logs/laravel.log" -ForegroundColor White
    Write-Host "   3. Que le serveur SMTP est accessible" -ForegroundColor White
    exit 1
}

Write-Host ""
Write-Host "=== Test terminé ===" -ForegroundColor Cyan
