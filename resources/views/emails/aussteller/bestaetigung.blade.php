@component('mail::message')
# Vielen Dank für deine Anmeldung, {{ $aussteller->vorname }}!

Wir freuen uns, dass du dich für unseren Markt angemeldet hast.

@if($aussteller->firma)**Firma:** {{ $aussteller->firma }}@endif
@if($aussteller->name)**Name:** {{ $aussteller->vorname }} {{ $aussteller->name }}@endif

Alle relevanten Information findest du anbei in deiner Anmeldebestätigung oder auf deiner Buchungsseite.

@component('mail::button', ['url' => config('app.url')])
Zur Buchungsseite
@endcomponent

Bei Fragen stehen wir dir jederzeit gerne zur Verfügung.

Viele Grüße,
Dein Markt-Team
@endcomponent