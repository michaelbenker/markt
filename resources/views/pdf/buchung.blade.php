<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Anmeldebestätigung</title>
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

    <div class="content">
        <h1 style="text-align: center;">Anmeldebestätigung</h1>

        <div class="section">
            <div class="section-title">Buchungsdetails</div>
            <table>
                <tr>
                    <th>Buchungsnummer</th>
                    <td>{{ $buchung->id }}</td>
                </tr>
                <tr>
                    <th>Markt & Termin</th>
                    <td>
                        {{ $buchung->termin->markt->name }}<br>
                        @php
                        $startDate = \Carbon\Carbon::parse($buchung->termin->start);
                        $endDate = \Carbon\Carbon::parse($buchung->termin->ende);

                        if ($startDate->format('m') === $endDate->format('m')) {
                        // Gleicher Monat
                        echo $startDate->format('d.') . '-' . $endDate->format('d.m.Y');
                        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
                        // Gleiches Jahr, aber unterschiedlicher Monat
                        echo $startDate->format('d.m.') . '-' . $endDate->format('d.m.Y');
                        } else {
                        // Unterschiedliche Jahre
                        echo $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
                        }
                        @endphp
                    </td>
                </tr>
                <tr>
                    <th>Standort</th>
                    <td>{{ $buchung->standort->name }}</td>
                </tr>
                <tr>
                    <th>Standplatz</th>
                    <td>{{ $buchung->standplatz }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Aussteller</div>
            <table>
                @if($buchung->aussteller->firma)
                <tr>
                    <th>Firma</th>
                    <td>{{ $buchung->aussteller->firma }}</td>
                </tr>
                @endif
                @if($buchung->aussteller->name || $buchung->aussteller->vorname)
                <tr>
                    <th>Name</th>
                    <td>{{ $buchung->aussteller->name }}, {{ $buchung->aussteller->vorname }}</td>
                </tr>
                @endif
                <tr>
                    <th>Adresse</th>
                    <td>
                        {{ $buchung->aussteller->strasse }}<br>
                        {{ $buchung->aussteller->plz }} {{ $buchung->aussteller->ort }}
                    </td>
                </tr>
                <tr>
                    <th>E-Mail</th>
                    <td>{{ $buchung->aussteller->email }}</td>
                </tr>
                @if($buchung->aussteller->telefon)
                <tr>
                    <th>Telefon</th>
                    <td>{{ $buchung->aussteller->telefon }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if($buchung->stand)
        <div class="section">
            <div class="section-title">Stand</div>
            <table>
                <tr>
                    <th>Art</th>
                    <td>{{ ucfirst($buchung->stand['art']) }}</td>
                </tr>
                <tr>
                    <th>Länge</th>
                    <td>{{ $buchung->stand['laenge'] }} m</td>
                </tr>
                <tr>
                    <th>Fläche</th>
                    <td>{{ $buchung->stand['flaeche'] }} m²</td>
                </tr>
            </table>
        </div>
        @endif

        @if($buchung->warenangebot)
        <div class="section">
            <div class="section-title">Warenangebot</div>
            <table>
                <tr>
                    <th>Angebotene Waren</th>
                    <td>{{ implode(', ', array_map('ucfirst', $buchung->warenangebot)) }}</td>
                </tr>
            </table>
        </div>
        @endif

        @if($buchung->herkunft)
        <div class="section">
            <div class="section-title">Herkunft der Waren</div>
            <table>
                <tr>
                    <th>Eigenfertigung</th>
                    <td>{{ $buchung->herkunft['eigenfertigung'] }}%</td>
                </tr>
                <tr>
                    <th>Industrieware (nicht Entwicklungsland)</th>
                    <td>{{ $buchung->herkunft['industrieware_nicht_entwicklungslaender'] }}%</td>
                </tr>
                <tr>
                    <th>Industrieware (Entwicklungsland)</th>
                    <td>{{ $buchung->herkunft['industrieware_entwicklungslaender'] }}%</td>
                </tr>
            </table>
        </div>
        @endif

        @if($buchung->leistungen->count() > 0)
        <div class="section">
            <div class="section-title">Gebuchte Leistungen</div>
            <table>
                <tr>
                    <th>Leistung</th>
                    <th>Preis</th>
                    <th>Menge</th>
                    <th>Gesamt</th>
                </tr>
                @foreach($buchung->leistungen as $leistung)
                <tr>
                    <td>{{ $leistung->leistung->name }}</td>
                    <td>{{ number_format($leistung->preis / 100, 2) }} €</td>
                    <td>{{ $leistung->menge }}</td>
                    <td>{{ number_format(($leistung->preis * $leistung->menge) / 100, 2) }} €</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3"><strong>Summe</strong></td>
                    <td><strong>{{ number_format($buchung->leistungen->sum(function($l) { return ($l->preis * $l->menge); }) / 100, 2) }} €</strong></td>
                </tr>
            </table>
        </div>
        @endif
    </div>
</body>

</html>