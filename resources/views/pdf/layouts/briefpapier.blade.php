<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'PDF Dokument')</title>
    <style>
        /* Footer-Text wird als Grafik angezeigt */

        @page {
            size: A4;
            margin: @yield('page-margin', '2cm 2cm 2.5cm');
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

        .logo-fixed {
            position: fixed;
            top: -1.5cm;
            /* 2cm page margin - 0.5cm gewünschter Abstand = -1.5cm */
            left: -1.5cm;
            /* 2cm page margin - 0.5cm gewünschter Abstand = -1.5cm */
            z-index: 1000;
            width: auto;
            height: 2cm;
        }

        .page-header {
            @yield('page-header-style', '')
        }

        .page-footer {
            position: fixed;
            bottom: @yield('footer-bottom', '-2cm');
            left: 0;
            right: 0;
            width: 17cm;
            height: 30px;
            font-size: 10px;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 0px;
            text-align: center;
            text-transform: lowercase;
        }

        /* Footer-Name wird als Grafik angezeigt - kein CSS mehr nötig */

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
            margin-top: @yield('content-margin-top', '3cm');
            /* Dynamischer Platz für Logo oben */
            margin-bottom: 0cm;
        }

        /* Rechnung-spezifische Styles */
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
            background-color: #0655a3;
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

        /* Briefpapier für Folgeseiten */
        .page-break {
            page-break-before: always;
            position: relative;
        }

        .page-rest-background {
            position: fixed;
            top: -2cm;
            left: -2cm;
            width: calc(100% + 4cm);
            height: calc(100% + 4.5cm);
            z-index: -1;
            overflow: hidden;
        }

        .page-rest-background img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @yield('additional-styles')
    </style>
</head>

<body>
    @section('header')
    <!-- Logo auf allen Seiten -->
    @if(file_exists(public_path('images/vff_icon.png')))
    <img src="{{ public_path('images/vff_icon.png') }}" alt="Logo" class="logo-fixed">
    @endif
    @show

    @section('kontaktdaten')
    <!-- Kontaktdaten über Footer (optional) -->
    @if(View::hasSection('show-kontaktdaten'))
    <div style="position: fixed; bottom: -1cm; left: 0; right: 0; width: 17cm;">
        <table style="width: 100%; font-size: 6pt; color: #ff0000; border-collapse: collapse; letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 10px;">
            <tr>
                <td style="width: 25%; vertical-align: top; padding: 0; border: none;">
                    <strong>Fon</strong> +49 (0) 8141/ 66 65-0<br>
                    <strong>Fax</strong> +49 (0) 8141/ 66 65-333<br>
                    veranstaltungsforum@fuerstenfeld.de
                </td>
                <td style="width: 25%; vertical-align: top; padding: 0; border: none;">
                    <strong>Leitung:</strong> Norbert Leinweber<br>
                    <strong>St.-Nr.</strong> 117/11470095<br>
                    <strong>Ust-ID-Nr.</strong> DE128255139
                </td>
                <td style="width: 25%; vertical-align: top; padding: 0; border: none;">
                    <strong>Sparkasse Fürstenfeldbruck</strong><br>
                    Swift Code (BIC): BYLADEM1FFB<br>
                    IBAN: DE 31700530700001433333
                </td>
                <td style="width: 25%; vertical-align: top; padding: 0; border: none;">
                    <strong>Volksbank Fürstenfeldbruck</strong><br>
                    Swift Code (BIC): GENODEF1FFB<br>
                    IBAN: DE 48701633700000087777
                </td>
            </tr>
        </table>
    </div>
    @endif
    @show

    @section('footer')
    <!-- Footer für alle Seiten -->
    <div class="page-footer">
        @php
        $stammdaten = [
        'allgemein' => [
        'name' => 'Veranstaltungsforum Fürstenfeld',
        'strasse' => 'Fürstenfeld 12',
        'plz' => '82256',
        'ort' => 'Fürstenfeldbruck',
        'web' => 'www.fuerstenfeld.de'
        ]
        ];
        @endphp
        @if(file_exists(public_path('images/footer-text.png')))
        <img src="{{ "data:image/png;base64," . base64_encode(file_get_contents(public_path("images/footer-text.png"))) }}"
            alt="Veranstaltungsforum Fürstenfeld"
            style="height: 14px; vertical-align: middle; margin-top: 22px;">
        @else
        <strong>{{ $stammdaten['allgemein']['name'] }}</strong>
        @endif
        · {{ $stammdaten['allgemein']['strasse'] }} · {{ $stammdaten['allgemein']['plz'] }} {{ $stammdaten['allgemein']['ort'] }} · {{ $stammdaten['allgemein']['web'] }}
        @yield('footer-additional')
    </div>
    @show

    <!-- Hauptinhalt -->
    <div class="content">
        @yield('content')
    </div>

</body>

</html>