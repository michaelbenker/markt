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
                        <option value="{{ $termin->id }}" data-markt-id="{{ $termin->markt->id }}" data-markt-slug="{{ $termin->markt->slug }}" {{ $selectedTerminId == $termin->id ? 'selected' : '' }}>
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
                        <label for="steuer_id" class="block font-medium text-sm text-gray-700">Steuer-ID</label>
                        <input type="text" name="steuer_id" id="steuer_id"
                            value="{{ old('steuer_id') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="handelsregisternummer" class="block font-medium text-sm text-gray-700">Handelsregisternummer</label>
                        <input type="text" name="handelsregisternummer" id="handelsregisternummer"
                            value="{{ old('handelsregisternummer') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- <div>
                        <label for="homepage" class="block font-medium text-sm text-gray-700">Homepage</label>
                        <input type="url" name="homepage" id="homepage"
                            value="{{ old('homepage') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div> -->
                </div>
                <div class="mt-6">
                    <label for="bereits_ausgestellt" class="block font-medium text-sm text-gray-700">Haben Sie bereits an Märkten in Fürstenfeld teilgenommen?</label>
                    <textarea name="bereits_ausgestellt" id="bereits_ausgestellt" rows="3"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Bitte geben Sie an, wann und bei welchen Veranstaltungen.">{{ old('bereits_ausgestellt') }}</textarea>
                </div>
            </div>

            <!-- Stand Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Stand Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                <div class="mt-6">
                    <label for="wunsch_standort_id" class="block font-medium text-sm text-gray-700">Wunschstandort</label>
                    <select name="wunsch_standort_id" id="wunsch_standort_id"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Kein Wunschstandort</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Wählen Sie einen Termin aus, um verfügbare Standorte zu sehen.</p>
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
                        <div id="warenangebot_container" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="col-span-3 text-gray-500 text-center py-4">
                                Bitte wählen Sie zuerst einen Termin aus, um die verfügbaren Kategorien zu sehen.
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="mt-6" />
                <div class="mt-6">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="vorfuehrung_am_stand" value="0">
                        <input type="checkbox" name="vorfuehrung_am_stand" value="1" {{ old('vorfuehrung_am_stand') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2">Vorführung des Handwerkes am eigenen Stand</span>
                    </label>
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

            <!-- Soziale Medien -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Soziale Medien</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="website" class="block font-medium text-sm text-gray-700">Website</label>
                        <input type="url" name="soziale_medien[website]" id="website"
                            value="{{ old('soziale_medien.website') }}"
                            placeholder="https://ihre-website.de"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="facebook" class="block font-medium text-sm text-gray-700">Facebook</label>
                        <input type="url" name="soziale_medien[facebook]" id="facebook"
                            value="{{ old('soziale_medien.facebook') }}"
                            placeholder="https://facebook.com/ihr-profil"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="instagram" class="block font-medium text-sm text-gray-700">Instagram</label>
                        <input type="text" name="soziale_medien[instagram]" id="instagram"
                            value="{{ old('soziale_medien.instagram') }}"
                            placeholder="@ihr-username"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="twitter" class="block font-medium text-sm text-gray-700">Twitter</label>
                        <input type="text" name="soziale_medien[twitter]" id="twitter"
                            value="{{ old('soziale_medien.twitter') }}"
                            placeholder="@ihr-username"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Wünsche für Zusatzleistungen -->
            <div class="bg-white p-6 rounded-lg shadow" id="zusatzleistungen-section">
                <h2 class="text-xl font-semibold mb-4">Wünsche für Zusatzleistungen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4" id="zusatzleistungen-container">
                    <p class="text-gray-500 col-span-3">Bitte wählen Sie zunächst einen Termin aus.</p>
                </div>
            </div>

            <!-- Werbematerial -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Gewünschtes Werbematerial</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="werbematerial_plakate_a3" class="block font-medium text-sm text-gray-700">Plakate A3</label>
                        <input type="number" name="werbematerial[plakate_a3]" id="werbematerial_plakate_a3" min="0" max="100"
                            value="{{ old('werbematerial.plakate_a3', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="werbematerial_plakate_a1" class="block font-medium text-sm text-gray-700">Plakate A1</label>
                        <input type="number" name="werbematerial[plakate_a1]" id="werbematerial_plakate_a1" min="0" max="100"
                            value="{{ old('werbematerial.plakate_a1', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="werbematerial_flyer" class="block font-medium text-sm text-gray-700">Flyer</label>
                        <input type="number" name="werbematerial[flyer]" id="werbematerial_flyer" min="0" max="1000"
                            value="{{ old('werbematerial.flyer', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="werbematerial[social_media_post]" value="1"
                                {{ old('werbematerial.social_media_post') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2">Social Media Post</span>
                        </label>
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
            // Subkategorien-Daten von PHP
            const subkategorienByMarkt = @json($subkategorienByMarkt);
            const standorteByMarkt = @json($standorteByMarkt);
            const maerkteBySlug = @json($maerkteBySlug);
            const oldWarenangebotValues = @json(old('warenangebot', []));
            const oldWunschStandortId = @json(old('wunsch_standort_id'));
            const oldZusatzleistungen = @json(old('wuensche_zusatzleistungen', []));

            document.addEventListener('DOMContentLoaded', function() {
                const terminSelect = document.getElementById('termin');
                const warenangebotContainer = document.getElementById('warenangebot_container');
                const wunschStandortSelect = document.getElementById('wunsch_standort_id');
                const zusatzleistungenContainer = document.getElementById('zusatzleistungen-container');

                // Event Listener für Termin-Auswahl
                terminSelect.addEventListener('change', function() {
                    updateWarenangebot();
                    updateWunschStandorte();
                    updateZusatzleistungen();
                });

                // Initial laden, falls ein Termin vorausgewählt ist
                if (terminSelect.value) {
                    updateWarenangebot();
                    updateWunschStandorte();
                    updateZusatzleistungen();
                }

                function updateWarenangebot() {
                    const selectedTerminId = terminSelect.value;
                    if (!selectedTerminId) {
                        warenangebotContainer.innerHTML = '<div class="col-span-3 text-gray-500 text-center py-4">Bitte wählen Sie zuerst einen Termin aus, um die verfügbaren Kategorien zu sehen.</div>';
                        return;
                    }

                    // Markt-ID aus dem ausgewählten Termin ermitteln
                    const selectedOption = terminSelect.options[terminSelect.selectedIndex];
                    const marktId = selectedOption.dataset.marktId;

                    if (!marktId || !subkategorienByMarkt[marktId]) {
                        warenangebotContainer.innerHTML = '<div class="col-span-3 text-gray-500 text-center py-4">Für diesen Markt sind keine Kategorien hinterlegt.</div>';
                        return;
                    }

                    const subkategorien = subkategorienByMarkt[marktId];
                    let html = '';

                    subkategorien.forEach(function(subkat) {
                        const isChecked = oldWarenangebotValues.includes(subkat.id.toString()) ? 'checked' : '';

                        html += `
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="warenangebot[]" value="${subkat.id}" ${isChecked} 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 warenangebot-checkbox">
                                <span class="ml-2">${subkat.name}</span>
                            </label>
                        `;
                    });

                    warenangebotContainer.innerHTML = html;

                    // Event Listener für neue Checkboxen hinzufügen
                    addWarenangebotEventListeners();
                }

                function updateWunschStandorte() {
                    const selectedTerminId = terminSelect.value;

                    // Alle Optionen außer der ersten löschen
                    wunschStandortSelect.innerHTML = '<option value="">Kein Wunschstandort</option>';

                    if (!selectedTerminId) {
                        return;
                    }

                    // Markt-ID aus dem ausgewählten Termin ermitteln
                    const selectedOption = terminSelect.options[terminSelect.selectedIndex];
                    const marktId = selectedOption.dataset.marktId;

                    if (!marktId || !standorteByMarkt[marktId]) {
                        return;
                    }

                    const standorte = standorteByMarkt[marktId];
                    standorte.forEach(function(standort) {
                        const option = document.createElement('option');
                        option.value = standort.id;
                        option.textContent = standort.name;

                        // Vorauswahl wiederherstellen
                        if (oldWunschStandortId && oldWunschStandortId == standort.id) {
                            option.selected = true;
                        }

                        wunschStandortSelect.appendChild(option);
                    });
                }

                function updateZusatzleistungen() {
                    const selectedTerminId = terminSelect.value;
                    if (!selectedTerminId) {
                        zusatzleistungenContainer.innerHTML = '<p class="text-gray-500 col-span-3">Bitte wählen Sie zunächst einen Termin aus.</p>';
                        return;
                    }

                    // Markt-Slug aus dem ausgewählten Termin ermitteln
                    const selectedOption = terminSelect.options[terminSelect.selectedIndex];
                    const marktSlug = selectedOption.dataset.marktSlug;

                    if (!marktSlug || !maerkteBySlug[marktSlug]) {
                        zusatzleistungenContainer.innerHTML = '<p class="text-gray-500 col-span-3">Für diesen Markt sind keine Zusatzleistungen konfiguriert.</p>';
                        return;
                    }

                    const markt = maerkteBySlug[marktSlug];
                    const leistungen = markt.leistungen;

                    if (!leistungen || leistungen.length === 0) {
                        zusatzleistungenContainer.innerHTML = '<p class="text-gray-500 col-span-3">Für diesen Markt sind keine Zusatzleistungen konfiguriert.</p>';
                        return;
                    }

                    let html = '';
                    leistungen.forEach(function(leistung) {
                        const isChecked = oldZusatzleistungen.includes(leistung.id.toString()) ? 'checked' : '';
                        const preis = (leistung.preis / 100).toLocaleString('de-DE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        html += `
                            <label class="inline-flex items-start">
                                <input type="checkbox" 
                                       name="wuensche_zusatzleistungen[]" 
                                       value="${leistung.id}" 
                                       ${isChecked}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1">
                                <div class="ml-2">
                                    <span class="block font-medium">${leistung.name}</span>
                                    <span class="text-sm text-gray-600">${preis} € / ${leistung.einheit}</span>
                                    ${leistung.beschreibung ? `<span class="text-xs text-gray-500 block">${leistung.beschreibung}</span>` : ''}
                                </div>
                            </label>
                        `;
                    });

                    zusatzleistungenContainer.innerHTML = html;
                }

                function addWarenangebotEventListeners() {
                    const warenangebotCheckboxes = document.querySelectorAll('.warenangebot-checkbox');
                    const warenangebotError = document.getElementById('warenangebot_error');

                    warenangebotCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const checkedBoxes = document.querySelectorAll('.warenangebot-checkbox:checked');
                            if (checkedBoxes.length > 0) {
                                warenangebotError.classList.add('hidden');
                            }
                        });
                    });
                }

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
                const warenangebotError = document.getElementById('warenangebot_error');

                form.addEventListener('submit', function(e) {
                    const checkedBoxes = document.querySelectorAll('.warenangebot-checkbox:checked');
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
            });
        </script>
    </div>
</x-layouts.app>