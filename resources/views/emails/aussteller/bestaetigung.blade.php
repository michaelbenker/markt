@component('mail::message')
# Vielen Dank für deine Anmeldung, {{ $aussteller->vorname }}!

Wir freuen uns, dass du dich für unseren Markt angemeldet hast.

**Firma:** {{ $aussteller->firma ?? '-' }}
**Name:** {{ $aussteller->anrede }} {{ $aussteller->vorname }} {{ $aussteller->name }}
**Ort:** {{ $aussteller->plz }} {{ $aussteller->ort }}

@component('mail::button', ['url' => config('app.url')])
Zur Marktübersicht
@endcomponent

Bei Fragen stehen wir dir jederzeit gerne zur Verfügung.

Viele Grüße,
Dein Markt-Team
@endcomponent