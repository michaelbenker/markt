@component('mail::message')
# Ihre Buchungsanfrage

Wir haben Ihre Buchungsanfrage erhalten und werden uns in Kürze bei Ihnen melden.

**Markt:** {{ $anfrage->markt->name ?? '-' }}
@if($anfrage->markt && $anfrage->markt->termine && $anfrage->markt->termine->count())
({{ $anfrage->markt->termine->map(fn($t) => \Carbon\Carbon::parse($t->start)->format('d.m.Y'))->join(', ') }})
@endif

**Name:** {{ $anfrage->anrede ? $anfrage->anrede . ' ' : '' }}{{ $anfrage->vorname }} {{ $anfrage->nachname }}
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