<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Billet - {{ $event->title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .ticket-info h2 {
            margin: 0 0 15px;
            color: #667eea;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .qr-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            margin: 20px 0;
            border-radius: 10px;
        }
        .qr-section h3 {
            margin: 0 0 15px;
            color: #333;
        }
        .qr-code {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .reference {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .important-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .important-note strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="https://www.nlcrdc.org/wp-content/uploads/2023/02/LogoWeb2-1.png" alt="Never Limit Children" style="height: 50px; max-width: 200px; margin-bottom: 15px;">
            <h1>🎫 Votre Billet</h1>
            <p>{{ $event->title }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Bonjour <strong>{{ $ticket->full_name }}</strong>,</p>
            
            <p>Merci pour votre inscription ! Voici les détails de votre billet :</p>

            <!-- Ticket Info -->
            <div class="ticket-info">
                <h2>📋 Informations du Billet</h2>
                
                <div class="info-row">
                    <span class="info-label">Référence :</span>
                    <span class="info-value reference">{{ $ticket->reference }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Participant :</span>
                    <span class="info-value">{{ $ticket->full_name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email :</span>
                    <span class="info-value">{{ $ticket->email }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Téléphone :</span>
                    <span class="info-value">{{ $ticket->phone }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Catégorie :</span>
                    <span class="info-value">{{ $price->label ?? $ticket->category }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Montant :</span>
                    <span class="info-value">{{ number_format($ticket->amount, 2) }} {{ $ticket->currency }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Statut :</span>
                    <span class="info-value">
                        @if($ticket->payment_status === 'completed')
                            <span class="status-badge status-completed">✅ Payé</span>
                        @else
                            <span class="status-badge status-pending">⏳ En attente</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Event Info -->
            <div class="ticket-info">
                <h2>🎪 Détails de l'Événement</h2>
                
                <div class="info-row">
                    <span class="info-label">Événement :</span>
                    <span class="info-value">{{ $event->title }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Date :</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                        @if($event->end_date && $event->end_date !== $event->date)
                            - {{ \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') }}
                        @endif
                    </span>
                </div>

                @if($event->time)
                <div class="info-row">
                    <span class="info-label">Horaire :</span>
                    <span class="info-value">
                        {{ $event->time }}
                        @if($event->end_time)
                            - {{ $event->end_time }}
                        @endif
                    </span>
                </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Lieu :</span>
                    <span class="info-value">{{ $event->location }}</span>
                </div>

                @if($event->venue_details)
                <div class="info-row">
                    <span class="info-label">Détails :</span>
                    <span class="info-value">{{ $event->venue_details }}</span>
                </div>
                @endif
            </div>

            <!-- QR Code Section -->
            <div class="qr-section">
                <h3>📱 Votre QR Code</h3>
                <p>Présentez ce QR code à l'entrée de l'événement</p>
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($ticket->qr_data) }}" alt="QR Code" style="width: 200px; height: 200px;">
                </div>
                <div class="reference">{{ $ticket->reference }}</div>
            </div>

            @if($ticket->payment_status === 'pending_cash')
            <!-- Important Note for Cash Payment -->
            <div class="important-note">
                <strong>⚠️ Important :</strong> Votre billet est en attente de paiement. Veuillez vous présenter à la caisse avec ce QR code pour finaliser votre paiement.
            </div>
            @endif

            <!-- Contact Info -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h3 style="margin: 0 0 15px; color: #333;">📞 Besoin d'aide ?</h3>
                <p style="margin: 5px 0;">
                    <strong>Email :</strong> <a href="mailto:{{ $event->contact_email ?? 'info@nlcrdc.org' }}">{{ $event->contact_email ?? 'info@nlcrdc.org' }}</a>
                </p>
                @if($event->contact_phone)
                <p style="margin: 5px 0;">
                    <strong>Téléphone :</strong> <a href="tel:{{ $event->contact_phone }}">{{ $event->contact_phone }}</a>
                </p>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Never Limit Children (NLC)</strong></p>
            <p>Ensemble pour l'inclusion</p>
            <p style="margin-top: 15px;">
                <a href="mailto:info@nlcrdc.org">info@nlcrdc.org</a>
            </p>
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre directement.
            </p>
        </div>
    </div>
</body>
</html>
