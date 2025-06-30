<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rechnung {{ $rechnung->rechnungsnummer }}</title>
    <style>
        @page {
            size: A4;
            margin: 2cm 2cm 2.5cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .logo {
            width: auto;
            height: 2cm;
            float: right;
            margin-bottom: 20px;
        }

        .page-footer {
            position: fixed;
            bottom: -2cm;
            left: 0;
            right: 0;
            width: 17cm;
            font-size: 9px;
            color: #666;
            line-height: 10px;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .content {
            margin-top: 0cm;
            margin-bottom: 0cm;
        }

        .rechnung-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .rechnung-info {
            text-align: right;
            font-size: 16px;
        }

        .rechnung-nummer {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .datum-info {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .empfaenger {
            font-size: 12px;
            line-height: 1.3;
        }

        .betreff {
            font-size: 14px;
            font-weight: bold;
            margin: 30px 0 20px 0;
        }

        .anschreiben {
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .positionen-table {
            margin-top: 20px;
        }

        .positionen-table th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }

        .summen-table {
            width: 60%;
            margin-left: auto;
            margin-top: 20px;
        }

        .summen-table td {
            border-bottom: 1px solid #ddd;
            padding: 5px 10px;
        }

        .summen-table .total-row {
            font-weight: bold;
            background-color: #ecf0f1;
            border-top: 2px solid #34495e;
        }

        .zahlungsinfo {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #34495e;
            page-break-inside: avoid;
        }

        .schlussschreiben {
            margin-top: 15px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background-color: #95a5a6;
            color: white;
        }

        .status-sent {
            background-color: #f39c12;
            color: white;
        }

        .status-paid {
            background-color: #27ae60;
            color: white;
        }

        .status-overdue {
            background-color: #e74c3c;
            color: white;
        }

        .status-canceled {
            background-color: #e74c3c;
            color: white;
        }

        .status-partial {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Footer für alle Seiten -->
    <div class="page-footer">
        @php
        $stammdaten = [
        'allgemein' => [
        'name' => 'Veranstaltungsforum Fürstenfeld',
        'abteilung' => 'Marktorganisation',
        'strasse' => 'Fürstenfeld 12',
        'plz' => '82256',
        'ort' => 'Fürstenfeldbruck'
        ],
        'ansprechpartner' => [
        'name' => 'Michaela Landmann',
        'telefon' => '+49 8141 123456',
        'email' => 'info@fuerstenfeld.de'
        ]
        ];
        @endphp
        <b>{{ $stammdaten['allgemein']['name'] }}</b> · {{ $stammdaten['allgemein']['abteilung'] }} · {{ $stammdaten['ansprechpartner']['name'] }}
        <br>{{ $stammdaten['allgemein']['strasse'] }} · {{ $stammdaten['allgemein']['plz'] }} {{ $stammdaten['allgemein']['ort'] }} · Tel. {{ $stammdaten['ansprechpartner']['telefon'] }}
        <br>E-Mail: {{ $stammdaten['ansprechpartner']['email'] }}
    </div>

    <!-- Hauptinhalt -->
    <div class="content">
        <!-- Logo -->
        @if(file_exists(public_path('images/vff_icon.png')))
        <img src="{{ public_path('images/vff_icon.png') }}" alt="Logo" class="logo">
        @endif

        <div style="clear: both;"></div>

        <!-- Rechnungsheader -->
        <table style="margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; border: none; vertical-align: top;">
                    <div class="empfaenger">
                        <strong>Rechnungsempfänger:</strong><br>
                        @if($rechnung->empf_firma)
                        {{ $rechnung->empf_firma }}<br>
                        @endif
                        @if($rechnung->empf_anrede)
                        {{ $rechnung->empf_anrede }}
                        @endif
                        {{ $rechnung->empf_vorname }} {{ $rechnung->empf_name }}<br>
                        {{ $rechnung->empf_strasse }}
                        @if($rechnung->empf_hausnummer) {{ $rechnung->empf_hausnummer }}@endif<br>
                        {{ $rechnung->empf_plz }} {{ $rechnung->empf_ort }}<br>
                        @if($rechnung->empf_land && $rechnung->empf_land !== 'Deutschland')
                        {{ $rechnung->empf_land }}<br>
                        @endif
                    </div>
                </td>
                <td style="width: 50%; border: none; text-align: right; vertical-align: top;">
                    <div class="rechnung-info">
                        <div class="rechnung-nummer">Rechnung {{ $rechnung->rechnungsnummer }}</div>
                        <span class="status-badge status-{{ $rechnung->status }}">
                            {{ match($rechnung->status) {
                                'draft' => 'Entwurf',
                                'sent' => 'Versendet',
                                'paid' => 'Bezahlt',
                                'overdue' => 'Überfällig',
                                'canceled' => 'Storniert',
                                'partial' => 'Teilweise bezahlt',
                                default => $rechnung->status
                            } }}
                        </span><br><br>

                        <div class="datum-info"><strong>Rechnungsdatum:</strong> {{ $rechnung->rechnungsdatum->format('d.m.Y') }}</div>
                        @if($rechnung->lieferdatum)
                        <div class="datum-info"><strong>Lieferdatum:</strong> {{ $rechnung->lieferdatum->format('d.m.Y') }}</div>
                        @endif
                        <div class="datum-info"><strong>Fällig bis:</strong> {{ $rechnung->faelligkeitsdatum->format('d.m.Y') }}</div>
                        @if($rechnung->buchung_id)
                        <div class="datum-info"><strong>Buchung:</strong> #{{ $rechnung->buchung_id }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Betreff -->
        <div class="betreff">{{ $rechnung->betreff }}</div>

        <!-- Anschreiben -->
        @if($rechnung->anschreiben)
        <div class="anschreiben">{{ $rechnung->anschreiben }}</div>
        @endif

        <!-- Rechnungspositionen -->
        <table class="positionen-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Pos.</th>
                    <th style="width: 40%;">Bezeichnung</th>
                    <th style="width: 12%;">Menge</th>
                    <th style="width: 15%;">Einzelpreis</th>
                    <th style="width: 10%;">MwSt</th>
                    <th style="width: 15%;">Gesamtpreis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rechnung->positionen as $position)
                <tr>
                    <td style="text-align: center;">{{ $position->position }}</td>
                    <td>
                        <strong>{{ $position->bezeichnung }}</strong>
                        @if($position->beschreibung)
                        <br><small>{{ $position->beschreibung }}</small>
                        @endif
                    </td>
                    <td>{{ number_format($position->menge, 2, ',', '.') }} {{ $position->einheit ?? 'Stk' }}</td>
                    <td>{{ number_format($position->einzelpreis, 2, ',', '.') }} €</td>
                    <td>{{ number_format($position->steuersatz, 1, ',', '.') }}%</td>
                    <td>{{ number_format($position->bruttobetrag, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summen -->
        <table class="summen-table">
            <tr>
                <td><strong>Zwischensumme (netto):</strong></td>
                <td style="text-align: right;">{{ number_format($rechnung->positionen->sum('nettobetrag'), 2, ',', '.') }} €</td>
            </tr>
            @if($rechnung->gesamtrabatt_betrag > 0)
            <tr>
                <td>Rabatt ({{ number_format($rechnung->gesamtrabatt_prozent, 1, ',', '.') }}%):</td>
                <td style="text-align: right;">-{{ number_format($rechnung->gesamtrabatt_betrag, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td><strong>Nettobetrag:</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($rechnung->nettobetrag, 2, ',', '.') }} €</strong></td>
            </tr>
            @endif
            <tr>
                <td>MwSt (19%):</td>
                <td style="text-align: right;">{{ number_format($rechnung->steuerbetrag, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total-row">
                <td><strong>Rechnungsbetrag:</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($rechnung->bruttobetrag, 2, ',', '.') }} €</strong></td>
            </tr>
            @if($rechnung->bezahlter_betrag > 0)
            <tr>
                <td>Bereits bezahlt:</td>
                <td style="text-align: right;">{{ number_format($rechnung->bezahlter_betrag, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total-row">
                <td><strong>Offener Betrag:</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($rechnung->bruttobetrag - $rechnung->bezahlter_betrag, 2, ',', '.') }} €</strong></td>
            </tr>
            @endif
        </table>

        <!-- Status-Alert für bezahlte Rechnungen -->
        @if($rechnung->status === 'paid' && $rechnung->bezahlt_am)
        <div style="margin-top: 20px; margin-bottom: 20px; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
            <strong>✓ Diese Rechnung wurde am {{ $rechnung->bezahlt_am->format('d.m.Y') }} vollständig bezahlt.</strong>
        </div>
        @endif

        <!-- Zahlungsinformationen -->
        @if($rechnung->status !== 'paid')
        <div class="zahlungsinfo">
            <strong>Zahlungsinformationen:</strong><br>
            @if($rechnung->zahlungsziel)
            Zahlungsziel: {{ $rechnung->zahlungsziel }}<br>
            @endif
            Bitte überweisen Sie den Rechnungsbetrag unter Angabe der Rechnungsnummer {{ $rechnung->rechnungsnummer }} auf unser Konto.<br>
            <br>
            <strong>Bankverbindung:</strong><br>
            Veranstaltungsforum Fürstenfeld<br>
            IBAN: DE12 7005 1540 0280 8173 47<br>
            BIC: BYLADEM1FFB<br>
            Sparkasse Fürstenfeldbruck
        </div>
        @endif

        <!-- Schlussschreiben -->
        @if($rechnung->schlussschreiben)
        <div class="schlussschreiben">{{ $rechnung->schlussschreiben }}</div>
        @endif
    </div>

</body>

</html>