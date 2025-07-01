<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tägliche Anfragen-Übersicht</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
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
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 28px;
        }

        .header p {
            color: #6b7280;
            font-size: 16px;
            margin: 10px 0 0 0;
        }

        .summary {
            background-color: #f0f9ff;
            border: 2px solid #3b82f6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .summary h2 {
            margin: 0;
            color: #1e40af;
            font-size: 24px;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #3b82f6;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 12px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .status-neu {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-importiert {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin: 2px;
        }

        .btn:hover {
            background-color: #2563eb;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .no-anfragen {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 40px 20px;
            background-color: #f9fafb;
            border-radius: 6px;
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
            <h1>Tägliche Anfragen-Übersicht</h1>
            <p>{{ $datum->format('d.m.Y') }}</p>
        </div>

        <div class="summary">
            <h2>{{ $gesamtAnzahl }} neue Anfrage{{ $gesamtAnzahl != 1 ? 'n' : '' }}</h2>
        </div>

        @if($gesamtAnzahl > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Firma</th>
                        <th>E-Mail</th>
                        <th>Markt</th>
                        <th>Eingereicht</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($neueAnfragen as $anfrage)
                    <tr>
                        <td>{{ $anfrage->vorname }} {{ $anfrage->nachname }}</td>
                        <td>{{ $anfrage->firma ?? '-' }}</td>
                        <td>{{ $anfrage->email }}</td>
                        <td>{{ $anfrage->markt->name ?? 'Unbekannt' }}</td>
                        <td>{{ $anfrage->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('filament.admin.resources.anfrage.view', $anfrage->id) }}" class="btn">
                                Ansehen
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="no-anfragen">
            <p>Gestern wurden keine neuen Anfragen eingereicht.</p>
        </div>
        @endif

        <div class="actions">
            <a href="{{ route('filament.admin.resources.anfrage.index') }}" class="btn">
                Alle Anfragen anzeigen
            </a>
        </div>

        <div class="footer">
            <p>Viele Grüße,<br>Ihr Markt-Verwaltungssystem</p>
        </div>
    </div>
</body>

</html>