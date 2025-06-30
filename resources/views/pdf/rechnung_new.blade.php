<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rechnung {{ $rechnung->rechnungsnummer }}</title>
    <style>
        @page {
            size: A4;
            margin: 3cm 2cm 2cm;
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
        }

        .page-container-table {
            width: 100%;
            border-collapse: collapse;
        }

        .page-header {
            position: fixed;
            top: -2cm;
            right: -2cm;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
        }

        .page-footer {
            position: fixed;
            bottom: -1cm;
            width: 17cm;
            font-size: 9px;
            color: #666;
            line-height: 10px;
        }

        .page-header-space {
            height: 2.5cm;
        }

        .page-footer-space {
            height: 2cm;
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
        }

        .content {
            margin-top: 0cm;
            margin-bottom: 0cm;
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
    <div class="page-header">
        <img src="{{ public_path('images/vff_icon.png') }}" alt="Logo" class="logo">
    </div>

    <div class="page-footer">
        <b>{{ $stammdaten['allgemein']['name'] }}</b> · {{ $stammdaten['allgemein']['abteilung'] }} · {{ $stammdaten['ansprechpartner']['name'] }}
        <br>{{ $stammdaten['allgemein']['strasse'] }} · {{ $stammdaten['allgemein']['plz'] }} {{ $stammdaten['allgemein']['ort'] }} · Tel. {{ $stammdaten['ansprechpartner']['telefon'] }}
        <br>E-Mail: {{ $stammdaten['ansprechpartner']['email'] }} · {{ $stammdaten['allgemein']['web'] }}
        <br>Werkleitung: {{ $stammdaten['allgemein']['leitung'] }} · Sitz: {{ $stammdaten['allgemein']['sitz'] }}; Registergericht: {{ $stammdaten['allgemein']['registergericht'] }}
    </div>

    <table class="page-container-table">
        <thead>
            <tr>
                <td>
                    <div class="page-header-space">&nbsp;</div>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="content">
                        <h1 style="text-align: center;">Rechnung {{ $rechnung->rechnungsnummer }}</h1>

                        <div class="section">
                            <div class="section-title">Rechnungsdetails</div>
                            <table>
                                <tr>
                                    <th>Rechnungsnummer</th>
                                    <td>{{ $rechnung->rechnungsnummer }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
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
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Rechnungsdatum</th>
                                    <td>{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</td>
                                </tr>
                                @if($rechnung->lieferdatum)
                                <tr>
                                    <th>Lieferdatum</th>
                                    <td>{{ $rechnung->lieferdatum->format('d.m.Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Fällig bis</th>
                                    <td>{{ $rechnung->faelligkeitsdatum->format('d.m.Y') }}</td>
                                </tr>
                                @if($rechnung->buchung_id)
                                <tr>
                                    <th>Buchung</th>
                                    <td>#{{ $rechnung->buchung_id }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Betreff</th>
                                    <td>{{ $rechnung->betreff }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="section">
                            <div class="section-title">Rechnungsempfänger</div>
                            <table>
                                @if($rechnung->empf_firma)
                                <tr>
                                    <th>Firma</th>
                                    <td>{{ $rechnung->empf_firma }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Name</th>
                                    <td>
                                        @if($rechnung->empf_anrede){{ $rechnung->empf_anrede }} @endif
                                        {{ $rechnung->empf_vorname }} {{ $rechnung->empf_name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Adresse</th>
                                    <td>
                                        {{ $rechnung->empf_strasse }}@if($rechnung->empf_hausnummer) {{ $rechnung->empf_hausnummer }}@endif<br>
                                        {{ $rechnung->empf_plz }} {{ $rechnung->empf_ort }}
                                        @if($rechnung->empf_land && $rechnung->empf_land !== 'Deutschland')<br>{{ $rechnung->empf_land }}@endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>E-Mail</th>
                                    <td>{{ $rechnung->empf_email }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Anschreiben -->
                        @if($rechnung->anschreiben)
                        <div class="section">
                            <div class="section-title">Anschreiben</div>
                            <p>{{ $rechnung->anschreiben }}</p>
                        </div>
                        @endif

                        <!-- Status-Alert für bezahlte Rechnungen -->
                        @if($rechnung->status === 'paid' && $rechnung->bezahlt_am)
                        <div class="section">
                            <div style="padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                                <strong>✓ Diese Rechnung wurde am {{ $rechnung->bezahlt_am->format('d.m.Y') }} vollständig bezahlt.</strong>
                            </div>
                        </div>
                        @endif

                        @if($rechnung->positionen->count() > 0)
                        <div class="section">
                            <div class="section-title">Rechnungspositionen</div>
                            <table>
                                <tr>
                                    <th>Pos.</th>
                                    <th>Bezeichnung</th>
                                    <th>Menge</th>
                                    <th>Einzelpreis</th>
                                    <th>MwSt</th>
                                    <th>Gesamtpreis</th>
                                </tr>
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
                        </div>
                        @endif

                        <!-- Zahlungsinformationen -->
                        @if($rechnung->status !== 'paid')
                        <div class="section">
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
                        </div>
                        @endif

                        <!-- Schlussschreiben -->
                        @if($rechnung->schlussschreiben)
                        <div class="section">
                            <div class="schlussschreiben">{{ $rechnung->schlussschreiben }}</div>
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <div class="page-footer-space">&nbsp;</div>
                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>