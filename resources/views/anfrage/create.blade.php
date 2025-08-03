<x-layouts.app>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold mb-3">Buchungsformular</h1>

        <p class="mb-6 text-sm text-gray-600">Felder mit <span class="text-red-600 font-bold">*</span> sind Pflichtfelder.</p>

        <!-- Vorausgewählter Termin -->
        @if($selectedTerminId)
        @php
        $selectedTermin = $termine->firstWhere('id', $selectedTerminId);
        @endphp
        @if($selectedTermin)
        <h2 class="text-2xl font-bold mb-3">
            {{ $selectedTermin->markt->name }}
            ({{ \Carbon\Carbon::parse($selectedTermin->start)->format('d.m.Y') }})
        </h2>
        @endif
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

        <form action="{{ route('anfrage.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Termin Auswahl</h2>
                <div>
                    <label for="termin" class="block font-medium text-sm text-gray-700">Termin</label>
                    <select name="termin" id="termin" required
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Bitte wählen Sie einen Termin</option>
                        @foreach($termine as $termin)
                        <option value="{{ $termin->id }}" {{ $selectedTerminId == $termin->id ? 'selected' : '' }}>
                            {{ $termin->markt->name }} - {{ \Carbon\Carbon::parse($termin->start)->format('d.m.Y') }} bis {{ \Carbon\Carbon::parse($termin->ende)->format('d.m.Y') }}
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
                        <label for="email" class="block font-medium text-sm text-gray-700">E-Mail <span class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" required autocomplete="email"
                            value="{{ old('email') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="mobil" class="block font-medium text-sm text-gray-700">Mobil <span class="text-red-600">*</span></label>
                        <input type="tel" name="mobil" required id="mobil"
                            value="{{ old('mobil') }}"
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
                    <!-- <div>
                        <label for="stand_art" class="block font-medium text-sm text-gray-700">Stand Art</label>
                        <select name="stand[art]" id="stand_art"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="klein" {{ old('stand.art') == 'klein' ? 'selected' : '' }}>Klein</option>
                            <option value="mittel" {{ old('stand.art') == 'mittel' ? 'selected' : '' }}>Mittel</option>
                            <option value="groß" {{ old('stand.art') == 'groß' ? 'selected' : '' }}>Groß</option>
                        </select>
                    </div> -->

                    <div>
                        <label for="stand_laenge" class="block font-medium text-sm text-gray-700">Länge (m)</label>
                        <input type="number" name="stand[laenge]" id="stand_laenge" step="0.1"
                            value="{{ old('stand.laenge') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="stand_tiefe" class="block font-medium text-sm text-gray-700">Tiefe (m)</label>
                        <input type="number" name="stand[tiefe]" id="stand_tiefe" step="0.1"
                            value="{{ old('stand.tiefe') }}"
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
                <h2 class="text-xl font-semibold mb-4">Warenangebot <span class="text-red-600">*</span></h2>
                <div class="space-y-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">
                            Warenangebot (Mehrfachauswahl möglich) <span class="text-red-600">*</span>
                        </label>
                        <div id="warenangebot_error" class="text-red-600 text-sm mb-2 hidden">Bitte wählen Sie mindestens eine Kategorie aus.</div>
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
                <h2 class="text-xl font-semibold mb-4">Herkunft der Waren <span class="text-red-600">*</span></h2>
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

            <!-- Bilder und Dateien -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Bilder und Dateien</h2>
                <div class="space-y-6">
                    <!-- Detailfotos Warenangebot -->
                    <div>
                        <label for="detailfotos_warenangebot" class="block font-medium text-sm text-gray-700 mb-2">
                            Detailfotos aus dem Warenangebot (bis zu 4 Bilder)
                        </label>
                        <input type="file" name="detailfotos_warenangebot[]" id="detailfotos_warenangebot"
                            accept="image/*" multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF bis 5MB pro Bild</p>
                        <div id="detailfotos_error" class="text-red-600 text-sm mt-1 hidden"></div>
                        <div id="detailfotos_preview" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                    </div>

                    <!-- Foto Verkaufsstand -->
                    <div>
                        <label for="foto_verkaufsstand" class="block font-medium text-sm text-gray-700 mb-2">
                            Foto Ihres Verkaufsstandes (1 Bild)
                        </label>
                        <input type="file" name="foto_verkaufsstand" id="foto_verkaufsstand"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF bis 5MB</p>
                    </div>

                    <!-- Foto Werkstatt -->
                    <div>
                        <label for="foto_werkstatt" class="block font-medium text-sm text-gray-700 mb-2">
                            Foto, das Sie in Ihrer Werkstatt zeigt (1 Bild)
                        </label>
                        <input type="file" name="foto_werkstatt" id="foto_werkstatt"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF bis 5MB</p>
                    </div>

                    <!-- Lebenslauf/Vita -->
                    <div>
                        <label for="lebenslauf_vita" class="block font-medium text-sm text-gray-700 mb-2">
                            Lebenslauf/Vita (PDF)
                        </label>
                        <input type="file" name="lebenslauf_vita" id="lebenslauf_vita"
                            accept=".pdf"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Nur PDF-Dateien bis 10MB</p>
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

                // Detailfotos Validierung
                const detailfotosInput = document.getElementById('detailfotos_warenangebot');
                const detailfotosError = document.getElementById('detailfotos_error');
                const detailfotosPreview = document.getElementById('detailfotos_preview');

                detailfotosInput.addEventListener('change', function() {
                    const files = Array.from(this.files);
                    detailfotosError.classList.add('hidden');
                    detailfotosPreview.innerHTML = '';

                    // Prüfe Anzahl der Dateien
                    if (files.length > 4) {
                        detailfotosError.textContent = 'Maximal 4 Bilder erlaubt. Bitte wählen Sie weniger Dateien aus.';
                        detailfotosError.classList.remove('hidden');
                        this.value = ''; // Auswahl zurücksetzen
                        return;
                    }

                    // Prüfe Dateigröße und zeige Vorschau
                    let hasError = false;
                    files.forEach((file, index) => {
                        // Dateigröße prüfen (5MB = 5242880 Bytes)
                        if (file.size > 5242880) {
                            detailfotosError.textContent = `Die Datei "${file.name}" ist zu groß. Maximal 5MB pro Bild erlaubt.`;
                            detailfotosError.classList.remove('hidden');
                            hasError = true;
                            return;
                        }

                        // Dateityp prüfen
                        if (!file.type.startsWith('image/')) {
                            detailfotosError.textContent = `Die Datei "${file.name}" ist kein gültiges Bild.`;
                            detailfotosError.classList.remove('hidden');
                            hasError = true;
                            return;
                        }

                        // Vorschau erstellen
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'relative';
                            preview.innerHTML = `
                                <img src="${e.target.result}" class="w-full h-20 object-cover rounded border">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate">
                                    ${file.name}
                                </div>
                            `;
                            detailfotosPreview.appendChild(preview);
                        };
                        reader.readAsDataURL(file);
                    });

                    if (hasError) {
                        this.value = ''; // Auswahl zurücksetzen
                        detailfotosPreview.innerHTML = '';
                    }
                });

                // Warenangebot Validierung
                const form = document.querySelector('form');
                const warenangebotCheckboxes = document.querySelectorAll('input[name="warenangebot[]"]');
                const warenangebotError = document.getElementById('warenangebot_error');

                form.addEventListener('submit', function(e) {
                    const checkedBoxes = document.querySelectorAll('input[name="warenangebot[]"]:checked');
                    if (checkedBoxes.length === 0) {
                        e.preventDefault();
                        warenangebotError.classList.remove('hidden');
                        warenangebotError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return false;
                    } else {
                        warenangebotError.classList.add('hidden');
                    }
                });

                // Verstecke Fehler wenn Checkbox ausgewählt wird
                warenangebotCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const checkedBoxes = document.querySelectorAll('input[name="warenangebot[]"]:checked');
                        if (checkedBoxes.length > 0) {
                            warenangebotError.classList.add('hidden');
                        }
                    });
                });
            });
        </script>
    </div>
</x-layouts.app>