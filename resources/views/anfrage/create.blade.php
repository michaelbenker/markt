<x-layouts.app>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold mb-3">Buchungsformular</h1>

        <p class="mb-6 text-sm text-gray-600">Felder mit <span class="text-red-600">*</span> sind Pflichtfelder.</p>

        <!-- Markt Auswahl -->
        @php
        $marktSlug = request('markt');
        $marktVorwahl = $marktSlug ? $maerkte->firstWhere('slug', $marktSlug) : null;
        @endphp

        @if($marktVorwahl)
        <h2 class="text-2xl font-bold mb-3">
            {{ $marktVorwahl->name }} (
            @php
            $termine = $marktVorwahl->termine()->where('start', '>', now())->orderBy('start')->get();
            echo $termine->map(fn($t) => \Carbon\Carbon::parse($t->start)->format('d.m.Y'))->join(', ');
            @endphp
            )
        </h2>

        @endif

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('anfrage.store') }}" method="POST" class="space-y-8">
            @csrf
            @if($marktVorwahl)
            <input type="hidden" name="markt" value="{{ $marktVorwahl->id }}">
            @else
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Markt Auswahl</h2>
                <div>
                    <label for="markt" class="block font-medium text-sm text-gray-700">Markt</label>
                    <select name="markt" id="markt" required
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Bitte wählen Sie einen Markt</option>
                        @foreach($maerkte as $markt)
                        <option value="{{ $markt->id }}" {{ request('markt') === $markt->slug ? 'selected' : '' }}>
                            {{ $markt->name }} (
                            @php
                            $termine = $markt->termine()->where('start', '>', now())->orderBy('start')->get();
                            echo $termine->map(fn($t) => \Carbon\Carbon::parse($t->start)->format('d.m.Y'))->join(', ');
                            @endphp
                            )
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <!-- Aussteller Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Aussteller Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="firma" class="block font-medium text-sm text-gray-700">Firma</label>
                        <input type="text" name="firma" id="firma"
                            value="{{ old('firma') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="anrede" class="block font-medium text-sm text-gray-700">Anrede</label>
                        <select name="anrede" id="anrede"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Herr" {{ old('anrede') == 'Herr' ? 'selected' : '' }}>Herr</option>
                            <option value="Frau" {{ old('anrede') == 'Frau' ? 'selected' : '' }}>Frau</option>
                            <option value="Divers" {{ old('anrede') == 'Divers' ? 'selected' : '' }}>Divers</option>
                        </select>
                    </div>

                    <div>
                        <label for="vorname" class="block font-medium text-sm text-gray-700">Vorname <span class="text-red-600">*</span></label>
                        <input type="text" name="vorname" id="vorname" required autocomplete="given-name"
                            value="{{ old('vorname') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="nachname" class="block font-medium text-sm text-gray-700">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="nachname" id="nachname" required autocomplete="family-name"
                            value="{{ old('nachname') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="strasse" class="block font-medium text-sm text-gray-700">Straße <span class="text-red-600">*</span></label>
                        <input type="text" name="strasse" id="strasse" required autocomplete="street-address"
                            value="{{ old('strasse') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="hausnummer" class="block font-medium text-sm text-gray-700">Hausnummer</label>
                        <input type="text" name="hausnummer" id="hausnummer" autocomplete="address-line2"
                            value="{{ old('hausnummer') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="plz" class="block font-medium text-sm text-gray-700">PLZ <span class="text-red-600">*</span></label>
                        <input type="text" name="plz" id="plz" required autocomplete="postal-code"
                            value="{{ old('plz') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="ort" class="block font-medium text-sm text-gray-700">Ort <span class="text-red-600">*</span></label>
                        <input type="text" name="ort" id="ort" required autocomplete="address-level2"
                            value="{{ old('ort') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="land" class="block font-medium text-sm text-gray-700">Land <span class="text-red-600">*</span></label>
                        <select name="land" id="land" required autocomplete="country"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Deutschland" {{ old('land') == 'Deutschland' ? 'selected' : '' }}>Deutschland</option>
                            <option value="Österreich" {{ old('land') == 'Österreich' ? 'selected' : '' }}>Österreich</option>
                            <option value="Schweiz" {{ old('land') == 'Schweiz' ? 'selected' : '' }}>Schweiz</option>
                            <option value="Italien" {{ old('land') == 'Italien' ? 'selected' : '' }}>Italien</option>
                            <option value="Frankreich" {{ old('land') == 'Frankreich' ? 'selected' : '' }}>Frankreich</option>
                            <option value="Niederlande" {{ old('land') == 'Niederlande' ? 'selected' : '' }}>Niederlande</option>
                        </select>
                    </div>

                    <div>
                        <label for="telefon" class="block font-medium text-sm text-gray-700">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" autocomplete="tel"
                            value="{{ old('telefon') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="mobil" class="block font-medium text-sm text-gray-700">Mobil</label>
                        <input type="tel" name="mobil" id="mobil"
                            value="{{ old('mobil') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="email" class="block font-medium text-sm text-gray-700">E-Mail <span class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" required autocomplete="email"
                            value="{{ old('email') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="homepage" class="block font-medium text-sm text-gray-700">Homepage</label>
                        <input type="url" name="homepage" id="homepage"
                            value="{{ old('homepage') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="bereits_ausgestellt" {{ old('bereits_ausgestellt') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                            <option value="klein" {{ old('stand.art') == 'klein' ? 'selected' : '' }}>Klein</option>
                            <option value="mittel" {{ old('stand.art') == 'mittel' ? 'selected' : '' }}>Mittel</option>
                            <option value="groß" {{ old('stand.art') == 'groß' ? 'selected' : '' }}>Groß</option>
                        </select>
                    </div>

                    <div>
                        <label for="stand_laenge" class="block font-medium text-sm text-gray-700">Länge (m)</label>
                        <input type="number" name="stand[laenge]" id="stand_laenge" step="0.1"
                            value="{{ old('stand.laenge') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="stand_flaeche" class="block font-medium text-sm text-gray-700">Fläche (m²)</label>
                        <input type="number" name="stand[flaeche]" id="stand_flaeche" step="0.1"
                            value="{{ old('stand.flaeche') }}"
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
                                <input type="checkbox" name="warenangebot[]" value="kleidung" {{ is_array(old('warenangebot')) && in_array('kleidung', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Kleidung</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="schmuck" {{ is_array(old('warenangebot')) && in_array('schmuck', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Schmuck</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="kunst" {{ is_array(old('warenangebot')) && in_array('kunst', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Kunst</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="accessoires" {{ is_array(old('warenangebot')) && in_array('accessoires', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Accessoires</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="dekoration" {{ is_array(old('warenangebot')) && in_array('dekoration', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Dekoration</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="lebensmittel" {{ is_array(old('warenangebot')) && in_array('lebensmittel', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Lebensmittel</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="getraenke" {{ is_array(old('warenangebot')) && in_array('getraenke', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Getränke</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="handwerk" {{ is_array(old('warenangebot')) && in_array('handwerk', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Handwerk</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="antiquitäten" {{ is_array(old('warenangebot')) && in_array('antiquitäten', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Antiquitäten</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="sonstiges" id="sonstiges_checkbox" {{ is_array(old('warenangebot')) && in_array('sonstiges', old('warenangebot')) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2">Sonstiges</span>
                            </label>
                        </div>
                        <div id="sonstiges_textarea_container" class="mt-4 hidden">
                            <label for="sonstiges_beschreibung" class="block font-medium text-sm text-gray-700">Bitte beschreiben Sie Ihr Warenangebot</label>
                            <textarea name="sonstiges_beschreibung" id="sonstiges_beschreibung" rows="3"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('sonstiges_beschreibung') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Herkunft der Waren -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Herkunft der Waren</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex flex-col">
                        <label for="herkunft_eigenfertigung" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Eigenfertigung (%) <span class="text-red-600">*</span></label>
                        <input type="number" name="herkunft[eigenfertigung]" id="herkunft_eigenfertigung" min="0" max="100"
                            value="{{ old('herkunft.eigenfertigung') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex flex-col">
                        <label for="herkunft_industrieware_nicht_entwicklungslaender" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Industrieware<br>(nicht Entwicklungsland) (%) <span class="text-red-600">*</span></label>
                        <input type="number" name="herkunft[industrieware_nicht_entwicklungslaender]" id="herkunft_industrieware_nicht_entwicklungslaender" min="0" max="100"
                            value="{{ old('herkunft.industrieware_nicht_entwicklungslaender') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex flex-col">
                        <label for="herkunft_industrieware_entwicklungslaender" class="block font-medium text-sm text-gray-700 h-12 flex items-end">Industrieware<br>(Entwicklungsland) (%) <span class="text-red-600">*</span></label>
                        <input type="number" name="herkunft[industrieware_entwicklungslaender]" id="herkunft_industrieware_entwicklungslaender" min="0" max="100"
                            value="{{ old('herkunft.industrieware_entwicklungslaender') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Bemerkung -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Bemerkung</h2>
                <textarea name="bemerkung" id="bemerkung" rows="4"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Hier können Sie uns noch etwas mitteilen...">{{ old('bemerkung') }}</textarea>
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
</x-layouts.app>