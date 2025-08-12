@extends('pdf.layouts.briefpapier')

@section('title', 'Rechnung ' . $rechnung->rechnungsnummer)

@section('page-margin', '2cm 2cm 2.5cm')

{{-- Logo-Position wird jetzt zentral im Layout definiert --}}

@section('content')
<div style="clear: both;"></div>

<!-- Stammdaten für Fenstercouvert -->
<div style="font-size: 7pt; color: #ff0000; text-transform: lowercase;">
    veranstaltungsforum fürstenfeld · fürstenfeld 12 · 82256 fürstenfeldbruck
</div>

<!-- Rechnungsheader -->
<table style="margin-bottom: 30px;">
    <tr>
        <td style="width: 50%; border: none; vertical-align: top; padding-left: 0;">
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
                @if($rechnung->status === 'canceled')
                <span class="status-badge status-canceled">
                    Storniert
                </span><br><br>
                @endif

                <div class="datum-info"><strong>Rechnungsdatum:</strong> {{ $rechnung->rechnungsdatum->format('d.m.Y') }}</div>
                @if($rechnung->lieferdatum)
                <div class="datum-info"><strong>Lieferdatum:</strong> {{ $rechnung->lieferdatum->format('d.m.Y') }}</div>
                @endif
                <div class="datum-info"><strong>Fällig bis:</strong> {{ $rechnung->faelligkeitsdatum->format('d.m.Y') }}</div>
                @if($rechnung->buchung_id)
                <div class="datum-info"><strong>Buchung:</strong> #{{ $rechnung->buchung_id }}</div>
                @php
                    $buchung = $rechnung->buchung;
                    if ($buchung) {
                        $buchung->load(['markt', 'standort']);
                    }
                @endphp
                @if($buchung && $buchung->markt)
                <div class="datum-info"><strong>Markt:</strong> {{ $buchung->markt->name }}</div>
                @endif
                @if($buchung && $buchung->termine && is_array($buchung->termine) && count($buchung->termine) > 0)
                <div class="datum-info"><strong>Termine:</strong>
                    @php
                        $terminObjekte = \App\Models\Termin::whereIn('id', $buchung->termine)->orderBy('start')->get();
                        $terminStrings = [];
                        foreach ($terminObjekte as $termin) {
                            $startDate = \Carbon\Carbon::parse($termin->start);
                            $endDate = \Carbon\Carbon::parse($termin->ende);
                            
                            if ($startDate->format('m') === $endDate->format('m')) {
                                $terminStrings[] = $startDate->format('d.') . '-' . $endDate->format('d.m.Y');
                            } elseif ($startDate->format('Y') === $endDate->format('Y')) {
                                $terminStrings[] = $startDate->format('d.m.') . '-' . $endDate->format('d.m.Y');
                            } else {
                                $terminStrings[] = $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
                            }
                        }
                        echo implode('<br>', $terminStrings);
                    @endphp
                </div>
                @endif
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
            <td style="text-align: right;">{{ number_format($position->einzelpreis / 100, 2, ',', '.') }} €</td>
            <td style="text-align: right;">{{ number_format(($position->menge * $position->einzelpreis) / 100, 2, ',', '.') }} €</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Summen -->
<table class="summen-table">
    <tr>
        <td><strong>Zwischensumme (netto):</strong></td>
        <td style="text-align: right;">{{ number_format($rechnung->positionen->sum('nettobetrag') / 100, 2, ',', '.') }} €</td>
    </tr>
    @if($rechnung->gesamtrabatt_betrag > 0)
    <tr>
        <td>Rabatt ({{ number_format($rechnung->gesamtrabatt_prozent, 1, ',', '.') }}%):</td>
        <td style="text-align: right;">-{{ number_format($rechnung->gesamtrabatt_betrag / 100, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td><strong>Nettobetrag:</strong></td>
        <td style="text-align: right;"><strong>{{ number_format($rechnung->nettobetrag / 100, 2, ',', '.') }} €</strong></td>
    </tr>
    @endif
    <tr>
        <td>MwSt (19%):</td>
        <td style="text-align: right;">{{ number_format($rechnung->steuerbetrag / 100, 2, ',', '.') }} €</td>
    </tr>
    <tr class="total-row">
        <td><strong>Rechnungsbetrag:</strong></td>
        <td style="text-align: right;"><strong>{{ number_format($rechnung->bruttobetrag / 100, 2, ',', '.') }} €</strong></td>
    </tr>
    @if($rechnung->bezahlter_betrag > 0)
    <tr>
        <td>Bereits bezahlt:</td>
        <td style="text-align: right;">{{ number_format($rechnung->bezahlter_betrag / 100, 2, ',', '.') }} €</td>
    </tr>
    <tr class="total-row">
        <td><strong>Offener Betrag:</strong></td>
        <td style="text-align: right;"><strong>{{ number_format(($rechnung->bruttobetrag - $rechnung->bezahlter_betrag) / 100, 2, ',', '.') }} €</strong></td>
    </tr>
    @endif
</table>

@section('show-kontaktdaten')
@endsection

<!-- Seitenumbruch für Folgeseiten-Briefpapier -->
<div class="page-break">

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
        Bitte überweisen Sie den Rechnungsbetrag unter Angabe der Rechnungsnummer {{ $rechnung->rechnungsnummer }} auf eines unserer Konten.<br>
    </div>
    @endif

    <!-- Schlussschreiben -->
    @if($rechnung->schlussschreiben)
    <div class="schlussschreiben">{{ $rechnung->schlussschreiben }}</div>
    @endif
</div>
@endsection