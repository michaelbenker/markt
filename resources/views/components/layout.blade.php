<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markt App</title>
    @if (app()->environment('local'))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    @endphp
    <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
    <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
    @endif
</head>

<body class="bg-gray-50 text-gray-900 h-screen grid m-0" style="grid-template-rows: 70px auto 60px;">
    <header class="bg-gray-100 flex items-center justify-center">
        <a href="/" class="inline-block">
            <img src="/images/vff_icon.svg" alt="Veranstaltungsforum Fürstenfeld Logo" class="h-12">
        </a>
    </header>
    <main>
        {{ $slot }}
    </main>
    <footer class="px-6 text-xs text-gray-600 bg-gray-100  flex items-center justify-center">
        <div class="text-center">
            <b>{{ $stammdaten['allgemein']['name'] }}</b> · {{ $stammdaten['allgemein']['abteilung'] }} · {{ $stammdaten['ansprechpartner']['name'] }}
            <br>Tel. <a href="tel:{{ preg_replace('/\D+/', '', $stammdaten['ansprechpartner']['telefon']) }}">{{ $stammdaten['ansprechpartner']['telefon'] }}</a> · E-Mail: <a href="mailto:{{ $stammdaten['ansprechpartner']['email'] }}">{{ $stammdaten['ansprechpartner']['email'] }}</a>
        </div>
    </footer>
</body>

</html>