<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport GSA — {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Charger Chart.js — essaie jsdelivr, fallback sur cdnjs
        (function() {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s.onerror = function() {
                var s2 = document.createElement('script');
                s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
                document.head.appendChild(s2);
            };
            document.head.appendChild(s);
        })();
    </script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #1e1b4b; font-family: system-ui, sans-serif; }

        .rp-gradient  { background: linear-gradient(135deg, #6d28d9 0%, #a855f7 50%, #ec4899 100%); }
        .rp-grad-dark { background: linear-gradient(135deg, #3b0764 0%, #6d28d9 100%); }
        .rp-grad-pink { background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); }
        .rp-grad-rose { background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); }
        .rp-grad-soft { background: linear-gradient(135deg, #f5f3ff 0%, #fdf4ff 100%); }

        .rp-section-title {
            font-size: .7rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
            color: #7c3aed; border-left: 4px solid #ec4899; padding-left: .6rem; margin-bottom: 1rem;
        }
        .rp-bar-bg   { background: #ede9fe; border-radius: 9999px; height: 8px; overflow: hidden; }
        .rp-bar-fill { background: linear-gradient(90deg, #7c3aed, #ec4899); border-radius: 9999px; height: 8px; }
        .rp-card     { border-radius: .75rem; padding: 1rem; color: #fff; position: relative; overflow: hidden; }
        .rp-card::after {
            content: ''; position: absolute; top: -16px; right: -16px;
            width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,.08);
        }

        /* Pages PDF — largeur A4 fixe */
        .pdf-page {
            width: 794px;
            min-height: 1123px;
            background: white;
            margin: 0 auto 24px;
            padding: 48px;
            position: relative;
        }

        /* Page de garde — fond blanc style annual report */
        .pdf-page-cover {
            width: 794px;
            height: 1123px;
            background: #ffffff;
            margin: 0 auto 24px;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        /* Formes organiques SVG */
        .cover-blob-top {
            position: absolute;
            top: 0; right: 0;
            width: 380px; height: 480px;
        }
        .cover-blob-bottom {
            position: absolute;
            bottom: 0; left: 0;
            width: 340px; height: 320px;
        }
        /* Cercles décoratifs */
        .cover-dot {
            position: absolute;
            border-radius: 50%;
        }

        .pdf-page-footer {
            position: absolute;
            bottom: 24px; left: 48px; right: 48px;
            display: flex; justify-content: space-between;
            font-size: 10px; color: #9ca3af;
            border-top: 1px solid #f3f4f6; padding-top: 8px;
        }

        /* Bouton export flottant */
        #btn-export {
            position: fixed; bottom: 32px; right: 32px; z-index: 999;
            background: linear-gradient(135deg, #6d28d9, #ec4899);
            color: white; border: none; border-radius: 50px;
            padding: 14px 28px; font-size: 15px; font-weight: 700;
            cursor: pointer; box-shadow: 0 8px 32px rgba(109,40,217,.5);
            display: flex; align-items: center; gap: 10px; transition: opacity .2s;
        }
        #btn-export:hover   { opacity: .9; }
        #btn-export:disabled { opacity: .6; cursor: wait; }

        #progress-bar {
            position: fixed; top: 0; left: 0; height: 4px; width: 0%;
            background: linear-gradient(90deg, #7c3aed, #ec4899);
            transition: width .3s; z-index: 9999;
        }

        /* Décorations géométriques cover */
        .cover-circle-1 {
            position: absolute; width: 500px; height: 500px; border-radius: 50%;
            border: 1px solid rgba(168,85,247,.2);
            top: -100px; right: -150px;
        }
        .cover-circle-2 {
            position: absolute; width: 350px; height: 350px; border-radius: 50%;
            border: 1px solid rgba(236,72,153,.15);
            top: 50px; right: -50px;
        }
        .cover-circle-3 {
            position: absolute; width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(109,40,217,.15) 0%, transparent 70%);
            bottom: -200px; left: -200px;
        }
        .cover-line {
            position: absolute; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, #a855f7, #ec4899, transparent);
        }

        /* ── Impression ── */
        @media print {
            body { background: white !important; }
            #btn-export, #progress-bar, #print-toolbar { display: none !important; }
            .pdf-page, .pdf-page-cover {
                width: 100% !important;
                margin: 0 !important;
                page-break-after: always;
                break-after: page;
                box-shadow: none !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div id="progress-bar"></div>

<!-- Toolbar impression -->
<div id="print-toolbar" style="position:fixed;bottom:32px;right:32px;z-index:999;display:flex;gap:12px;align-items:center;">
    <button id="btn-export" onclick="exportPDF()">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Exporter en PDF
    </button>
</div>

<div id="rapport-content">

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE DE GARDE                                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page-cover" id="page-cover">

    {{-- Blob haut-droite : forme organique via clip-path + background inline --}}
    <div style="position:absolute;top:0;right:0;width:400px;height:500px;overflow:hidden;pointer-events:none;">
        {{-- Forme principale violet→rouge --}}
        <div style="position:absolute;top:-40px;right:-60px;width:340px;height:440px;
                    background:linear-gradient(160deg,#7c3aed 0%,#a855f7 45%,#ef4444 100%);
                    border-radius:0 0 0 80%;opacity:0.92;"></div>
        {{-- Forme secondaire rouge→orange --}}
        <div style="position:absolute;top:20px;right:-20px;width:220px;height:360px;
                    background:linear-gradient(160deg,#ef4444 0%,#f97316 100%);
                    border-radius:0 0 0 70%;opacity:0.55;"></div>
        {{-- Cercle déco violet --}}
        <div style="position:absolute;top:68px;right:148px;width:52px;height:52px;border-radius:50%;
                    background:linear-gradient(135deg,#7c3aed,#a855f7);opacity:0.95;"></div>
        {{-- Cercle déco rouge --}}
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
        {{-- Cercle déco bas --}}
        <div style="position:absolute;bottom:30px;left:190px;width:36px;height:36px;border-radius:50%;
                    background:linear-gradient(135deg,#ef4444,#f97316);opacity:0.9;"></div>
        <div style="position:absolute;bottom:120px;left:60px;width:26px;height:26px;border-radius:50%;
                    background:linear-gradient(135deg,#7c3aed,#a855f7);opacity:0.75;"></div>
    </div>

    {{-- Lignes courbes décoratives (SVG sans gradient — juste stroke) --}}
    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;" viewBox="0 0 794 1123" fill="none">
        <path d="M100 300 C200 280,350 350,400 300 C450 250,480 180,550 200" stroke="#e5e7eb" stroke-width="1" fill="none"/>
        <path d="M80 340 C180 320,330 390,380 340 C430 290,460 220,530 240" stroke="#e5e7eb" stroke-width="1" fill="none"/>
        <path d="M60 380 C160 360,310 430,360 380 C410 330,440 260,510 280" stroke="#e5e7eb" stroke-width="1" fill="none"/>
    </svg>

    {{-- Contenu principal --}}
    <div style="position:relative;z-index:10;padding:44px 48px;height:100%;display:flex;flex-direction:column;">

        {{-- Logo --}}
        <div style="margin-bottom:auto;">
            <img src="{{ asset('logo-nlc-blanc.png') }}"
                 alt="Never Limit Children"
                 style="height:64px;width:auto;object-fit:contain;"
                 onerror="this.style.display='none';">
        </div>

        {{-- Titre + description --}}
        <div style="flex:1;display:flex;flex-direction:column;justify-content:center;max-width:420px;padding-top:40px;">
            <div style="margin-bottom:24px;">
                <p style="font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#7c3aed;margin-bottom:6px;">
                    Rapport d'activité
                </p>
                <h1 style="font-size:54px;font-weight:900;color:#1e1b4b;line-height:1.0;letter-spacing:-.02em;margin-bottom:0;">
                    RAPPORT
                </h1>
                <h2 style="font-size:42px;font-weight:300;color:#7c3aed;line-height:1.0;letter-spacing:-.01em;margin-bottom:16px;">
                    D'ACTIVITÉ
                </h2>
                <p style="font-size:22px;font-weight:800;color:#ef4444;letter-spacing:.02em;">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </p>
            </div>

            <p style="font-size:11px;color:#6b7280;line-height:1.7;margin-bottom:32px;max-width:360px;">
                Ce rapport présente les statistiques d'activité du site de réservation GSA pour la période sélectionnée,
                incluant les ventes par canal, l'activité des agents et les performances par événement.
            </p>

            <div style="display:flex;flex-direction:column;gap:14px;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px;">Billets & Paiements</p>
                        <p style="font-size:10px;color:#9ca3af;line-height:1.5;">Suivi complet des tickets créés, validés et des revenus encaissés par canal de vente.</p>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#f97316);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:700;color:#ef4444;text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px;">Activité des agents</p>
                        <p style="font-size:10px;color:#9ca3af;line-height:1.5;">Performance individuelle des agents mobiles : validations, scans QR et revenus générés.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nom de l'événement bas-droite --}}
        <div style="align-self:flex-end;text-align:right;margin-bottom:48px;">
            <div style="width:40px;height:3px;background:linear-gradient(90deg,#7c3aed,#ef4444);border-radius:9999px;margin-left:auto;margin-bottom:10px;"></div>
            <p style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Événement</p>
            <p style="font-size:20px;font-weight:900;color:#1e1b4b;line-height:1.2;max-width:260px;text-align:right;">
                {{ $reportData['filteredEvent'] ? strtoupper($reportData['filteredEvent']->title) : 'TOUS LES ÉVÉNEMENTS' }}
            </p>
        </div>

        {{-- Footer --}}
        <div style="border-top:1px solid #e5e7eb;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
            <p style="font-size:9px;color:#9ca3af;">
                Site de réservation GSA &nbsp;·&nbsp; {{ now()->format('d/m/Y à H:i') }}
            </p>
            <p style="font-size:9px;color:#9ca3af;text-align:right;">
                © Rapport généré par
                <span style="color:#7c3aed;font-weight:700;">franck_codes</span>
                (franckkapuya.com)
            </p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 1 : KPIs + Répartition + Canaux                      --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page" id="page-1">
    {{-- Header page --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid #f3f4f6;">
        <div>
            <p style="font-size:9px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#a855f7; margin-bottom:2px;">NLC Events — GSA</p>
            <p style="font-size:18px; font-weight:900; color:#1e1b4b;">Statistiques générales</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#9ca3af;">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            @if($reportData['filteredEvent'])
            <p style="font-size:9px; font-weight:600; color:#7c3aed;">{{ $reportData['filteredEvent']->title }}</p>
            @endif
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="rp-card rp-grad-dark text-center">
            <p class="text-3xl font-black">{{ $reportData['totalTickets'] }}</p>
            <p class="text-xs text-purple-300 mt-1 uppercase tracking-wide">Tickets créés</p>
        </div>
        <div class="rp-card rp-gradient text-center">
            <p class="text-3xl font-black">{{ $reportData['confirmed'] }}</p>
            <p class="text-xs text-purple-100 mt-1 uppercase tracking-wide">Confirmés</p>
        </div>
        <div class="rp-card rp-grad-pink text-center">
            <p class="text-3xl font-black">{{ $reportData['pending'] }}</p>
            <p class="text-xs text-pink-100 mt-1 uppercase tracking-wide">En attente</p>
        </div>
        <div class="rp-card rp-grad-rose text-center">
            <p class="text-3xl font-black">{{ $reportData['ticketScans'] }}</p>
            <p class="text-xs text-red-100 mt-1 uppercase tracking-wide">Scans billets</p>
        </div>
    </div>

    {{-- Donut + Canaux --}}
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="border border-purple-100 rounded-2xl p-5">
            <p class="rp-section-title">Répartition des tickets</p>
            <div class="flex items-center gap-4">
                <div style="width:140px;height:140px;flex-shrink:0;"><canvas id="chartStatuts"></canvas></div>
                <div class="space-y-2 flex-1 text-sm">
                    @php $cancelled = max(0, $reportData['totalTickets'] - $reportData['confirmed'] - $reportData['pending']); @endphp
                    <div class="flex justify-between"><div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span><span class="text-gray-600">Confirmés</span></div><span class="font-bold text-purple-700">{{ $reportData['confirmed'] }}</span></div>
                    <div class="flex justify-between"><div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-pink-400 inline-block"></span><span class="text-gray-600">En attente</span></div><span class="font-bold text-pink-600">{{ $reportData['pending'] }}</span></div>
                    <div class="flex justify-between"><div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span><span class="text-gray-600">Autres</span></div><span class="font-bold text-gray-500">{{ $cancelled }}</span></div>
                    <div class="pt-2 border-t border-purple-50 flex justify-between"><span class="text-xs text-gray-400 uppercase">Total</span><span class="font-black text-gray-800">{{ $reportData['totalTickets'] }}</span></div>
                </div>
            </div>
        </div>
        <div class="border border-purple-100 rounded-2xl p-5">
            <p class="rp-section-title">Canaux de vente</p>
            <div style="position:relative; width:100%; height:180px;">
                <canvas id="chartCanaux"></canvas>
            </div>
        </div>
    </div>

    {{-- Détail canaux --}}
    <p class="rp-section-title">Détail par canal</p>
    @php
        $physRate   = $reportData['physicalTotal'] > 0 ? ($reportData['physicalValidated'] / $reportData['physicalTotal']) * 100 : 0;
        $onlineRate = $reportData['onlineTotal']   > 0 ? ($reportData['onlineValidated']   / $reportData['onlineTotal'])   * 100 : 0;
    @endphp
    <div class="grid grid-cols-2 gap-6">
        <div class="rp-grad-soft rounded-xl p-4 border border-purple-100">
            <div class="flex items-center gap-2 mb-3">
                <span class="rp-grad-dark w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-black">QR</span>
                <div><p class="font-bold text-gray-800 text-sm">Billets physiques</p><p class="text-xs text-gray-400">QR physique</p></div>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total créés</span><span class="font-bold">{{ $reportData['physicalTotal'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Validés</span><span class="font-bold text-purple-700">{{ $reportData['physicalValidated'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Revenus</span><span class="font-bold">{{ number_format($reportData['physicalRevenue'], 2) }} USD</span></div>
                <div class="pt-1"><div class="flex justify-between text-xs text-gray-400 mb-1"><span>Taux</span><span class="font-bold text-purple-700">{{ number_format($physRate,1) }} %</span></div><div class="rp-bar-bg"><div class="rp-bar-fill" style="width:{{ min($physRate,100) }}%"></div></div></div>
            </div>
        </div>
        <div class="rp-grad-soft rounded-xl p-4 border border-purple-100">
            <div class="flex items-center gap-2 mb-3">
                <span class="rp-grad-pink w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-black">WEB</span>
                <div><p class="font-bold text-gray-800 text-sm">Billets en ligne</p><p class="text-xs text-gray-400">Site web</p></div>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total créés</span><span class="font-bold">{{ $reportData['onlineTotal'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Validés</span><span class="font-bold text-pink-600">{{ $reportData['onlineValidated'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Revenus</span><span class="font-bold">{{ number_format($reportData['onlineRevenue'], 2) }} USD</span></div>
                <div class="pt-1"><div class="flex justify-between text-xs text-gray-400 mb-1"><span>Taux</span><span class="font-bold text-pink-600">{{ number_format($onlineRate,1) }} %</span></div><div class="rp-bar-bg"><div class="rp-bar-fill" style="width:{{ min($onlineRate,100) }}%"></div></div></div>
            </div>
        </div>
    </div>

    <div class="pdf-page-footer">
        <span>NLC Events — Rapport d'activité GSA</span>
        <span>Page 1 / 4</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 2 : Activité des agents                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page" id="page-2">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid #f3f4f6;">
        <div>
            <p style="font-size:9px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#a855f7; margin-bottom:2px;">NLC Events — GSA</p>
            <p style="font-size:18px; font-weight:900; color:#1e1b4b;">Activité des agents</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#9ca3af;">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        </div>
    </div>

    @if($reportData['agentActivity']->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-400 text-sm italic">Aucun agent n'a effectué de validation sur cette période.</p>
        </div>
    @else
        <div style="position:relative; width:100%; height:140px; margin-bottom:1.5rem;"><canvas id="chartAgents"></canvas></div>
        <table class="w-full text-xs">
            <thead>
                <tr class="rp-grad-soft">
                    <th class="px-3 py-2 text-left font-bold uppercase tracking-wide text-purple-700 rounded-l-lg">#</th>
                    <th class="px-3 py-2 text-left font-bold uppercase tracking-wide text-purple-700">Agent</th>
                    <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Validations</th>
                    <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Physiques</th>
                    <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">En ligne</th>
                    <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Scans QR</th>
                    <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700 rounded-r-lg">Revenus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-purple-50">
                @foreach($reportData['agentActivity'] as $i => $agent)
                @php $maxVal = $reportData['agentActivity']->max('total_validations') ?: 1; $pct = round(($agent->total_validations / $maxVal) * 100); @endphp
                <tr>
                    <td class="px-3 py-2 text-gray-400 font-bold">{{ $i+1 }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            <div class="{{ $i===0?'rp-gradient':($i===1?'rp-grad-pink':'bg-purple-100') }} w-7 h-7 rounded-full flex items-center justify-center text-xs font-black {{ $i<2?'text-white':'text-purple-700' }}">
                                {{ strtoupper(substr($agent->agent_name,0,1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $agent->agent_name }}</p>
                                <div class="rp-bar-bg w-20 mt-0.5"><div class="rp-bar-fill" style="width:{{ $pct }}%"></div></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-right font-black text-purple-700">{{ $agent->total_validations }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">{{ $agent->physical }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">{{ $agent->online }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-pink-600">{{ $agent->total_scans }}</td>
                    <td class="px-3 py-2 text-right font-bold text-gray-800">{{ number_format($agent->revenue,2) }} <span class="text-gray-400">USD</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pdf-page-footer">
        <span>NLC Events — Rapport d'activité GSA</span>
        <span>Page 2 / 4</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 3 : Statistiques événements                          --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page" id="page-3">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid #f3f4f6;">
        <div>
            <p style="font-size:9px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#a855f7; margin-bottom:2px;">NLC Events — GSA</p>
            <p style="font-size:18px; font-weight:900; color:#1e1b4b;">Statistiques des événements</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#9ca3af;">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="border-2 border-purple-100 rounded-xl p-4 text-center">
            <p class="text-3xl font-black text-purple-700">{{ $reportData['eventScans'] }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Scans page événement</p>
        </div>
        <div class="border-2 border-pink-100 rounded-xl p-4 text-center">
            <p class="text-3xl font-black text-pink-600">{{ $reportData['uniqueScanned'] }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Billets uniques scannés</p>
        </div>
        <div class="rp-gradient rounded-xl p-4 text-center text-white">
            <p class="text-3xl font-black">{{ number_format($reportData['totalRevenue'], 2) }}</p>
            <p class="text-xs text-purple-200 uppercase tracking-wide mt-1">Revenus encaissés (USD)</p>
        </div>
    </div>

    @if($reportData['eventDetails']->isNotEmpty())
    <div style="position:relative; width:100%; height:130px; margin-bottom:1.5rem;"><canvas id="chartEvents"></canvas></div>
    <table class="w-full text-xs">
        <thead>
            <tr class="rp-grad-soft">
                <th class="px-3 py-2 text-left font-bold uppercase tracking-wide text-purple-700 rounded-l-lg">Événement</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Créés</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Validés</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Taux</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700 rounded-r-lg">Revenus</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-purple-50">
            @foreach($reportData['eventDetails'] as $ev)
            @php $evRate = $ev->tickets_created > 0 ? ($ev->tickets_validated / $ev->tickets_created) * 100 : 0; @endphp
            <tr>
                <td class="px-3 py-2 font-semibold text-gray-800">{{ $ev->title }}</td>
                <td class="px-3 py-2 text-right text-gray-600">{{ $ev->tickets_created }}</td>
                <td class="px-3 py-2 text-right font-bold text-purple-700">{{ $ev->tickets_validated }}</td>
                <td class="px-3 py-2 text-right">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $evRate >= 50 ? 'bg-purple-100 text-purple-700' : 'bg-pink-100 text-pink-700' }}">
                        {{ number_format($evRate, 1) }} %
                    </span>
                </td>
                <td class="px-3 py-2 text-right font-bold text-gray-800">{{ number_format($ev->event_revenue, 2) }} <span class="text-gray-400">USD</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-gray-400 text-sm italic text-center py-8">Aucun événement avec des tickets sur cette période.</p>
    @endif

    {{-- Footer rapport --}}
    <div class="rp-gradient rounded-xl p-4 text-center text-white text-xs" style="margin-top:auto; position:absolute; bottom:60px; left:48px; right:48px;">
        Rapport généré automatiquement par NLC Events Admin &nbsp;·&nbsp; {{ now()->format('d/m/Y à H:i') }}
    </div>

    <div class="pdf-page-footer">
        <span>NLC Events — Rapport d'activité GSA</span>
        <span>Page 3 / 4</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 4 : Billets par type de billet                       --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="pdf-page" id="page-4">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid #f3f4f6;">
        <div>
            <p style="font-size:9px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#a855f7; margin-bottom:2px;">NLC Events — GSA</p>
            <p style="font-size:18px; font-weight:900; color:#1e1b4b;">Billets par type de billet</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#9ca3af;">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            @if($reportData['filteredEvent'])
            <p style="font-size:9px; font-weight:600; color:#7c3aed;">{{ $reportData['filteredEvent']->title }}</p>
            @endif
        </div>
    </div>

    {{-- Section : Par catégorie de participant --}}
    <p class="rp-section-title" style="margin-bottom:1rem;">Par catégorie de participant</p>

    @php
        $catLabels = ['teacher' => 'Enseignant', 'student_1day' => 'Étudiant (1 jour)', 'student_2days' => 'Étudiant (2 jours)', 'doctor' => 'Médecin', 'parent' => 'Parent', 'etudiant' => 'Étudiant', 'enseignant' => 'Enseignant', 'medecin' => 'Médecin'];
        $totalCat = $reportData['ticketsByCategory']->sum('total') ?: 1;
    @endphp

    <div style="position:relative; width:100%; height:160px; margin-bottom:1.5rem;"><canvas id="chartCategories"></canvas></div>

    <table class="w-full text-xs mb-8">
        <thead>
            <tr class="rp-grad-soft">
                <th class="px-3 py-2 text-left font-bold uppercase tracking-wide text-purple-700 rounded-l-lg">Catégorie</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Total créés</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Validés</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Taux</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700">Part</th>
                <th class="px-3 py-2 text-right font-bold uppercase tracking-wide text-purple-700 rounded-r-lg">Revenus (USD)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-purple-50">
            @forelse($reportData['ticketsByCategory'] as $cat)
            @php
                $catRate = $cat->total > 0 ? ($cat->validated / $cat->total) * 100 : 0;
                $catPart = ($cat->total / $totalCat) * 100;
                $catLabel = $catLabels[$cat->category] ?? ucfirst($cat->category);
            @endphp
            <tr>
                <td class="px-3 py-2 font-semibold text-gray-800">{{ $catLabel }}</td>
                <td class="px-3 py-2 text-right font-bold text-gray-700">{{ $cat->total }}</td>
                <td class="px-3 py-2 text-right font-bold text-purple-700">{{ $cat->validated }}</td>
                <td class="px-3 py-2 text-right">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $catRate >= 50 ? 'bg-purple-100 text-purple-700' : 'bg-pink-100 text-pink-700' }}">
                        {{ number_format($catRate, 1) }} %
                    </span>
                </td>
                <td class="px-3 py-2 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <div class="rp-bar-bg w-16"><div class="rp-bar-fill" style="width:{{ min($catPart,100) }}%"></div></div>
                        <span class="text-gray-500 w-10 text-right">{{ number_format($catPart, 1) }}%</span>
                    </div>
                </td>
                <td class="px-3 py-2 text-right font-bold text-gray-800">{{ number_format($cat->revenue, 2) }} <span class="text-gray-400">USD</span></td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-3 py-8 text-center text-gray-400 italic">Aucune donnée sur cette période.</td></tr>
            @endforelse
        </tbody>
        @if($reportData['ticketsByCategory']->isNotEmpty())
        <tfoot>
            <tr class="rp-grad-soft font-bold">
                <td class="px-3 py-2 text-purple-700 rounded-l-lg">TOTAL</td>
                <td class="px-3 py-2 text-right text-gray-800">{{ $reportData['ticketsByCategory']->sum('total') }}</td>
                <td class="px-3 py-2 text-right text-purple-700">{{ $reportData['ticketsByCategory']->sum('validated') }}</td>
                <td class="px-3 py-2 text-right">
                    @php $globalRate = $reportData['ticketsByCategory']->sum('total') > 0 ? ($reportData['ticketsByCategory']->sum('validated') / $reportData['ticketsByCategory']->sum('total')) * 100 : 0; @endphp
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">{{ number_format($globalRate, 1) }} %</span>
                </td>
                <td class="px-3 py-2 text-right text-gray-500">100%</td>
                <td class="px-3 py-2 text-right text-gray-800 rounded-r-lg">{{ number_format($reportData['ticketsByCategory']->sum('revenue'), 2) }} <span class="text-gray-400">USD</span></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Section : Par mode de paiement --}}
    <p class="rp-section-title" style="margin-bottom:1rem;">Par mode de paiement</p>

    @php
        $payLabels = ['cash' => 'Caisse', 'maxicash' => 'MaxiCash', 'mpesa' => 'M-Pesa', 'orange_money' => 'Orange Money'];
        $totalPay = $reportData['ticketsByPayType']->sum('total') ?: 1;
    @endphp

    <div class="grid grid-cols-2 gap-4">
        @forelse($reportData['ticketsByPayType'] as $pay)
        @php
            $payRate = $pay->total > 0 ? ($pay->validated / $pay->total) * 100 : 0;
            $payPart = ($pay->total / $totalPay) * 100;
            $payLabel = $payLabels[$pay->pay_type] ?? ucfirst($pay->pay_type);
        @endphp
        <div class="rp-grad-soft rounded-xl p-4 border border-purple-100">
            <div class="flex items-center justify-between mb-3">
                <p class="font-bold text-gray-800 text-sm">{{ $payLabel }}</p>
                <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">{{ number_format($payPart, 1) }}%</span>
            </div>
            <div class="space-y-1 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">Total créés</span><span class="font-bold">{{ $pay->total }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Validés</span><span class="font-bold text-purple-700">{{ $pay->validated }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Revenus</span><span class="font-bold">{{ number_format($pay->revenue, 2) }} USD</span></div>
                <div class="pt-1">
                    <div class="flex justify-between text-gray-400 mb-1"><span>Taux validation</span><span class="font-bold text-purple-700">{{ number_format($payRate, 1) }}%</span></div>
                    <div class="rp-bar-bg"><div class="rp-bar-fill" style="width:{{ min($payRate,100) }}%"></div></div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center text-gray-400 italic py-4">Aucune donnée.</div>
        @endforelse
    </div>

    <div class="pdf-page-footer">
        <span>NLC Events — Rapport d'activité GSA</span>
        <span>Page 4 / 4</span>
    </div>
</div>

</div>{{-- fin #rapport-content --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    const purple = '#7c3aed', violet = '#a855f7', pink = '#ec4899', rose = '#f43f5e', light = '#ede9fe';
    const gradColors = [purple, violet, pink, rose, '#6366f1', '#8b5cf6'];
    Chart.defaults.font.family = 'system-ui, sans-serif';
    Chart.defaults.font.size   = 11;

    // Donut statuts
    new Chart(document.getElementById('chartStatuts'), {
        type: 'doughnut',
        data: {
            labels: ['Confirmés','En attente','Autres'],
            datasets: [{ data: [{{ $reportData['confirmed'] }}, {{ $reportData['pending'] }}, {{ $cancelled ?? 0 }}],
                backgroundColor: [purple, pink, '#e5e7eb'], borderWidth: 0, hoverOffset: 4 }]
        },
        options: { cutout:'72%', plugins:{ legend:{display:false} } }
    });

    // Barres canaux
    new Chart(document.getElementById('chartCanaux'), {
        type: 'bar',
        data: {
            labels: ['Physique','En ligne'],
            datasets: [
                { label:'Créés',        data:[{{ $reportData['physicalTotal'] }},{{ $reportData['onlineTotal'] }}],         backgroundColor:light,  borderRadius:6 },
                { label:'Validés',      data:[{{ $reportData['physicalValidated'] }},{{ $reportData['onlineValidated'] }}], backgroundColor:purple, borderRadius:6 },
                { label:'Revenus (USD)',data:[{{ $reportData['physicalRevenue'] }},{{ $reportData['onlineRevenue'] }}],      backgroundColor:pink,   borderRadius:6, yAxisID:'y2' }
            ]
        },
        options: { responsive:true, maintainAspectRatio: false, plugins:{legend:{position:'top'}},
            scales:{ y:{beginAtZero:true,grid:{color:'#f3f4f6'},ticks:{color:'#9ca3af'}},
                     y2:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{color:'#ec4899',callback:v=>v+' $'}},
                     x:{grid:{display:false},ticks:{color:'#6b7280'}} } }
    });

    // Barres agents
    @if($reportData['agentActivity']->isNotEmpty())
    const agentLabels = {!! json_encode($reportData['agentActivity']->pluck('agent_name')->toArray()) !!};
    const agentValid  = {!! json_encode($reportData['agentActivity']->pluck('total_validations')->toArray()) !!};
    const agentScans  = {!! json_encode($reportData['agentActivity']->pluck('total_scans')->toArray()) !!};
    const agentRev    = {!! json_encode($reportData['agentActivity']->pluck('revenue')->map(fn($v)=>round((float)$v,2))->toArray()) !!};
    new Chart(document.getElementById('chartAgents'), {
        type:'bar',
        data:{ labels:agentLabels, datasets:[
            { label:'Validations', data:agentValid, backgroundColor:agentLabels.map((_,i)=>gradColors[i%gradColors.length]), borderRadius:8, borderSkipped:false },
            { label:'Scans QR',    data:agentScans, backgroundColor:agentLabels.map((_,i)=>gradColors[i%gradColors.length]+'55'), borderRadius:8, borderSkipped:false },
            { label:'Revenus (USD)', data:agentRev, type:'line', borderColor:pink, backgroundColor:'transparent',
              pointBackgroundColor:pink, pointRadius:5, tension:.4, yAxisID:'yRev' }
        ]},
        options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'top'}},
            scales:{ y:{beginAtZero:true,grid:{color:'#f3f4f6'},ticks:{color:'#9ca3af'}},
                     yRev:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{color:'#ec4899',callback:v=>v+' $'}},
                     x:{grid:{display:false},ticks:{color:'#6b7280'}} } }
    });
    @endif

    // Barres catégories de billets
    @if($reportData['ticketsByCategory']->isNotEmpty())
    const catLabelsMap = {teacher:'Enseignant', student_1day:'Étudiant 1j', student_2days:'Étudiant 2j', doctor:'Médecin', parent:'Parent', etudiant:'Étudiant', enseignant:'Enseignant', medecin:'Médecin'};
    const catKeys   = {!! json_encode($reportData['ticketsByCategory']->pluck('category')->toArray()) !!};
    const catLabels = catKeys.map(k => catLabelsMap[k] || k);
    const catTotal  = {!! json_encode($reportData['ticketsByCategory']->pluck('total')->toArray()) !!};
    const catValid  = {!! json_encode($reportData['ticketsByCategory']->pluck('validated')->toArray()) !!};
    const catRev    = {!! json_encode($reportData['ticketsByCategory']->pluck('revenue')->map(fn($v)=>round((float)$v,2))->toArray()) !!};
    new Chart(document.getElementById('chartCategories'), {
        type: 'bar',
        data: { labels: catLabels, datasets: [
            { label: 'Total créés', data: catTotal, backgroundColor: light,  borderRadius: 6 },
            { label: 'Validés',     data: catValid, backgroundColor: purple, borderRadius: 6 },
            { label: 'Revenus (USD)', data: catRev, type: 'line', borderColor: pink, backgroundColor: 'transparent',
              pointBackgroundColor: pink, pointRadius: 5, tension: .4, yAxisID: 'yRevCat' }
        ]},
        options: { responsive: true, maintainAspectRatio: false, interaction: {mode:'index',intersect:false},
            plugins: { legend: {position:'top'} },
            scales: { y: {beginAtZero:true,grid:{color:'#f3f4f6'},ticks:{color:'#9ca3af'}},
                      yRevCat: {beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{color:'#ec4899',callback:v=>v+' $'}},
                      x: {grid:{display:false},ticks:{color:'#6b7280'}} } }
    });
    @endif
    @if($reportData['eventDetails']->isNotEmpty())
    const evLabels  = {!! json_encode($reportData['eventDetails']->pluck('title')->toArray()) !!};
    const evCreated = {!! json_encode($reportData['eventDetails']->pluck('tickets_created')->toArray()) !!};
    const evValid   = {!! json_encode($reportData['eventDetails']->pluck('tickets_validated')->toArray()) !!};
    const evRev     = {!! json_encode($reportData['eventDetails']->pluck('event_revenue')->map(fn($v)=>round((float)$v,2))->toArray()) !!};
    new Chart(document.getElementById('chartEvents'), {
        type:'bar',
        data:{ labels:evLabels, datasets:[
            { label:'Tickets créés', data:evCreated, backgroundColor:light,  borderRadius:6 },
            { label:'Validés',       data:evValid,   backgroundColor:purple, borderRadius:6 },
            { label:'Revenus (USD)', data:evRev, type:'line', borderColor:pink, backgroundColor:'transparent',
              pointBackgroundColor:pink, pointRadius:5, tension:.4, yAxisID:'yRev2' }
        ]},
        options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'top'}},
            scales:{ y:{beginAtZero:true,grid:{color:'#f3f4f6'},ticks:{color:'#9ca3af'}},
                     yRev2:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{color:'#ec4899',callback:v=>v+' $'}},
                     x:{grid:{display:false},ticks:{color:'#6b7280'}} } }
    });
    @endif
});

// Export PDF — utilise window.print() (fonctionne en local ET en production)
function exportPDF() {
    const btn = document.getElementById('btn-export');
    const bar = document.getElementById('progress-bar');
    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
        <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z"></path></svg> Préparation…`;
    bar.style.width = '60%';

    // Laisser le temps aux graphiques de se rendre complètement
    setTimeout(() => {
        bar.style.width = '100%';
        setTimeout(() => {
            window.print();
            bar.style.width = '0%';
            btn.disabled = false;
            btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Exporter en PDF`;
        }, 300);
    }, 800);
}
</script>
</body>
</html>
