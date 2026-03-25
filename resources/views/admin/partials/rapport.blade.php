{{-- Onglet Rapport d'activité --}}
<style>
    .rp-gradient    { background: linear-gradient(135deg, #6d28d9 0%, #a855f7 50%, #ec4899 100%); }
    .rp-grad-dark   { background: linear-gradient(135deg, #3b0764 0%, #6d28d9 100%); }
    .rp-grad-pink   { background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); }
    .rp-grad-rose   { background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); }
    .rp-grad-soft   { background: linear-gradient(135deg, #f5f3ff 0%, #fdf4ff 100%); }
    .rp-section-title {
        font-size:.75rem; font-weight:800; letter-spacing:.12em;
        text-transform:uppercase; color:#7c3aed;
        border-left:4px solid #ec4899; padding-left:.75rem; margin-bottom:1.25rem;
    }
    .rp-bar-bg   { background:#ede9fe; border-radius:9999px; height:10px; overflow:hidden; }
    .rp-bar-fill { background:linear-gradient(90deg,#7c3aed,#ec4899); border-radius:9999px; height:10px; transition:width .6s ease; }
    .rp-card     { border-radius:1rem; padding:1.25rem; color:#fff; position:relative; overflow:hidden; }
    .rp-card::after { content:''; position:absolute; top:-20px; right:-20px; width:80px; height:80px;
                      border-radius:50%; background:rgba(255,255,255,.08); }
    @media print {
        .no-print { display:none !important; }
        body { background:white !important; }
        canvas { max-width:100% !important; }
        .rp-gradient,.rp-grad-dark,.rp-grad-pink,.rp-grad-rose {
            -webkit-print-color-adjust:exact; print-color-adjust:exact;
        }
    }
</style>

{{-- Formulaire --}}
<div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6 no-print">
    <p class="rp-section-title">Paramètres du rapport</p>
    <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex flex-wrap items-end gap-4">
        <input type="hidden" name="tab" value="rapport">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Événement</label>
            <select name="report_event_id"
                    class="w-full border border-purple-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-purple-50">
                <option value="">— Tous les événements —</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" {{ ($reportEventId ?? '') == $ev->id ? 'selected' : '' }}>
                        {{ $ev->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Du</label>
            <input type="date" name="report_date_from" value="{{ $reportDateFrom ?? '' }}" required
                   class="border border-purple-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-purple-50">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Au</label>
            <input type="date" name="report_date_to" value="{{ $reportDateTo ?? '' }}" required
                   class="border border-purple-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-purple-50">
        </div>
        <button type="submit" class="rp-gradient text-white px-6 py-2 rounded-xl text-sm font-bold hover:opacity-90 transition shadow-md">
            Générer
        </button>
        @if($reportGenerated)
        <a href="{{ route('admin.rapport.export', ['report_date_from' => $reportDateFrom, 'report_date_to' => $reportDateTo, 'report_event_id' => $reportEventId]) }}"
           target="_blank"
           class="border border-purple-300 text-purple-700 px-5 py-2 rounded-xl text-sm font-semibold hover:bg-purple-50 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Exporter PDF
        </a>
        @endif
    </form>
</div>

@if(!$reportGenerated)
<div class="bg-white rounded-2xl border border-purple-100 p-16 text-center">
    <div class="rp-gradient w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <p class="text-gray-400 text-sm">Sélectionnez un événement et une période, puis cliquez sur <span class="font-bold text-purple-600">Générer</span>.</p>
</div>

@else
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Cover --}}
<div class="rp-gradient rounded-2xl p-8 mb-6 text-white relative overflow-hidden">
    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-white opacity-5"></div>
    <div class="absolute -bottom-12 -left-8 w-64 h-64 rounded-full bg-white opacity-5"></div>
    <div class="relative z-10">
        <p class="text-xs font-bold uppercase tracking-widest text-purple-200 mb-1">NLC Events</p>
        <h2 class="text-4xl font-black mb-1">RAPPORT <span class="font-light opacity-80">D'ACTIVITÉ</span></h2>
        <p class="text-purple-200 text-sm mb-4">Site de réservation GSA</p>
        <div class="flex flex-wrap gap-3 text-sm">
            @if($reportData['filteredEvent'])
                <span class="bg-white bg-opacity-20 rounded-lg px-3 py-1 font-semibold">📅 {{ $reportData['filteredEvent']->title }}</span>
            @endif
            <span class="bg-white bg-opacity-20 rounded-lg px-3 py-1">
                {{ \Carbon\Carbon::parse($reportDateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($reportDateTo)->format('d/m/Y') }}
            </span>
            <span class="bg-white bg-opacity-10 rounded-lg px-3 py-1 text-purple-200 text-xs">Généré le {{ now()->format('d/m/Y à H:i') }}</span>
        </div>
    </div>
</div>

{{-- 1. KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="rp-card rp-grad-dark">
        <p class="text-3xl font-black">{{ $reportData['totalTickets'] }}</p>
        <p class="text-xs text-purple-300 mt-1 uppercase tracking-wide">Tickets créés</p>
    </div>
    <div class="rp-card rp-gradient">
        <p class="text-3xl font-black">{{ $reportData['confirmed'] }}</p>
        <p class="text-xs text-purple-100 mt-1 uppercase tracking-wide">Confirmés</p>
    </div>
    <div class="rp-card rp-grad-pink">
        <p class="text-3xl font-black">{{ $reportData['pending'] }}</p>
        <p class="text-xs text-pink-100 mt-1 uppercase tracking-wide">En attente</p>
    </div>
    <div class="rp-card rp-grad-rose">
        <p class="text-3xl font-black">{{ $reportData['ticketScans'] }}</p>
        <p class="text-xs text-red-100 mt-1 uppercase tracking-wide">Scans billets</p>
    </div>
</div>

{{-- 2. GRAPHIQUES GÉNÉRAUX : Donut statuts + Barres canaux --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- Donut : répartition des statuts --}}
    <div class="bg-white rounded-2xl border border-purple-100 p-6">
        <p class="rp-section-title">Répartition des tickets</p>
        <div class="flex items-center gap-6">
            <div class="relative w-40 h-40 flex-shrink-0">
                <canvas id="chartStatuts"></canvas>
            </div>
            <div class="space-y-3 flex-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span><span class="text-sm text-gray-600">Confirmés</span></div>
                    <span class="font-bold text-purple-700">{{ $reportData['confirmed'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-pink-400 inline-block"></span><span class="text-sm text-gray-600">En attente</span></div>
                    <span class="font-bold text-pink-600">{{ $reportData['pending'] }}</span>
                </div>
                @php $cancelled = $reportData['totalTickets'] - $reportData['confirmed'] - $reportData['pending']; @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span><span class="text-sm text-gray-600">Autres</span></div>
                    <span class="font-bold text-gray-500">{{ max(0, $cancelled) }}</span>
                </div>
                <div class="pt-2 border-t border-purple-50">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Total</span>
                        <span class="font-black text-gray-800">{{ $reportData['totalTickets'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barres : comparaison canaux --}}
    <div class="bg-white rounded-2xl border border-purple-100 p-6">
        <p class="rp-section-title">Canaux de vente</p>
        <canvas id="chartCanaux" height="160"></canvas>
    </div>
</div>

{{-- 3. VENTES PAR CANAL détail --}}
<div class="bg-white rounded-2xl border border-purple-100 p-6 mb-6">
    <p class="rp-section-title">Détail ventes par canal</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @php
            $physRate   = $reportData['physicalTotal'] > 0 ? ($reportData['physicalValidated'] / $reportData['physicalTotal']) * 100 : 0;
            $onlineRate = $reportData['onlineTotal']   > 0 ? ($reportData['onlineValidated']   / $reportData['onlineTotal'])   * 100 : 0;
        @endphp
        <div class="rp-grad-soft rounded-xl p-5 border border-purple-100">
            <div class="flex items-center gap-2 mb-4">
                <span class="rp-grad-dark w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-black">QR</span>
                <div><p class="font-bold text-gray-800 text-sm">Billets physiques</p><p class="text-xs text-gray-400">QR physique</p></div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total créés</span><span class="font-bold">{{ $reportData['physicalTotal'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Validés</span><span class="font-bold text-purple-700">{{ $reportData['physicalValidated'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Revenus</span><span class="font-bold">{{ number_format($reportData['physicalRevenue'], 2) }} USD</span></div>
                <div class="pt-1">
                    <div class="flex justify-between text-xs text-gray-400 mb-1"><span>Taux de validation</span><span class="font-bold text-purple-700">{{ number_format($physRate, 1) }} %</span></div>
                    <div class="rp-bar-bg"><div class="rp-bar-fill" style="width:{{ min($physRate,100) }}%"></div></div>
                </div>
            </div>
        </div>
        <div class="rp-grad-soft rounded-xl p-5 border border-purple-100">
            <div class="flex items-center gap-2 mb-4">
                <span class="rp-grad-pink w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-black">WEB</span>
                <div><p class="font-bold text-gray-800 text-sm">Billets en ligne</p><p class="text-xs text-gray-400">Site web</p></div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total créés</span><span class="font-bold">{{ $reportData['onlineTotal'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Validés</span><span class="font-bold text-pink-600">{{ $reportData['onlineValidated'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Revenus</span><span class="font-bold">{{ number_format($reportData['onlineRevenue'], 2) }} USD</span></div>
                <div class="pt-1">
                    <div class="flex justify-between text-xs text-gray-400 mb-1"><span>Taux de validation</span><span class="font-bold text-pink-600">{{ number_format($onlineRate, 1) }} %</span></div>
                    <div class="rp-bar-bg"><div class="rp-bar-fill" style="width:{{ min($onlineRate,100) }}%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. ACTIVITÉ DES AGENTS --}}
<div class="bg-white rounded-2xl border border-purple-100 p-6 mb-6">
    <p class="rp-section-title">Activité des agents</p>
    @if($reportData['agentActivity']->isEmpty())
        <div class="text-center py-10">
            <p class="text-gray-400 text-sm italic">Aucun agent n'a effectué de validation sur cette période.</p>
        </div>
    @else
        {{-- Graphique barres groupées agents --}}
        <div class="mb-6">
            <canvas id="chartAgents" height="120"></canvas>
        </div>

        {{-- Tableau agents --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="rp-grad-soft">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-purple-700 rounded-l-xl">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-purple-700">Agent</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Validations</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Physiques</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">En ligne</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Scans QR</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700 rounded-r-xl">Revenus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    @foreach($reportData['agentActivity'] as $i => $agent)
                    @php
                        $maxVal = $reportData['agentActivity']->max('total_validations') ?: 1;
                        $pct    = round(($agent->total_validations / $maxVal) * 100);
                    @endphp
                    <tr class="hover:bg-purple-50 transition">
                        <td class="px-4 py-3 text-gray-400 font-bold text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="{{ $i === 0 ? 'rp-gradient' : ($i === 1 ? 'rp-grad-pink' : 'bg-purple-100') }} w-9 h-9 rounded-full flex items-center justify-center text-xs font-black {{ $i < 2 ? 'text-white' : 'text-purple-700' }}">
                                    {{ strtoupper(substr($agent->agent_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $agent->agent_name }}</p>
                                    <div class="rp-bar-bg w-24 mt-1"><div class="rp-bar-fill" style="width:{{ $pct }}%"></div></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-black text-purple-700 text-base">{{ $agent->total_validations }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $agent->physical }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $agent->online }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-pink-600">{{ $agent->total_scans }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-800">
                            {{ number_format($agent->revenue, 2) }} <span class="text-xs text-gray-400">USD</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- 5. STATISTIQUES ÉVÉNEMENTS --}}
<div class="bg-white rounded-2xl border border-purple-100 p-6 mb-6">
    <p class="rp-section-title">Statistiques des événements</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
    {{-- Graphique revenus par événement --}}
    <div class="mb-6">
        <canvas id="chartEvents" height="100"></canvas>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="rp-grad-soft">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-purple-700 rounded-l-xl">Événement</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Créés</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Validés</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700">Taux</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-purple-700 rounded-r-xl">Revenus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-purple-50">
                @foreach($reportData['eventDetails'] as $ev)
                @php $evRate = $ev->tickets_created > 0 ? ($ev->tickets_validated / $ev->tickets_created) * 100 : 0; @endphp
                <tr class="hover:bg-purple-50 transition">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $ev->title }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $ev->tickets_created }}</td>
                    <td class="px-4 py-3 text-right font-bold text-purple-700">{{ $ev->tickets_validated }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $evRate >= 50 ? 'bg-purple-100 text-purple-700' : 'bg-pink-100 text-pink-700' }}">
                            {{ number_format($evRate, 1) }} %
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">{{ number_format($ev->event_revenue, 2) }} <span class="text-xs text-gray-400">USD</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Footer --}}
<div class="rp-gradient rounded-2xl p-4 text-center text-white text-xs opacity-80 mb-2">
    Rapport généré automatiquement par NLC Events Admin &nbsp;·&nbsp; {{ now()->format('d/m/Y à H:i') }}
</div>

{{-- Scripts Chart.js --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const purple  = '#7c3aed';
    const violet  = '#a855f7';
    const pink    = '#ec4899';
    const rose    = '#f43f5e';
    const light   = '#ede9fe';
    const gradColors = [purple, violet, pink, rose, '#6366f1', '#8b5cf6'];

    Chart.defaults.font.family = 'system-ui, sans-serif';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    Chart.defaults.plugins.legend.labels.padding  = 16;

    // ── 1. Donut statuts ──────────────────────────────────────────
    const confirmed = {{ $reportData['confirmed'] }};
    const pending   = {{ $reportData['pending'] }};
    const other     = Math.max(0, {{ $reportData['totalTickets'] }} - confirmed - pending);

    new Chart(document.getElementById('chartStatuts'), {
        type: 'doughnut',
        data: {
            labels: ['Confirmés', 'En attente', 'Autres'],
            datasets: [{
                data: [confirmed, pending, other],
                backgroundColor: [purple, pink, '#e5e7eb'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: ctx => ` ${ctx.label}: ${ctx.parsed}`
            }}},
        }
    });

    // ── 2. Barres canaux ─────────────────────────────────────────
    new Chart(document.getElementById('chartCanaux'), {
        type: 'bar',
        data: {
            labels: ['Physique', 'En ligne'],
            datasets: [
                { label: 'Créés',   data: [{{ $reportData['physicalTotal'] }}, {{ $reportData['onlineTotal'] }}],     backgroundColor: light,  borderRadius: 6 },
                { label: 'Validés', data: [{{ $reportData['physicalValidated'] }}, {{ $reportData['onlineValidated'] }}], backgroundColor: purple, borderRadius: 6 },
                { label: 'Revenus (USD)', data: [{{ $reportData['physicalRevenue'] }}, {{ $reportData['onlineRevenue'] }}], backgroundColor: pink, borderRadius: 6, yAxisID: 'y2' },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y:  { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af' } },
                y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#ec4899', callback: v => v + ' $' } },
                x:  { grid: { display: false }, ticks: { color: '#6b7280' } }
            }
        }
    });

    // ── 3. Barres groupées agents ─────────────────────────────────
    @if($reportData['agentActivity']->isNotEmpty())
    const agentLabels = {!! json_encode($reportData['agentActivity']->pluck('agent_name')->toArray()) !!};
    const agentValid  = {!! json_encode($reportData['agentActivity']->pluck('total_validations')->toArray()) !!};
    const agentScans  = {!! json_encode($reportData['agentActivity']->pluck('total_scans')->toArray()) !!};
    const agentRev    = {!! json_encode($reportData['agentActivity']->pluck('revenue')->map(fn($v) => round((float)$v, 2))->toArray()) !!};

    new Chart(document.getElementById('chartAgents'), {
        type: 'bar',
        data: {
            labels: agentLabels,
            datasets: [
                {
                    label: 'Validations',
                    data: agentValid,
                    backgroundColor: agentLabels.map((_, i) => gradColors[i % gradColors.length]),
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Scans QR',
                    data: agentScans,
                    backgroundColor: agentLabels.map((_, i) => gradColors[i % gradColors.length] + '55'),
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Revenus (USD)',
                    data: agentRev,
                    type: 'line',
                    borderColor: pink,
                    backgroundColor: 'transparent',
                    pointBackgroundColor: pink,
                    pointRadius: 5,
                    tension: 0.4,
                    yAxisID: 'yRev',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y:    { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af' } },
                yRev: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#ec4899', callback: v => v + ' $' } },
                x:    { grid: { display: false }, ticks: { color: '#6b7280' } }
            }
        }
    });
    @endif

    // ── 4. Barres revenus par événement ───────────────────────────
    @if($reportData['eventDetails']->isNotEmpty())
    const evLabels  = {!! json_encode($reportData['eventDetails']->pluck('title')->toArray()) !!};
    const evCreated = {!! json_encode($reportData['eventDetails']->pluck('tickets_created')->toArray()) !!};
    const evValid   = {!! json_encode($reportData['eventDetails']->pluck('tickets_validated')->toArray()) !!};
    const evRev     = {!! json_encode($reportData['eventDetails']->pluck('event_revenue')->map(fn($v) => round((float)$v, 2))->toArray()) !!};

    new Chart(document.getElementById('chartEvents'), {
        type: 'bar',
        data: {
            labels: evLabels,
            datasets: [
                { label: 'Tickets créés',  data: evCreated, backgroundColor: light,  borderRadius: 6 },
                { label: 'Validés',        data: evValid,   backgroundColor: purple, borderRadius: 6 },
                { label: 'Revenus (USD)',   data: evRev,     type: 'line', borderColor: pink,
                  backgroundColor: 'transparent', pointBackgroundColor: pink, pointRadius: 5, tension: 0.4, yAxisID: 'yRev2' }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y:     { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af' } },
                yRev2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#ec4899', callback: v => v + ' $' } },
                x:     { grid: { display: false }, ticks: { color: '#6b7280' } }
            }
        }
    });
    @endif
});
</script>

@endif {{-- fin @if($reportGenerated) --}}
