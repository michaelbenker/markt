<x-layouts.app>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold mb-3">Buchungsformular</h1>

        <p class="mb-6 text-sm text-gray-600">Felder mit <span class="text-red-600 font-bold">*</span> sind Pflichtfelder.</p>

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Markt-Auswahl wenn kein Markt ausgewählt oder mehrere verfügbar -->
        @if(!$selectedMarkt && $aktiveMaerkte->count() > 1)
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-4">Markt auswählen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($aktiveMaerkte as $markt)
                <a href="{{ route('anfrage.create', ['markt' => $markt->slug]) }}"
                    class="block p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition">
                    <h3 class="font-semibold text-lg">{{ $markt->name }}</h3>
                    @if($markt->termine->count() > 0)
                    <p class="text-sm text-gray-600 mt-1">
                        @if($markt->termine->count() == 1)
                        {{ $markt->termine->first()->start->format('d.m.Y') }}
                        @else
                        {{ $markt->termine->count() }} Termine verfügbar
                        @endif
                    </p>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Formular wenn Markt ausgewählt -->
        @if($selectedMarkt)
        <form action="{{ route('anfrage.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            @if($selectedTermine->count() == 1)
            <!-- Hidden field für einzelnen Termin direkt nach CSRF -->
            <input type="hidden" name="termine[]" value="{{ $selectedTermine->first()->id }}">
            @endif

            <!-- Markt & Termine -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">{{ $selectedMarkt->name }}</h2>

                @if($selectedTermine->count() == 1)
                <!-- Nur ein Termin - automatisch ausgewählt -->
                <div class="p-3 bg-gray-50 rounded-md">
                    <p class="text-gray-700">
                        <strong>Termin:</strong>
                        @if($selectedTermine->first()->ende)
                        {{ $selectedTermine->first()->start->format('d.m.Y') }} -
                        {{ $selectedTermine->first()->ende->format('d.m.Y') }}
                        @else
                        {{ $selectedTermine->first()->start->format('d.m.Y') }}
                        @endif
                        @if($selectedTermine->first()->bemerkung)
                        <span class="text-sm text-gray-600 block mt-1">{{ $selectedTermine->first()->bemerkung }}</span>
                        @endif
                    </p>
                </div>
                <script>
                    // Debug: Sicherstellen, dass das hidden field beim Submit vorhanden ist
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.querySelector('form');
                        form.addEventListener('submit', function(e) {
                            const hiddenTermin = document.getElementById('single-termin');
                            if (hiddenTermin) {
                                console.log('Hidden Termin value:', hiddenTermin.value);
                                console.log('Hidden Termin name:', hiddenTermin.name);
                            }
                        });
                    });
                </script>
                @else
                <!-- Mehrere Termine - Checkboxen -->
                <p class="mb-3 text-sm text-gray-600">Bitte wählen Sie einen oder mehrere Termine aus:</p>
                <div class="space-y-2">
                    @foreach($selectedTermine as $termin)
                    <label class="flex items-start">
                        <input type="checkbox"
                            name="termine[]"
                            value="{{ $termin->id }}"
                            checked
                            class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ps-2">
                            @if($termin->ende)
                            {{ $termin->start->format('d.m.Y') }} -
                            {{ $termin->ende->format('d.m.Y') }}
                            @else
                            {{ $termin->start->format('d.m.Y') }}
                            @endif
                            @if($termin->beschreibung)
                            <span class="text-sm text-gray-600 block">{{ $termin->beschreibung }}</span>
                            @endif
                        </span>
                    </label>
                    @endforeach
                </div>
                @endif

                <!-- Link zum Wechseln des Marktes -->
                @if($aktiveMaerkte->count() > 1)
                <div class="mt-3 pt-3 border-t">
                    <a href="{{ route('anfrage.create') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                        Anderen Markt auswählen
                    </a>
                </div>
                @endif
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
                            <option value="">Bitte wählen</option>
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
                            @foreach($countries as $value => $label)
                                @if($value === '---')
                                    <option disabled>──────────────</option>
                                @else
                                    <option value="{{ $value }}" {{ old('land', 'Deutschland') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="telefon" class="block font-medium text-sm text-gray-700">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" autocomplete="tel"
                            value="{{ old('telefon') }}"
                            pattern="^\+[1-9]\d{1,14}$"
                            placeholder="+49 89 12345678"
                            title="Bitte geben Sie eine internationale Telefonnummer mit Ländervorwahl ein (z.B. +49 für Deutschland)"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Format: +Ländervorwahl Nummer (z.B. +49 89 12345678)</p>
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
                            pattern="^\+[1-9]\d{1,14}$"
                            placeholder="+49 176 12345678"
                            title="Bitte geben Sie eine internationale Telefonnummer mit Ländervorwahl ein (z.B. +49 für Deutschland)"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Format: +Ländervorwahl Nummer (z.B. +49 176 12345678)</p>
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
                </div>

                <div class="mt-6">
                    <label for="bereits_ausgestellt" class="block font-medium text-sm text-gray-700">Haben Sie bereits an Märkten in Fürstenfeld teilgenommen?</label>
                    <textarea name="bereits_ausgestellt" id="bereits_ausgestellt" rows="3"
                        placeholder="Bitte geben Sie an, wann und bei welchen Veranstaltungen"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('bereits_ausgestellt') }}</textarea>
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

            <!-- Stand Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Stand Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="stand_laenge" class="block font-medium text-sm text-gray-700">Länge (Meter) <span class="text-red-600">*</span></label>
                        <input type="number" name="stand[laenge]" id="stand_laenge" required step="0.5" min="0"
                            value="{{ old('stand.laenge') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="stand_tiefe" class="block font-medium text-sm text-gray-700">Tiefe (Meter) <span class="text-red-600">*</span></label>
                        <input type="number" name="stand[tiefe]" id="stand_tiefe" required step="0.5" min="0"
                            value="{{ old('stand.tiefe') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="stand_flaeche" class="block font-medium text-sm text-gray-700">Fläche (m²)</label>
                        <input type="number" name="stand[flaeche]" id="stand_flaeche" step="0.1" min="0"
                            value="{{ old('stand.flaeche') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Standaufbau -->
                <div class="mt-6">
                    <label for="stand_aufbau" class="block font-medium text-sm text-gray-700">Standaufbau</label>
                    <textarea name="stand[aufbau]" id="stand_aufbau" rows="3"
                        placeholder="Unser Aufbau erfolgt durch Zelt/Pavillon, Verkaufshütte, Verkaufsanhänger, Marktschirm..."
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('stand.aufbau') }}</textarea>
                    <p class="text-sm text-gray-600 mt-1">Angabe nur für Standplatz im Außenbereich erforderlich.</p>
                </div>

                <!-- Wunsch-Standort -->
                @if(isset($standorteByMarkt[$selectedMarkt->id]) && $standorteByMarkt[$selectedMarkt->id]->count() > 0)
                <div class="mt-6">
                    <label for="wunsch_standort_id" class="block font-medium text-sm text-gray-700">Wunsch-Standort</label>
                    <select name="wunsch_standort_id" id="wunsch_standort_id"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Kein besonderer Wunsch</option>
                        @foreach($standorteByMarkt[$selectedMarkt->id] as $standort)
                        <option value="{{ $standort->id }}" {{ old('wunsch_standort_id') == $standort->id ? 'selected' : '' }}>
                            {{ $standort->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <p class="text-sm text-gray-600 mt-3">Die finale Standzuweisung erfolgt über den Veranstalter. </p>
                @endif
            </div>

            <!-- Warenangebot -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Warenangebot <span class="text-red-600">*</span></h2>
                @if(isset($subkategorienByMarkt[$selectedMarkt->id]))
                @php
                $gruppierteSubkategorien = $subkategorienByMarkt[$selectedMarkt->id]->groupBy('kategorie.name');
                @endphp
                @foreach($gruppierteSubkategorien as $kategorieName => $subkategorien)
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-700 mb-2">{{ $kategorieName }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($subkategorien as $subkategorie)
                        <label class="flex items-center">
                            <input type="checkbox" name="warenangebot[]" value="{{ $subkategorie->id }}"
                                {{ in_array($subkategorie->id, old('warenangebot', [])) ? 'checked' : '' }}
                                class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm">{{ $subkategorie->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @else
                <p class="text-gray-500">Keine Kategorien für diesen Markt verfügbar.</p>
                @endif

                <!-- Sonstiges Textfeld (wird nur bei aktivierter Checkbox angezeigt) -->
                <div id="sonstiges-container" class="mt-4 hidden">
                    <label for="warenangebot_sonstiges" class="block font-medium text-sm text-gray-700 mb-1">
                        Bitte beschreiben Sie "Sonstiges" genauer:
                    </label>
                    <textarea
                        name="warenangebot_sonstiges"
                        id="warenangebot_sonstiges"
                        rows="2"
                        placeholder="z.B. Handgefertigte Kerzen, Selbstgemachte Seifen..."
                        class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('warenangebot_sonstiges') }}</textarea>
                </div>

                <div class="border-t mt-3 pt-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="vorfuehrung_am_stand" value="1"
                            {{ old('vorfuehrung_am_stand') ? 'checked' : '' }}
                            class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">Ich führe handwerkliche Tätigkeiten am Stand vor</span>
                    </label>
                </div>
            </div>

            <!-- Herkunft der Ware -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Herkunft der Ware <span class="text-red-600">*</span></h2>
                <p class="text-sm text-gray-600 mb-4">Bitte geben Sie die prozentuale Aufteilung an</p>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="eigenfertigung" class="block font-medium text-sm text-gray-700">Eigenfertigung (%)</label>
                        <input type="number" name="herkunft[eigenfertigung]" id="eigenfertigung" min="0" max="100" required
                            value="{{ old('herkunft.eigenfertigung', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="industrieware" class="block font-medium text-sm text-gray-700">Industrieware (%)</label>
                        <input type="number" name="herkunft[industrieware]" id="industrieware" min="0" max="100" required
                            value="{{ old('herkunft.industrieware', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Zusätzliche Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Wünsche für Zusatzleistungen</h2>
                @if(isset($leistungenByMarkt[$selectedMarkt->id]) && $leistungenByMarkt[$selectedMarkt->id]->count() > 0)
                @php
                $leistungenGruppiert = $leistungenByMarkt[$selectedMarkt->id]->groupBy('kategorie');
                @endphp

                <!-- Miete-Leistungen (nur Anzeige) -->
                @if(isset($leistungenGruppiert['miete']))
                <div class="mb-6">
                    <h3 class="font-semibold text-sm text-gray-700 mb-2">Standgebühren</h3>
                    <div class="bg-gray-50 p-3 rounded">
                        @foreach($leistungenGruppiert['miete'] as $leistung)
                        <div class="flex justify-between items-center py-1">
                            <span class="text-sm">{{ $leistung->name }}</span>
                            <span class="text-sm font-medium">{{ number_format($leistung->preis / 100, 2, ',', '.') }} €</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Andere Leistungen (anklickbar, nach Kategorie gruppiert) -->
                @foreach($leistungenGruppiert as $kategorie => $leistungen)
                @if($kategorie !== 'miete')
                <div class="mb-4">
                    <h3 class="font-semibold text-sm text-gray-700 mb-2 capitalize">{{ ucfirst($kategorie) }}</h3>
                    <div class="space-y-2">
                        @foreach($leistungen as $leistung)
                        <label class="flex items-center">
                            <input type="checkbox" name="wuensche_zusatzleistungen[]" value="{{ $leistung->id }}"
                                {{ in_array($leistung->id, old('wuensche_zusatzleistungen', [])) ? 'checked' : '' }}
                                class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm">
                                {{ $leistung->name }}
                                @if($leistung->preis > 0)
                                ({{ number_format($leistung->preis / 100, 2, ',', '.') }} €)
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                @else
                <p class="text-gray-500 text-sm">Keine Zusatzleistungen verfügbar.</p>
                @endif
            </div>

            <!-- Gewünschtes Werbematerial -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Gewünschtes Werbematerial</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="plakate_a3" class="block text-sm text-gray-700">Plakate A3 (Anzahl)</label>
                        <input type="number" name="werbematerial[plakate_a3]" id="plakate_a3" min="0" max="100"
                            value="{{ old('werbematerial.plakate_a3', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="plakate_a1" class="block text-sm text-gray-700">Plakate A1 (Anzahl)</label>
                        <input type="number" name="werbematerial[plakate_a1]" id="plakate_a1" min="0" max="100"
                            value="{{ old('werbematerial.plakate_a1', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="flyer" class="block text-sm text-gray-700">Flyer (Anzahl)</label>
                        <input type="number" name="werbematerial[flyer]" id="flyer" min="0" max="1000"
                            value="{{ old('werbematerial.flyer', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center mt-6">
                        <input type="checkbox" name="werbematerial[social_media_post]" id="social_media_post" value="1"
                            {{ old('werbematerial.social_media_post') ? 'checked' : '' }}
                            class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="social_media_post" class="text-sm">Social Media Post erwünscht</label>
                    </div>
                </div>
            </div>

            <!-- Datei-Uploads -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Datei-Uploads</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">Detailfotos Warenangebot (max. 4 Bilder)</label>
                        <input type="file" name="detailfotos_warenangebot[]" multiple accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Erlaubte Formate: JPG, PNG, GIF (max. 5MB pro Bild)</p>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">Foto Verkaufsstand</label>
                        <input type="file" name="foto_verkaufsstand" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Erlaubte Formate: JPG, PNG, GIF (max. 5MB)</p>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">Foto Werkstatt</label>
                        <input type="file" name="foto_werkstatt" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Erlaubte Formate: JPG, PNG, GIF (max. 5MB)</p>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-2">Lebenslauf/Vita (PDF)</label>
                        <input type="file" name="lebenslauf_vita" accept=".pdf"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Nur PDF-Dateien (max. 10MB)</p>
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

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Anfrage absenden
                </button>
            </div>
        </form>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Sonstiges-Textfeld ein/ausblenden
            function setupSonstigesField() {
                const sonstigesCheckboxes = document.querySelectorAll('input[name="warenangebot[]"][value="24"]');
                const sonstigesContainer = document.getElementById('sonstiges-container');

                function toggleSonstigesField() {
                    let isChecked = false;
                    sonstigesCheckboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            isChecked = true;
                        }
                    });

                    if (sonstigesContainer) {
                        if (isChecked) {
                            sonstigesContainer.classList.remove('hidden');
                        } else {
                            sonstigesContainer.classList.add('hidden');
                            // Optional: Textfeld leeren wenn Checkbox deaktiviert wird
                            const textField = document.getElementById('warenangebot_sonstiges');
                            if (textField) {
                                textField.value = '';
                            }
                        }
                    }
                }

                // Event Listener für alle Sonstiges-Checkboxen
                sonstigesCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', toggleSonstigesField);
                });

                // Initial prüfen ob Sonstiges bereits gecheckt ist (z.B. bei old() values)
                toggleSonstigesField();
            }

            // Setup aufrufen - mit kleiner Verzögerung falls DOM noch nicht bereit
            setTimeout(setupSonstigesField, 100);

            // Validierung für Termine bei mehreren Checkboxen
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Nur Checkboxen prüfen, nicht hidden fields
                    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="termine[]"]');

                    // Wenn es Checkboxen gibt, prüfen ob mindestens eine ausgewählt ist
                    if (checkboxes.length > 0) {
                        let checked = false;
                        checkboxes.forEach(cb => {
                            if (cb.checked) checked = true;
                        });

                        if (!checked) {
                            e.preventDefault();
                            alert('Bitte wählen Sie mindestens einen Termin aus.');
                        }
                    }
                    // Wenn keine Checkboxen da sind (= nur ein Termin mit hidden field), 
                    // dann ist alles ok und das Formular wird normal submitted
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>