<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased bg-gray-50 text-gray-900 h-screen grid m-0" style="grid-template-rows: 70px auto 60px;">
    <header class="bg-gray-100 flex items-center justify-center">
        <a href="/" class="inline-block">
            <img src="/images/vff_icon.svg" alt="Veranstaltungsforum Fürstenfeld Logo" class="h-12">
        </a>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="px-6 text-xs text-gray-600 bg-gray-100 flex items-center justify-center">
        <div class="text-center">
            <b>{{ $stammdaten['allgemein']['name'] ?? '' }}</b> · {{ $stammdaten['allgemein']['abteilung'] ?? '' }} · {{ $stammdaten['ansprechpartner']['name'] ?? '' }}
            <br>Tel. <a href="tel:{{ isset($stammdaten['ansprechpartner']['telefon']) ? preg_replace('/\D+/', '', $stammdaten['ansprechpartner']['telefon']) : '' }}">{{ $stammdaten['ansprechpartner']['telefon'] ?? '' }}</a> · E-Mail: <a href="mailto:{{ $stammdaten['ansprechpartner']['email'] ?? '' }}">{{ $stammdaten['ansprechpartner']['email'] ?? '' }}</a>
        </div>
    </footer>

    @livewire('notifications')

    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>