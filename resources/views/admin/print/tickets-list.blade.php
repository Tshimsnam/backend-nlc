<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des billets — NLC Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        .pdf-page {
            width: 794px;
            background: white;
            margin: 0 auto 24px;
            padding: 48px;
            position: relative;
        }
        .pdf-page-cover {
            width: 794px;
            height: 1123px;
            background: #ffffff;
            margin: 0 auto 24px;
            padding: 0;
            position: relative;
            overflow: hidden;
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
            .pdf-page, .pdf-page-cover { margin: 0; box-shadow: none; page-break-after: always; }
            .pdf-page:last-child { page-break-after: avoid; }
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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE DE GARDE                                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page-cover">

    {{-- Blob haut-droite --}}
    <div style="position:absolute;top:0;right:0;width:400px;height:500px;overflow:hidden;pointer-events:none;">
        <div style="position:absolute;top:-40px;right:-60px;width:340px;height:440px;
                    background:linear-gradient(160deg,#7c3aed 0%,#a855f7 45%,#ef4444 100%);
                    border-radius:0 0 0 80%;opacity:0.92;"></div>
        <div style="position:absolute;top:20px;right:-20px;width:220px;height:360px;
                    background:linear-gradient(160deg,#ef4444 0%,#f97316 100%);
                    border-radius:0 0 0 70%;opacity:0.55;"></div>
        <div style="position:absolute;top:68px;right:148px;width:52px;height:52px;border-radius:50%;
                    background:linear-gradient(135deg,#7c3aed,#a855f7);opacity:0.95;"></div>
        <div style="position:absolute;top:280px;right:68px;width:40px;height:40px;border-radius:50%;
                    background:linear-gradient(135deg,#ef4444,#f97316);opacity:0.9;"></div>
    </div>

    {{-- Blob bas-gauche --}}
    <div style="position:absolute;bottom:0;left:0;width:360px;height:340px;overflow:hidden;pointer-events:none;">
        <div style="position:absolute;bottom:-40px;left:-40px;width:320px;height:280px;
                    background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);
                    border-radius:0 80% 0 0;opacity:0.88;"></div>
        <div style="position:absolute;bottom:-20px;left:-20px;width:360px;height:120px;
                    background:linear-gradient(90deg,#ef4444 0%,#f97316 100%);
                    border-radius:0 60% 0 0;opacity:0.6;"></div>
        <div style="position:absolute;bottom:30px;left:190px;width:36px;height:36px;border-radius:50%;
                    background:linear-gradient(135deg,#ef4444,#f97316);opacity:0.9;"></div>
        <div style="position:absolute;bottom:120px;left:60px;width:26px;height:26px;border-radius:50%;
                    background:linear-gradient(135deg,#7c3aed,#a855f7);opacity:0.75;"></div>
    </div>

    {{-- Lignes décoratives --}}
    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;" viewBox="0 0 794 1123" fill="none">
        <path d="M100 300 C200 280,350 350,400 300 C450 250,480 180,550 200" stroke="#e5e7eb" stroke-width="1" fill="none"/>
        <path d="M80 340 C180 320,330 390,380 340 C430 290,460 220,530 240" stroke="#e5e7eb" stroke-width="1" fill="none"/>
        <path d="M60 380 C160 360,310 430,360 380 C410 330,440 260,510 280" stroke="#e5e7eb" stroke-width="1" fill="none"/>
    </svg>

    {{-- Contenu --}}
    <div style="position:relative;z-index:10;padding:44px 48px;height:100%;display:flex;flex-direction:column;">

        {{-- Logo --}}
        <div style="margin-bottom:auto;">
            <img src="{{ asset('logo-nlc-blanc.png') }}" alt="Never Limit Children"
                 style="height:64px;width:auto;object-fit:contain;"
                 onerror="this.style.display='none';">
        </div>

        {{-- Titre --}}
        <div style="flex:1;display:flex;flex-direction:column;justify-content:center;max-width:460px;padding-top:40px;">
            <div style="margin-bottom:24px;">
                <p style="font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#7c3aed;margin-bottom:6px;">
                    NLC Events — Liste officielle
                </p>
                <h1 style="font-size:54px;font-weight:900;color:#1e1b4b;line-height:1.0;letter-spacing:-.02em;">
                    LISTE DES
                </h1>
                <h2 style="font-size:42px;font-weight:300;color:#7c3aed;line-height:1.0;letter-spacing:-.01em;margin-bottom:16px;">
                    PARTICIPANTS
                </h2>
                <p style="font-size:22px;font-weight:800;color:#ef4444;letter-spacing:.02em;">
                    Généré le {{ now()->format('d/m/Y à H:i') }}
                </p>
            </div>

            <p style="font-size:11px;color:#6b7280;line-height:1.7;margin-bottom:32px;max-width:380px;">
                Cette liste présente l'ensemble des billets
                @if($status === 'completed') validés et payés
                @elseif($status === 'pending_cash') en attente de paiement
                @else enregistrés @endif
                dans le système de réservation NLC Events.
            </p>

            {{-- Stats --}}
            <div style="display:flex;gap:24px;">
                <div style="background:linear-gradient(135deg,#3b0764,#6d28d9);border-radius:12px;padding:16px 20px;color:white;min-width:120px;text-align:center;">
                    <p style="font-size:36px;font-weight:900;">{{ $stats['total'] }}</p>
                    <p style="font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#c4b5fd;margin-top:2px;">Billets</p>
                </div>
                <div style="background:linear-gradient(135deg,#a855f7,#ec4899);border-radius:12px;padding:16px 20px;color:white;min-width:140px;text-align:center;">
                    <p style="font-size:28px;font-weight:900;">{{ number_format($stats['revenue'], 0) }}</p>
                    <p style="font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#fce7f3;margin-top:2px;">{{ $stats['currency'] }} encaissés</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div style="border-top:1px solid #e5e7eb;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
            <p style="font-size:9px;color:#9ca3af;">NLC Events &nbsp;·&nbsp; {{ now()->format('d/m/Y à H:i') }}</p>
            <p style="font-size:9px;color:#9ca3af;">© Développé par <span style="color:#7c3aed;font-weight:700;">Franck Kapuya</span></p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE(S) : Tableau des billets                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page">

    {{-- Header page --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;padding-bottom:14px;border-bottom:2px solid #f3f4f6;">
        <div>
            <p style="font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#a855f7;margin-bottom:2px;">NLC Events</p>
            <p style="font-size:18px;font-weight:900;color:#1e1b4b;">Liste des participants</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px;color:#9ca3af;">{{ now()->format('d/m/Y à H:i') }}</p>
            <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:10px;font-weight:700;
                background:linear-gradient(135deg,#6d28d9,#ec4899);color:white;">
                {{ $stats['total'] }} billet(s)
            </span>
        </div>
    </div>

    {{-- Tableau --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:11px;border-collapse:collapse;">
        <thead>
            <tr style="background:linear-gradient(135deg,#f5f3ff,#fdf4ff);">
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">#</th>
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Référence</th>
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Nom</th>
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Email</th>
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Téléphone</th>
                <th style="padding:10px 8px;text-align:left;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Catégorie</th>
                <th style="padding:10px 8px;text-align:right;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#7c3aed;border-bottom:2px solid #ede9fe;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $i => $ticket)
            <tr style="{{ $i % 2 === 0 ? 'background:#fafafa;' : 'background:white;' }} border-bottom:1px solid #f3f4f6;">
                <td style="padding:8px;color:#9ca3af;font-weight:600;">{{ $i + 1 }}</td>
                <td style="padding:8px;font-family:monospace;font-weight:700;color:#1e1b4b;font-size:10px;">{{ $ticket->reference }}</td>
                <td style="padding:8px;font-weight:600;color:#1f2937;">{{ $ticket->full_name }}</td>
                <td style="padding:8px;color:#6b7280;font-size:10px;">{{ $ticket->email ?? '—' }}</td>
                <td style="padding:8px;color:#6b7280;font-size:10px;">{{ $ticket->phone ?? '—' }}</td>
                <td style="padding:8px;color:#374151;font-size:10px;">{{ $ticket->price->label ?? ucfirst($ticket->category) }}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#1e1b4b;">{{ number_format($ticket->amount, 0) }} <span style="color:#9ca3af;font-weight:400;">{{ $ticket->currency }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:32px;text-align:center;color:#9ca3af;font-style:italic;">Aucun billet trouvé.</td>
            </tr>
            @endforelse
        </tbody>
        @if($tickets->isNotEmpty())
        <tfoot>
            <tr style="background:linear-gradient(135deg,#f5f3ff,#fdf4ff);border-top:2px solid #ede9fe;">
                <td colspan="6" style="padding:10px 8px;font-weight:800;color:#7c3aed;text-transform:uppercase;letter-spacing:.08em;font-size:10px;">TOTAL — {{ $stats['total'] }} billet(s)</td>
                <td style="padding:10px 8px;text-align:right;font-weight:900;color:#1e1b4b;font-size:13px;">{{ number_format($stats['revenue'], 0) }} <span style="color:#9ca3af;font-weight:400;font-size:10px;">{{ $stats['currency'] }}</span></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Footer --}}
    <div style="margin-top:32px;padding-top:12px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;font-size:9px;color:#9ca3af;">
        <span>NLC Events — Liste des participants</span>
        <span>{{ now()->format('d/m/Y à H:i') }}</span>
    </div>
</div>

</body>
</html>
