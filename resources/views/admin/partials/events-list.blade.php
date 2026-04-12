<style>
@media print {
    body > * { display: none !important; }
    #qr-print-area { display: block !important; }
    #qr-print-area * { display: revert !important; }
    #qr-print-area {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        padding: 10mm;
    }
    #qr-output {
        display: flex !important;
        flex-wrap: wrap;
        gap: 12px;
    }
    .qr-item {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        page-break-inside: avoid;
        border: 1px solid #e5e7eb;
        padding: 8px;
        border-radius: 6px;
    }
    .qr-item p {
        font-size: 9px;
        font-family: monospace;
        margin-top: 4px;
        text-align: center;
    }
}
</style>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- GÉNÉRATEUR DE QR CODES PHYSIQUES                               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">Générateur de QR Codes Physiques</h3>
            <p class="text-sm text-gray-500">Générez des QR codes pour les billets physiques à distribuer</p>
        </div>
    </div>

    <div class="p-6">
        <!-- Formulaire de génération -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Sélection événement -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Événement</label>
                <select id="qr-event-select"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">— Sélectionner un événement —</option>
                    @foreach($events as $evt)
                        <option value="{{ $evt->id }}">{{ $evt->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quantité -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                <input type="number" id="qr-quantity"
                    min="1" max="200" value="10"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <p class="text-xs text-gray-400 mt-1">Max 200 par génération</p>
            </div>

            <!-- Info format -->
            <div class="flex items-end">
                <div class="w-full p-3 bg-indigo-50 border border-indigo-200 rounded-lg text-xs text-indigo-700">
                    <p class="font-semibold mb-1">Format généré :</p>
                    <p class="font-mono">PHY-{timestamp}-{code}</p>
                    <p class="mt-1 text-indigo-500">Compatible app mobile</p>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-wrap gap-3 mb-6">
            <button onclick="generateQRCodes()"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Générer les QR codes
            </button>

            <button onclick="printQRCodes()" id="btn-print" style="display:none"
                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>

            <button onclick="clearQRCodes()" id="btn-clear" style="display:none"
                class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-lg font-medium text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Effacer
            </button>
        </div>

        <!-- Zone d'info / erreur -->
        <div id="qr-info" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

        <!-- Zone d'affichage des QR codes (aussi utilisée pour l'impression) -->
        <div id="qr-print-area">
            <div id="qr-output" class="flex flex-wrap gap-4"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- LISTE DES ÉVÉNEMENTS                                           -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-800">Liste des événements</h3>
    </div>

    <!-- Filtres -->
    <div class="p-6 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex gap-4">
            <input type="hidden" name="tab" value="events">

            <input
                type="text"
                name="events_search"
                value="{{ request('events_search') }}"
                placeholder="Rechercher un événement..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Rechercher
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billets</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarifs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($eventsList as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $event->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->location }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->tickets_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->event_prices_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucun événement trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($eventsList->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $eventsList->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- qrcode.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
function randomAlphaNum(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

function generateQRCodes() {
    const eventSelect = document.getElementById('qr-event-select');
    const eventId     = eventSelect.value;
    const eventTitle  = eventSelect.options[eventSelect.selectedIndex]?.text || '';
    const quantity    = parseInt(document.getElementById('qr-quantity').value, 10);
    const output      = document.getElementById('qr-output');
    const info        = document.getElementById('qr-info');

    // Validation
    if (!eventId) {
        showInfo('Veuillez sélectionner un événement.', 'error');
        return;
    }
    if (isNaN(quantity) || quantity < 1 || quantity > 200) {
        showInfo('La quantité doit être entre 1 et 200.', 'error');
        return;
    }

    // Nettoyer la sortie précédente
    output.innerHTML = '';
    info.classList.add('hidden');

    const now = new Date().toISOString();

    // Générer les QR codes
    for (let i = 0; i < quantity; i++) {
        const timestamp = Date.now() + i; // unique par itération
        const physicalId = 'PHY-' + timestamp + '-' + randomAlphaNum(9);

        const qrData = JSON.stringify({
            id: physicalId,
            event_id: eventId,
            type: 'physical_ticket',
            created_at: now
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'qr-item flex flex-col items-center p-3 border border-gray-200 rounded-lg bg-white';

        const qrDiv = document.createElement('div');
        wrapper.appendChild(qrDiv);

        const label = document.createElement('p');
        label.className = 'text-xs font-mono text-gray-700 mt-2 text-center break-all';
        label.textContent = physicalId;
        wrapper.appendChild(label);

        const sublabel = document.createElement('p');
        sublabel.className = 'text-xs text-gray-400 mt-1 text-center';
        sublabel.textContent = 'Billet Physique';
        wrapper.appendChild(sublabel);

        output.appendChild(wrapper);

        new QRCode(qrDiv, {
            text: qrData,
            width: 128,
            height: 128,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    // Afficher les boutons d'action
    document.getElementById('btn-print').style.display = 'inline-flex';
    document.getElementById('btn-clear').style.display = 'inline-flex';

    showInfo(quantity + ' QR code(s) générés pour <strong>' + eventTitle + '</strong>', 'success');
}

function printQRCodes() {
    window.print();
}

function clearQRCodes() {
    document.getElementById('qr-output').innerHTML = '';
    document.getElementById('btn-print').style.display = 'none';
    document.getElementById('btn-clear').style.display = 'none';
    document.getElementById('qr-info').classList.add('hidden');
}

function showInfo(message, type) {
    const info = document.getElementById('qr-info');
    info.innerHTML = message;
    info.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'border', 'border-green-200',
                                    'bg-red-50',   'text-red-800',   'border-red-200');
    if (type === 'success') {
        info.classList.add('bg-green-50', 'text-green-800', 'border', 'border-green-200');
    } else {
        info.classList.add('bg-red-50', 'text-red-800', 'border', 'border-red-200');
    }
}
</script>
