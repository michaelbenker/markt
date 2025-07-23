@component('mail::message')
# Ihre Buchungsanfrage

Wir haben Ihre Buchungsanfrage erhalten und werden uns in Kürze bei Ihnen melden.

**Markt:** {{ $anfrage->termin->markt->name ?? '-' }} \
**Termin:** {{ $anfrage->termin ? \Carbon\Carbon::parse($anfrage->termin->start)->format('d.m.Y') . ' bis ' . \Carbon\Carbon::parse($anfrage->termin->ende)->format('d.m.Y') : '-' }}

**Name:** {{ $anfrage->anrede ? $anfrage->anrede . ' ' : '' }}{{ $anfrage->vorname }} {{ $anfrage->nachname }} \
**E-Mail:** {{ $anfrage->email }}

**Warenangebot:**
@if(is_array($anfrage->warenangebot))
- {{ implode(", ", $anfrage->warenangebot) }}
@else
- {{ $anfrage->warenangebot }}
@endif

**Stand:**
@php $stand = $anfrage->stand; @endphp
@if(is_array($stand))
- Art: {{ $stand['art'] ?? '-' }}
- Länge: {{ $stand['laenge'] ?? '-' }} m
- Fläche: {{ $stand['flaeche'] ?? '-' }} m²
@endif

@if($anfrage->bemerkung)
**Bemerkung:**
{{ $anfrage->bemerkung }}
@endif

Bei Rückfragen antworten Sie einfach auf diese E-Mail.

Viele Grüße
Ihr Markt-Team
@endcomponent