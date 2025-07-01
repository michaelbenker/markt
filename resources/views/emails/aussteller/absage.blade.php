<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absage Standanfrage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #dc2626;
            margin: 0;
            font-size: 24px;
        }

        .content {
            margin-bottom: 30px;
        }

        .content p {
            margin-bottom: 15px;
        }

        .highlight {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
        }

        .details {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .details h3 {
            margin-top: 0;
            color: #374151;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Absage Ihrer Standanfrage</h1>
        </div>

        <div class="content">
            <p>
                @if($anfrage->anrede && $anfrage->anrede !== 'Divers')
                Sehr {{ $anfrage->anrede === 'Herr' ? 'geehrter Herr' : 'geehrte Frau' }} {{ $anfrage->nachname }},
                @else
                Sehr geehrte Damen und Herren,
                @endif
            </p>

            <p>vielen Dank für Ihr Interesse an unserem Markt <strong>{{ $anfrage->markt->name ?? 'unserem Markt' }}</strong>.</p>

            <div class="highlight">
                <p><strong>Leider können wir Ihr Angebot nicht berücksichtigen.</strong></p>
            </div>

            <p>Aufgrund der begrenzten Platzkapazität und der hohen Nachfrage können wir Ihnen leider keinen Stand anbieten. Wir bedauern diese Entscheidung sehr.</p>

            <div class="details">
                <h3>Ihre Anfrage im Überblick:</h3>
                <p><strong>Markt:</strong> {{ $anfrage->markt->name ?? 'Unbekannt' }}</p>
                @if($anfrage->markt && $anfrage->markt->termine)
                <p><strong>Termine:</strong>
                    {{ $anfrage->markt->termine->map(fn($t) => \Carbon\Carbon::parse($t->start)->format('d.m.Y'))->join(', ') }}
                </p>
                @endif
                <p><strong>Eingereicht am:</strong> {{ $anfrage->created_at->format('d.m.Y H:i') }}</p>
                @if($anfrage->firma)
                <p><strong>Firma:</strong> {{ $anfrage->firma }}</p>
                @endif
                <p><strong>Warenangebot:</strong> {{ is_array($anfrage->warenangebot) ? implode(', ', $anfrage->warenangebot) : $anfrage->warenangebot }}</p>
            </div>

            <p>Wir möchten Sie gerne über zukünftige Märkte informieren und laden Sie herzlich ein, sich auch in Zukunft bei uns zu bewerben.</p>

            <p>Sollten Sie Fragen haben, stehen wir Ihnen gerne zur Verfügung.</p>

            <p>Wir wünschen Ihnen alles Gute und hoffen auf eine zukünftige Zusammenarbeit.</p>
        </div>

        <div class="footer">
            <p>
                Mit freundlichen Grüßen<br>
                <strong>Ihr Markt-Team</strong>
            </p>
        </div>
    </div>
</body>

</html>