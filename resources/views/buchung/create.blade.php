<x-layout>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold mb-6">Buchungsformular</h1>

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ url('/buchung') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Markt Auswahl -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Markt Auswahl</h2>
                <div>
                    <label for="markt" class="block font-medium text-sm text-gray-700">Markt</label>
                    <select name="markt" id="markt" required
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Bitte wählen Sie einen Markt</option>
                        @foreach($maerkte as $markt)
                        <option value="{{ $markt->id }}" {{ request('markt') === $markt->slug ? 'selected' : '' }}>
                            {{ $markt->name }} ({{ \Carbon\Carbon::parse($markt->start)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($markt->ende)->format('d.m.Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Aussteller Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Aussteller Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="firma" class="block font-medium text-sm text-gray-700">Firma</label>
                        <input type="text" name="firma" id="firma"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="anrede" class="block font-medium text-sm text-gray-700">Anrede</label>
                        <select name="anrede" id="anrede"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Herr">Herr</option>
                            <option value="Frau">Frau</option>
                            <option value="Divers">Divers</option>
                        </select>
                    </div>

                    <div>
                        <label for="vorname" class="block font-medium text-sm text-gray-700">Vorname</label>
                        <input type="text" name="vorname" id="vorname" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="strasse" class="block font-medium text-sm text-gray-700">Straße</label>
                        <input type="text" name="strasse" id="strasse" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="hausnummer" class="block font-medium text-sm text-gray-700">Hausnummer</label>
                        <input type="text" name="hausnummer" id="hausnummer"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="plz" class="block font-medium text-sm text-gray-700">PLZ</label>
                        <input type="text" name="plz" id="plz" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="ort" class="block font-medium text-sm text-gray-700">Ort</label>
                        <input type="text" name="ort" id="ort" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="land" class="block font-medium text-sm text-gray-700">Land</label>
                        <select name="land" id="land" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Deutschland">Deutschland</option>
                            <option value="Österreich">Österreich</option>
                            <option value="Schweiz">Schweiz</option>
                            <option value="Italien">Italien</option>
                            <option value="Frankreich">Frankreich</option>
                            <option value="Niederlande">Niederlande</option>
                        </select>
                    </div>

                    <div>
                        <label for="telefon" class="block font-medium text-sm text-gray-700">Telefon</label>
                        <input type="tel" name="telefon" id="telefon"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="mobil" class="block font-medium text-sm text-gray-700">Mobil</label>
                        <input type="tel" name="mobil" id="mobil"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="email" class="block font-medium text-sm text-gray-700">E-Mail</label>
                        <input type="email" name="email" id="email" required
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="homepage" class="block font-medium text-sm text-gray-700">Homepage</label>
                        <input type="url" name="homepage" id="homepage"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="bereits_ausgestellt" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2">Ich habe schon einmal im Veranstaltungsforum Fürstenfeld ausgestellt</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Stand Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Stand Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="stand_art" class="block font-medium text-sm text-gray-700">Stand Art</label>
                        <select name="stand[art]" id="stand_art"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="klein">Klein</option>
                            <option value="mittel">Mittel</option>
                            <option value="groß">Groß</option>
                        </select>
                    </div>

                    <div>
                        <label for="stand_laenge" class="block font-medium text-sm text-gray-700">Länge (m)</label>
                        <input type="number" name="stand[laenge]" id="stand_laenge" step="0.1"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="stand_flaeche" class="block font-medium text-sm text-gray-700">Fläche (m²)</label>
                        <input type="number" name="stand[flaeche]" id="stand_flaeche" step="0.1"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Warenangebot -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Warenangebot</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">Warenangebot (Mehrfachauswahl möglich)</label>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="kleidung" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Kleidung</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="schmuck" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Schmuck</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="kunst" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Kunst</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="accessoires" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Accessoires</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="dekoration" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Dekoration</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="lebensmittel" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Lebensmittel</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="getraenke" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Getränke</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="handwerk" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Handwerk</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="antiquitäten" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Antiquitäten</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="sonstiges" id="sonstiges_checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Sonstiges</span>
                            </label>
                        </div>
                        <div id="sonstiges_textarea_container" class="mt-4 hidden">
                            <label for="sonstiges_beschreibung" class="block font-medium text-sm text-gray-700">Bitte beschreiben Sie Ihr Warenangebot</label>
                            <textarea name="sonstiges_beschreibung" id="sonstiges_beschreibung" rows="3"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Herkunft der Waren -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Herkunft der Waren</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex flex-col">
                        <label for="herkunft_eigenfertigung" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Eigenfertigung (%)</label>
                        <input type="number" name="herkunft[eigenfertigung]" id="herkunft_eigenfertigung" min="0" max="100"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex flex-col">
                        <label for="herkunft_industrieware_nicht_entwicklungslaender" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Industrieware<br>(nicht Entwicklungsland) (%)</label>
                        <input type="number" name="herkunft[industrieware_nicht_entwicklungslaender]" id="herkunft_industrieware_nicht_entwicklungslaender" min="0" max="100"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex flex-col">
                        <label for="herkunft_industrieware_entwicklungslaender" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Industrieware<br>(Entwicklungsland) (%)</label>
                        <input type="number" name="herkunft[industrieware_entwicklungslaender]" id="herkunft_industrieware_entwicklungslaender" min="0" max="100"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                    Buchung anfragen
                </button>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sonstigesCheckbox = document.getElementById('sonstiges_checkbox');
                const sonstigesTextareaContainer = document.getElementById('sonstiges_textarea_container');
                const sonstigesTextarea = document.getElementById('sonstiges_beschreibung');

                sonstigesCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        sonstigesTextareaContainer.classList.remove('hidden');
                        sonstigesTextarea.setAttribute('required', 'required');
                    } else {
                        sonstigesTextareaContainer.classList.add('hidden');
                        sonstigesTextarea.removeAttribute('required');
                        sonstigesTextarea.value = '';
                    }
                });
            });
        </script>
    </div>
</x-layout>