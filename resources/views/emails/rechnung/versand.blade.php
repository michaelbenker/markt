@component('mail::message')
<style>
    .highlight {
        background-color: #e8f4fd !important;
        padding: 15px !important;
        border-radius: 5px !important;
        margin: 20px 0 !important;
    }

    .info-section {
        background-color: #f8f9fa !important;
        padding: 15px !important;
        border-left: 4px solid #34495e !important;
        margin: 20px 0 !important;
    }

    .payment-info {
        background-color: #fff3cd !important;
        padding: 15px !important;
        border-left: 4px solid #ffc107 !important;
        margin: 20px 0 !important;
    }
</style>

# Rechnung {{ $rechnung->rechnungsnummer }}

@php
$aussteller = $rechnung->aussteller ?? $rechnung->buchung?->aussteller;
$anrede = $aussteller?->briefanrede ?: ($rechnung->empf_anrede ? $rechnung->empf_anrede . ' ' . $rechnung->empf_vorname . ' ' . $rechnung->empf_name : 'Sehr geehrte Damen und Herren');
@endphp

{{ $anrede }},

{{ $rechnung->anschreiben ?: 'hiermit erhalten Sie Ihre Rechnung für die gebuchten Leistungen.' }}

<div class="info-section">
    <div><strong>Rechnungsnummer:</strong> {{ $rechnung->rechnungsnummer }}</div>
    <div><strong>Rechnungsdatum:</strong> {{ $rechnung->rechnungsdatum->format('d.m.Y') }}</div>
    <div><strong>Fälligkeitsdatum:</strong> {{ $rechnung->faelligkeitsdatum->format('d.m.Y') }}</div>
    <div><strong>Rechnungsbetrag:</strong> {{ number_format(($rechnung->bruttobetrag / 100), 2, ',', '.') }} €</div>
    @if($rechnung->zahlungsziel)
    <div><strong>Zahlungsziel:</strong> {{ $rechnung->zahlungsziel }}</div>
    @endif
</div>

<div class="payment-info">
    <div><strong>Bankverbindung:</strong><br>
        Veranstaltungsforum Fürstenfeld<br>
        <strong>IBAN:</strong> DE12 7005 1540 0280 8173 47<br>
        <strong>BIC:</strong> BYLADEM1FFB<br>
        Sparkasse Fürstenfeldbruck
    </div>
    <div>Bitte geben Sie bei der Überweisung die <strong>Rechnungsnummer {{ $rechnung->rechnungsnummer }}</strong> als Verwendungszweck an.</div>
</div>

<div class="highlight">
    Die detaillierte Rechnung finden Sie im PDF-Anhang.
</div>

@if($rechnung->schlussschreiben)
{{ $rechnung->schlussschreiben }}
@else
Bei Fragen stehen wir Ihnen gerne zur Verfügung.
@endif

Mit freundlichen Grüßen<br>
**Ihr Team vom Veranstaltungsforum Fürstenfeld**
@endcomponent