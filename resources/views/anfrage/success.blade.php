<x-layouts.app>
    <div class="max-w-2xl mx-auto py-12 px-6">
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold mb-4">Vielen Dank für Ihre Buchungsanfrage!</h1>

            <p class="text-gray-600 mb-6">
                Wir haben Ihre Buchungsanfrage erhalten und werden uns in Kürze bei Ihnen melden.
                Sie erhalten in den nächsten Minuten eine Bestätigungs-E-Mail mit allen Details.
            </p>

            <div class="space-y-4">
                <a href="{{ route('anfrage.create') }}"
                    class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded">
                    Neue Buchungsanfrage
                </a>

                <p class="text-sm text-gray-500">
                    Bei Fragen erreichen Sie uns unter <a href="mailto:{{ $stammdaten['ansprechpartner']['email'] }}" class="text-indigo-600 hover:text-indigo-700">{{ $stammdaten['ansprechpartner']['email'] }}</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>