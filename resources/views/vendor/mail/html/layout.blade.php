<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        /* Template-spezifische Styles für Datenbank-Templates */
        .template-content {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .template-content h1,
        .template-content h2,
        .template-content h3 {
            color: #2d3748;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .template-content p {
            margin-bottom: 10px;
        }

        .template-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .template-content table th,
        .template-content table td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .template-content table th {
            background-color: #f7fafc;
            font-weight: bold;
        }
    </style>
    {!! $head ?? '' !!}
</head>

<body>

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td align="center">
                            <img src="data:image/png;base64,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" alt="Markt Logo" style="height: 60px; margin: 20px 0;">
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                        {!! $subcopy ?? '' !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Immer Standard Footer mit Stammdaten -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">
                                        @if(isset($stammdaten))
                                        <p style="text-align: left; font-size: 0.7em;">
                                            <b>{{ $stammdaten['allgemein']['name'] }}</b> · {{ $stammdaten['allgemein']['abteilung'] }} · {{ $stammdaten['ansprechpartner']['name'] }}
                                            <br>{{ $stammdaten['allgemein']['strasse'] }} · {{ $stammdaten['allgemein']['plz'] }} {{ $stammdaten['allgemein']['ort'] }} · Tel. <a href="tel:{{ preg_replace('/\D+/', '', $stammdaten['ansprechpartner']['telefon']) }}">{{ $stammdaten['ansprechpartner']['telefon'] }}</a>
                                            <br>E-Mail: <a href="mailto:{{ $stammdaten['ansprechpartner']['email'] }}">{{ $stammdaten['ansprechpartner']['email'] }}</a> · <a href="{{ $stammdaten['allgemein']['web'] }}">{{ $stammdaten['allgemein']['web'] }}</a>
                                            <br>Werkleitung: {{ $stammdaten['allgemein']['leitung'] }} · Sitz: {{ $stammdaten['allgemein']['sitz'] }}; Registergericht: {{ $stammdaten['allgemein']['registergericht'] }}
                                        </p>
                                        @else
                                        <p style="text-align: center; font-size: 0.7em; color: #b0adc5;">
                                            © {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.<br>
                                            Bei Fragen wenden Sie sich gerne an uns.
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>