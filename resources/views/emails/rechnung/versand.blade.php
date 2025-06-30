@component('mail::message')
# Rechnung {{ $rechnung->rechnungsnummer }}

@if($rechnung->empf_anrede){{ $rechnung->empf_anrede }} @endif{{ $rechnung->empf_vorname }} {{ $rechnung->empf_name }},

{{ $rechnung->anschreiben ?: 'hiermit erhalten Sie Ihre Rechnung.' }}

**Rechnungsdetails:**
- Rechnungsnummer: {{ $rechnung->rechnungsnummer }}
- Rechnungsdatum: {{ $rechnung->rechnungsdatum->format('d.m.Y') }}
- Fälligkeitsdatum: {{ $rechnung->faelligkeitsdatum->format('d.m.Y') }}
- Rechnungsbetrag: {{ number_format($rechnung->bruttobetrag, 2, ',', '.') }} €

@if($rechnung->zahlungsziel)
**Zahlungsziel:** {{ $rechnung->zahlungsziel }}
@endif

**Bankverbindung:**
Veranstaltungsforum Fürstenfeld
IBAN: DE12 7005 1540 0280 8173 47
BIC: BYLADEM1FFB
Sparkasse Fürstenfeldbruck

Bitte geben Sie bei der Überweisung die Rechnungsnummer {{ $rechnung->rechnungsnummer }} als Verwendungszweck an.

Die detaillierte Rechnung finden Sie im PDF-Anhang.

@if($rechnung->schlussschreiben)
{{ $rechnung->schlussschreiben }}
@else
Bei Fragen stehen wir Ihnen gerne zur Verfügung.
@endif

@component('mail::button', ['url' => route('rechnung.pdf', $rechnung->rechnungsnummer)])
Rechnung online anzeigen
@endcomponent

Viele Grüße
Ihr Team vom Veranstaltungsforum Fürstenfeld
@endcomponent