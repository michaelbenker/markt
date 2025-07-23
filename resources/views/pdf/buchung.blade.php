@extends('pdf.layouts.briefpapier')

@section('title', 'Anmeldebestätigung')

@section('page-margin', '2cm 2cm 2.5cm')
@section('content-margin-top', '1cm')

{{-- Logo-Position wird jetzt zentral im Layout definiert --}}

@section('content')
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
            <td>{{ ucfirst($buchung->stand['tiefe'] ?? '') }}</td>
        </tr>
        <tr>
            <th>Länge</th>
            <td>{{ $buchung->stand['laenge'] ?? '' }} m</td>
        </tr>
        <tr>
            <th>Fläche</th>
            <td>{{ $buchung->stand['flaeche'] ?? '' }} m²</td>
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
            <td>{{ $buchung->herkunft['eigenfertigung'] ?? '' }}%</td>
        </tr>
        <tr>
            <th>Industrieware (nicht Entwicklungsland)</th>
            <td>{{ $buchung->herkunft['industrieware_nicht_entwicklungslaender'] ?? '' }}%</td>
        </tr>
        <tr>
            <th>Industrieware (Entwicklungsland)</th>
            <td>{{ $buchung->herkunft['industrieware_entwicklungslaender'] ?? '' }}%</td>
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
        @php
        $summeNetto = $buchung->leistungen->sum(function($l) { return ($l->preis * $l->menge); }) / 100;
        $mwst = $summeNetto * 0.19;
        $summeBrutto = $summeNetto + $mwst;
        @endphp
        <tr>
            <td colspan="3"><strong>Summe (netto)</strong></td>
            <td><strong>{{ number_format($summeNetto, 2) }} €</strong></td>
        </tr>
        <tr>
            <td colspan="3">zzgl. 19% MwSt.</td>
            <td>{{ number_format($mwst, 2) }} €</td>
        </tr>
        <tr style="border-top: 2px solid #34495e;">
            <td colspan="3"><strong>Gesamtsumme (brutto)</strong></td>
            <td><strong>{{ number_format($summeBrutto, 2) }} €</strong></td>
        </tr>
    </table>
</div>
@endif
@endsection