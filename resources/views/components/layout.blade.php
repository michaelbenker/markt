<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markt App</title>
    @vite('resources/css/app.css')
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
            <b>veranstaltungsforum fürstenfeld</b> · Märkte & Veranstaltungen · Michael Landmann
            <br>Tel. <a href="tel:+4981416665166">+49 8141 / 6665-166</a> · E-Mail: <a href="mailto:michaela.landmann@fuerstenfeld.de">michaela.landmann@fuerstenfeld.de</a>
        </div>
    </footer>
</body>

</html>