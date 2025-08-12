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
            <th>Markt</th>
            <td>{{ $buchung->markt->name ?? 'Unbekannt' }}</td>
        </tr>
        <tr>
            <th>Termine</th>
            <td>
                @php
                if ($buchung->termine && is_array($buchung->termine) && count($buchung->termine) > 0) {
                    $terminObjekte = \App\Models\Termin::whereIn('id', $buchung->termine)->orderBy('start')->get();
                    $terminStrings = [];
                    
                    foreach ($terminObjekte as $termin) {
                        $startDate = \Carbon\Carbon::parse($termin->start);
                        $endDate = \Carbon\Carbon::parse($termin->ende);
                        
                        if ($startDate->format('m') === $endDate->format('m')) {
                            // Gleicher Monat
                            $terminStrings[] = $startDate->format('d.') . '-' . $endDate->format('d.m.Y');
                        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
                            // Gleiches Jahr, aber unterschiedlicher Monat
                            $terminStrings[] = $startDate->format('d.m.') . '-' . $endDate->format('d.m.Y');
                        } else {
                            // Unterschiedliche Jahre
                            $terminStrings[] = $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
                        }
                    }
                    
                    echo implode('<br>', $terminStrings);
                } else {
                    echo 'Keine Termine ausgewählt';
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

@if($buchung->aussteller->stand)
<div class="section">
    <div class="section-title">Stand</div>
    <table>
        <tr>
            <th>Länge</th>
            <td>{{ $buchung->aussteller->stand['laenge'] ?? '-' }} m</td>
        </tr>
        <tr>
            <th>Tiefe</th>
            <td>{{ $buchung->aussteller->stand['tiefe'] ?? '-' }} m</td>
        </tr>
        <tr>
            <th>Fläche</th>
            <td>{{ $buchung->aussteller->stand['flaeche'] ?? '-' }} m²</td>
        </tr>
    </table>
</div>
@endif

@if($buchung->aussteller->vorfuehrung_am_stand)
<div class="section">
    <div class="section-title">Vorführung</div>
    <table>
        <tr>
            <th>Vorführung des Handwerks am Stand</th>
            <td>{{ $buchung->aussteller->vorfuehrung_am_stand ? 'Ja' : 'Nein' }}</td>
        </tr>
    </table>
</div>
@endif

@if($buchung->aussteller->subkategorien && $buchung->aussteller->subkategorien->count() > 0)
<div class="section">
    <div class="section-title">Warenangebot (Kategorien)</div>
    <table>
        <tr>
            <th>Kategorien</th>
            <td>
                @php
                    $kategorienText = [];
                    foreach($buchung->aussteller->subkategorien as $subkategorie) {
                        if($subkategorie->kategorie) {
                            $kategorienText[] = $subkategorie->kategorie->name . ' → ' . $subkategorie->name;
                        } else {
                            $kategorienText[] = $subkategorie->name;
                        }
                    }
                    echo implode('<br>', $kategorienText);
                @endphp
            </td>
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
            <td>
                @if(is_array($buchung->warenangebot))
                    @php
                        $subkategorieNames = \App\Models\Subkategorie::whereIn('id', $buchung->warenangebot)
                            ->pluck('name')
                            ->toArray();
                    @endphp
                    {{ implode(', ', $subkategorieNames) }}
                @else
                    {{ $buchung->warenangebot }}
                @endif
            </td>
        </tr>
    </table>
</div>
@endif

@if($buchung->aussteller->herkunft)
<div class="section">
    <div class="section-title">Herkunft der Waren</div>
    <table>
        <tr>
            <th>Eigenfertigung</th>
            <td>{{ $buchung->aussteller->herkunft['eigenfertigung'] ?? 0 }}%</td>
        </tr>
        <tr>
            <th>Industrieware</th>
            <td>{{ $buchung->aussteller->herkunft['industrieware'] ?? 0 }}%</td>
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