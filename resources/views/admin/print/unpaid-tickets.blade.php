<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des billets non payés - NLC Events</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #111;
            background: white;
            padding: 20px;
        }

        /* En-tête */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .header-left h1 {
            font-size: 20pt;
            font-weight: bold;
            color: #1d4ed8;
        }

        .header-left p {
            font-size: 9pt;
            color: #555;
            margin-top: 4px;
        }

        .header-right {
            text-align: right;
            font-size: 9pt;
            color: #555;
        }

        .header-right strong {
            display: block;
            font-size: 11pt;
            color: #111;
        }

        /* Statistiques */
        .stats {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-box {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px 16px;
            background: #f8fafc;
        }

        .stat-box .label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-box .value {
            font-size: 18pt;
            font-weight: bold;
            color: #111;
            margin-top: 4px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        thead tr {
            background-color: #1d4ed8;
            color: white;
        }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .td-num {
            width: 30px;
            color: #6b7280;
            font-size: 8.5pt;
        }

        .td-name {
            font-weight: 600;
        }

        .td-ref {
            font-size: 7.5pt;
            color: #6b7280;
            margin-top: 2px;
        }

        .td-amount {
            font-weight: 700;
            color: #dc2626;
            white-space: nowrap;
        }

        /* Pied de page */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            color: #6b7280;
        }

        /* Bouton impression (masqué à l'impression) */
        .print-btn {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .print-btn button {
            background: #1d4ed8;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 11pt;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn button:hover {
            background: #1e40af;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 1.2cm 1.5cm;
            }

            .print-btn { display: none; }

            body { padding: 0; }

            thead { display: table-header-group; }

            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- Bouton imprimer (visible seulement à l'écran) -->
    <div class="print-btn">
        <button onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <!-- En-tête -->
    <div class="header">
        <div class="header-left">
            <h1>NLC Events</h1>
            <p>Liste des billets non payés — à relancer</p>
        </div>
        <div class="header-right">
            <strong>Généré le {{ now()->format('d/m/Y à H:i') }}</strong>
            @if(request('unpaid_search'))
                <span>Filtre : "{{ request('unpaid_search') }}"</span>
            @endif
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-box">
            <div class="label">Total billets non payés</div>
            <div class="value">{{ $unpaidTickets->total() }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Montant total en attente</div>
            <div class="value">{{ number_format($unpaidTickets->sum('amount'), 0, ',', ' ') }} {{ $unpaidTickets->first()?->currency ?? 'USD' }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Personnes sur cette page</div>
            <div class="value">{{ $unpaidTickets->count() }}</div>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th class="td-num">N°</th>
                <th>Nom complet</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Événement</th>
                <th>Montant</th>
                <th>Date création</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unpaidTickets as $index => $ticket)
                <tr>
                    <td class="td-num">{{ ($unpaidTickets->currentPage() - 1) * $unpaidTickets->perPage() + $index + 1 }}</td>
                    <td>
                        <div class="td-name">{{ $ticket->full_name }}</div>
                        <div class="td-ref">Réf : {{ $ticket->reference }}</div>
                    </td>
                    <td>{{ $ticket->phone ?? '—' }}</td>
                    <td>{{ $ticket->email ?? '—' }}</td>
                    <td>{{ $ticket->event->title ?? '—' }}</td>
                    <td class="td-amount">{{ number_format($ticket->amount, 0, ',', ' ') }} {{ $ticket->currency }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 30px; color: #6b7280;">
                        Aucun billet non payé trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <span>NLC Events — Document confidentiel</span>
        <span>Total : {{ $unpaidTickets->total() }} billets non payés</span>
    </div>

</body>
</html>
