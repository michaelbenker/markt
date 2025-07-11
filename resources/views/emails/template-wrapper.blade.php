@component('mail::layout')
@if(isset($content))
{!! $content !!}
@else
Kein Content verfügbar
@endif
@endcomponent