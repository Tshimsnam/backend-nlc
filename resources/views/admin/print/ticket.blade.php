<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billet — {{ $ticket->reference }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #1e1b4b; font-family: system-ui, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .rp-gradient  { background: linear-gradient(135deg, #6d28d9 0%, #a855f7 50%, #ec4899 100%); }
        .rp-grad-dark { background: linear-gradient(135deg, #3b0764 0%, #6d28d9 100%); }
        .rp-grad-soft { background: linear-gradient(135deg, #f5f3ff 0%, #fdf4ff 100%); }
        .rp-section-title {
            font-size: .7rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
            color: #7c3aed; border-left: 4px solid #ec4899; padding-left: .6rem; margin-bottom: 1rem;
        }

        .ticket-page {
            width: 794px;
            min-height: 1123px;
            background: white;
            margin: 0 auto 24px;
            padding: 48px;
            position: relative;
        }

        #btn-print {
            position: fixed; bottom: 32px; right: 32px; z-index: 999;
            background: linear-gradient(135deg, #6d28d9, #ec4899);
            color: white; border: none; border-radius: 50px;
            padding: 14px 28px; font-size: 15px; font-weight: 700;
            cursor: pointer; box-shadow: 0 8px 32px rgba(109,40,217,.5);
            display: flex; align-items: center; gap: 10px;
        }
        #btn-back {
            position: fixed; bottom: 32px; left: 32px; z-index: 999;
            background: white; color: #6d28d9; border: 2px solid #6d28d9;
            border-radius: 50px; padding: 14px 28px; font-size: 15px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; gap: 10px;
        }

        @media print {
            body { background: white; }
            #btn-print, #btn-back { display: none !important; }
            .ticket-page { margin: 0; box-shadow: none; }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<button id="btn-back" onclick="window.history.back()">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour
</button>

<button id="btn-print" onclick="window.print()">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Imprimer
</button>

<div class="ticket-page">

    {{-- ── Header ── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid #f3f4f6;">
        <div style="display:flex; align-items:center; gap:16px;">
            <img src="{{ asset('logo-nlc-blanc.png') }}" alt="NLC"
                 style="height:48px; width:auto; object-fit:contain; filter: invert(1) sepia(1) saturate(5) hue-rotate(220deg);"
                 onerror="this.style.display='none'">
            <div>
                <p style="font-size:9px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#a855f7; margin-bottom:2px;">NLC Events</p>
                <p style="font-size:18px; font-weight:900; color:#1e1b4b;">Billet d'entrée</p>
            </div>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#9ca3af;">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <span style="display:inline-block; padding:4px 12px; border-radius:9999px; font-size:11px; font-weight:700;
                {{ $ticket->payment_status === 'completed' ? 'background:#d1fae5; color:#065f46;' : 'background:#fef3c7; color:#92400e;' }}">
                {{ $ticket->payment_status === 'completed' ? '✓ PAYÉ' : '⏳ EN ATTENTE' }}
            </span>
        </div>
    </div>

    {{-- ── Référence + Nom ── --}}
    <div class="rp-grad-dark" style="border-radius:16px; padding:24px 28px; margin-bottom:24px; color:white; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <p style="font-size:10px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#c4b5fd; margin-bottom:6px;">Référence du billet</p>
            <p style="font-size:28px; font-weight:900; letter-spacing:.05em; font-family:monospace;">{{ $ticket->reference }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:10px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#c4b5fd; margin-bottom:6px;">Participant</p>
            <p style="font-size:22px; font-weight:800;">{{ $ticket->full_name }}</p>
            <p style="font-size:13px; color:#c4b5fd; margin-top:2px;">{{ $ticket->price->label ?? ucfirst($ticket->category) }}</p>
        </div>
    </div>

    {{-- ── Contenu principal : infos + QR ── --}}
    <div style="display:grid; grid-template-columns:1fr 200px; gap:24px; margin-bottom:24px;">

        {{-- Infos événement + participant --}}
        <div>
            <p class="rp-section-title">Détails de l'événement</p>
            <div class="rp-grad-soft" style="border-radius:12px; padding:16px; border:1px solid #ede9fe; margin-bottom:16px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#333;">
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed; width:35%;">Événement</td>
                        <td style="padding:8px 0; font-weight:600;">{{ $ticket->event->title ?? 'N/A' }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Date</td>
                        <td style="padding:8px 0;">
                            {{ \Carbon\Carbon::parse($ticket->event->date)->format('d/m/Y') }}
                            @if($ticket->event->end_date && $ticket->event->end_date !== $ticket->event->date)
                                — {{ \Carbon\Carbon::parse($ticket->event->end_date)->format('d/m/Y') }}
                            @endif
                        </td>
                    </tr>
                    @if($ticket->event->time)
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Horaire</td>
                        <td style="padding:8px 0;">{{ $ticket->event->time }}@if($ticket->event->end_time) — {{ $ticket->event->end_time }}@endif</td>
                    </tr>
                    @endif
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Lieu</td>
                        <td style="padding:8px 0;">{{ $ticket->event->location }}</td>
                    </tr>
                    @if($ticket->event->venue_details)
                    <tr>
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Détails</td>
                        <td style="padding:8px 0;">{{ $ticket->event->venue_details }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p class="rp-section-title">Informations du participant</p>
            <div class="rp-grad-soft" style="border-radius:12px; padding:16px; border:1px solid #ede9fe; margin-bottom:16px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#333;">
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed; width:35%;">Nom</td>
                        <td style="padding:8px 0; font-weight:600;">{{ $ticket->full_name }}</td>
                    </tr>
                    @if($ticket->email)
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Email</td>
                        <td style="padding:8px 0;">{{ $ticket->email }}</td>
                    </tr>
                    @endif
                    @if($ticket->phone)
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Téléphone</td>
                        <td style="padding:8px 0;">{{ $ticket->phone }}</td>
                    </tr>
                    @endif
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Catégorie</td>
                        <td style="padding:8px 0;">{{ $ticket->price->label ?? ucfirst($ticket->category) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Montant</td>
                        <td style="padding:8px 0; font-weight:800; font-size:15px; color:#1e1b4b;">{{ number_format($ticket->amount, 2) }} {{ $ticket->currency }}</td>
                    </tr>
                </table>
            </div>

            <p class="rp-section-title">Paiement</p>
            <div class="rp-grad-soft" style="border-radius:12px; padding:16px; border:1px solid #ede9fe;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#333;">
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed; width:35%;">Mode</td>
                        <td style="padding:8px 0;">{{ ucfirst($ticket->pay_type) }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #ede9fe;">
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Statut</td>
                        <td style="padding:8px 0;">
                            <span style="padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:700;
                                {{ $ticket->payment_status === 'completed' ? 'background:#d1fae5; color:#065f46;' : 'background:#fef3c7; color:#92400e;' }}">
                                {{ $ticket->payment_status === 'completed' ? 'Payé' : 'En attente' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; font-weight:700; color:#7c3aed;">Date création</td>
                        <td style="padding:8px 0;">{{ $ticket->created_at->format('d/m/Y à H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- QR Code --}}
        <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
            <div style="background:white; border:2px solid #ede9fe; border-radius:16px; padding:16px; box-shadow:0 4px 16px rgba(109,40,217,.1);">
                <div id="qrcode"></div>
            </div>
            <p style="font-size:10px; font-family:monospace; color:#7c3aed; font-weight:700; text-align:center; word-break:break-all;">
                {{ $ticket->reference }}
            </p>
            <p style="font-size:9px; color:#9ca3af; text-align:center;">Présentez ce QR code à l'entrée</p>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="rp-gradient" style="border-radius:12px; padding:16px 20px; color:white; display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
        <div>
            <p style="font-size:11px; font-weight:700;">Never Limit Children (NLC)</p>
            <p style="font-size:10px; opacity:.8;">Ensemble pour l'inclusion</p>
        </div>
        <div style="text-align:right; font-size:10px; opacity:.8;">
            <p>info@nlcrdc.org &nbsp;·&nbsp; +243 844 338 747</p>
            <p style="margin-top:2px;">© Développé par <span style="font-weight:700;">Franck Kapuya</span></p>
        </div>
    </div>

</div>

<script>
    const qrPayload = JSON.stringify({ reference: '{{ $ticket->reference }}' });
    new QRCode(document.getElementById('qrcode'), {
        text: qrPayload,
        width: 168,
        height: 168,
        colorDark: '#1e1b4b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
</body>
</html>
