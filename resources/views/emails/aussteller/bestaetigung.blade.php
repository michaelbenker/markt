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
</style>

# Vielen Dank für Ihre Anmeldung!

@php
$anrede = $aussteller->briefanrede ?: ($aussteller->anrede ? $aussteller->anrede . ' ' . $aussteller->vorname . ' ' . $aussteller->name : 'Sehr geehrte Damen und Herren');
@endphp

{{ $anrede }},

wir freuen uns, dass Sie sich für unseren Markt angemeldet haben.

<div class="info-section">
    @if($aussteller->firma)
    <div><strong>Firma:</strong> {{ $aussteller->firma }}</div>
    @endif
    @if($aussteller->name)
    <div><strong>Name:</strong> {{ $aussteller->vorname }} {{ $aussteller->name }}</div>
    @endif
</div>

<div class="highlight">
    Alle relevanten Informationen finden Sie anbei in Ihrer Anmeldebestätigung.
</div>

Bei Fragen stehen wir Ihnen jederzeit gerne zur Verfügung.

Mit freundlichen Grüßen<br>
**Ihr Team vom Veranstaltungsforum Fürstenfeld**
@endcomponent