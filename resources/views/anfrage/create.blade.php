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
                        <label for="hausnummer" class="block font-medium text-sm text-gray-700">Hausnummer <span class="text-red-600">*</span></label>
                        <input type="text" name="hausnummer" required id="hausnummer" autocomplete="address-line2"
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
                        <p class="mt-1 text-sm text-gray-500">+Ländervorwahl Nummer (z.B. +49 176 12345678)</p>
                    </div>

                    <div>
                        <label for="telefon" class="block font-medium text-sm text-gray-700">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" autocomplete="tel"
                            value="{{ old('telefon') }}"
                            pattern="^\+[1-9]\d{1,14}$"
                            placeholder="+49 89 12345678"
                            title="Bitte geben Sie eine internationale Telefonnummer mit Ländervorwahl ein (z.B. +49 für Deutschland)"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">+Ländervorwahl Nummer (z.B. +49 89 12345678)</p>
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
                        <label for="eigenfertigung" class="block font-medium text-sm text-gray-700">Eigenfertigung (%) <span class="text-red-600">*</span></label>
                        <input type="number" name="herkunft[eigenfertigung]" id="eigenfertigung" min="0" max="100"
                            value="{{ old('herkunft.eigenfertigung', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="industrieware" class="block font-medium text-sm text-gray-700">Industrieware (%) <span class="text-red-600">*</span></label>
                        <input type="number" name="herkunft[industrieware]" id="industrieware" min="0" max="100"
                            value="{{ old('herkunft.industrieware', 0) }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Stand Informationen -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Stand Informationen</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="stand_laenge" class="block font-medium text-sm text-gray-700">Länge (Meter)</label>
                        <input type="number" name="stand[laenge]" id="stand_laenge" step="0.5" min="0"
                            value="{{ old('stand.laenge') }}"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="stand_tiefe" class="block font-medium text-sm text-gray-700">Tiefe (Meter)</label>
                        <input type="number" name="stand[tiefe]" id="stand_tiefe" step="0.5" min="0"
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
                        @if(strtolower($kategorie) === 'mobiliar')
                        <!-- Mobiliar mit Dropdown für Menge -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm">
                                {{ $leistung->name }}
                                @if($leistung->preis > 0)
                                ({{ number_format($leistung->preis / 100, 2, ',', '.') }} €)
                                @endif
                            </span>
                            <select name="wuensche_zusatzleistungen_menge[{{ $leistung->id }}]"
                                class="ml-4 text-sm rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('wuensche_zusatzleistungen_menge.'.$leistung->id, 0) == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                    </option>
                                    @endfor
                            </select>
                        </div>
                        @else
                        <!-- Andere Kategorien mit Checkbox -->
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
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 italic">Alle angegebenen Preise netto zzgl. 19% MwSt.</p>
                </div>
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
                        <div class="space-y-2">
                            @for($i = 1; $i <= 4; $i++)
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 w-16">Bild {{ $i }}:</span>
                                <input type="file" name="detailfotos_warenangebot[]" accept="image/*"
                                    class="block flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            @endfor
                        </div>
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
            // Client-seitige Formvalidierung
            const form = document.querySelector('form[method="POST"]');
            if (form && form.action && form.action.includes('/anfrage')) {
                
                // Funktion zum Entfernen von Fehlermeldungen
                function removeFieldError(element) {
                    // Entferne roten Rahmen
                    element.classList.remove('border-red-500');
                    
                    // Finde und entferne Fehlermeldung
                    const parent = element.closest('.grid') || element.parentElement || element;
                    const errorDivs = parent.querySelectorAll('.field-error');
                    errorDivs.forEach(div => div.remove());
                    
                    // Spezialfall für Container
                    const container = element.closest('.bg-white');
                    if (container) {
                        const containerErrors = container.querySelectorAll('.field-error');
                        containerErrors.forEach(div => div.remove());
                    }
                }
                
                // Event-Listener für Input-Felder
                form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea').forEach(input => {
                    input.addEventListener('input', function() {
                        if (this.value.trim()) {
                            removeFieldError(this);
                        }
                    });
                });
                
                // Event-Listener für Checkboxen (Warenangebot)
                form.querySelectorAll('input[name="warenangebot[]"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const checked = form.querySelectorAll('input[name="warenangebot[]"]:checked');
                        if (checked.length > 0) {
                            const container = this.closest('.bg-white');
                            if (container) {
                                const errors = container.querySelectorAll('.field-error');
                                errors.forEach(div => div.remove());
                            }
                        }
                    });
                });
                
                // Event-Listener für Herkunft-Felder
                const eigenfertigungInput = form.querySelector('[name="herkunft[eigenfertigung]"]');
                const industriewareInput = form.querySelector('[name="herkunft[industrieware]"]');
                
                function checkHerkunftSum() {
                    const eigen = parseInt(eigenfertigungInput.value) || 0;
                    const industrie = parseInt(industriewareInput.value) || 0;
                    if (eigen + industrie === 100) {
                        const container = eigenfertigungInput.closest('.grid');
                        if (container) {
                            const errors = container.querySelectorAll('.field-error');
                            errors.forEach(div => div.remove());
                        }
                    }
                }
                
                if (eigenfertigungInput) {
                    eigenfertigungInput.addEventListener('input', checkHerkunftSum);
                }
                if (industriewareInput) {
                    industriewareInput.addEventListener('input', checkHerkunftSum);
                }
                
                // Event-Listener für Stand-Felder
                const standLaenge = form.querySelector('[name="stand[laenge]"]');
                const standTiefe = form.querySelector('[name="stand[tiefe]"]');
                
                function checkStandFields() {
                    if ((standLaenge && standLaenge.value) || (standTiefe && standTiefe.value)) {
                        const container = standLaenge ? standLaenge.closest('.grid') : null;
                        if (container) {
                            const errors = container.querySelectorAll('.field-error');
                            errors.forEach(div => div.remove());
                        }
                    }
                }
                
                if (standLaenge) {
                    standLaenge.addEventListener('input', checkStandFields);
                }
                if (standTiefe) {
                    standTiefe.addEventListener('input', checkStandFields);
                }
                
                // Event-Listener für Datei-Uploads
                form.querySelectorAll('input[type="file"]').forEach(fileInput => {
                    fileInput.addEventListener('change', function() {
                        removeFieldError(this);
                    });
                });
                form.addEventListener('submit', function(e) {
                    // Alle vorherigen Fehler entfernen
                    document.querySelectorAll('.field-error').forEach(el => el.remove());
                    document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
                    
                    let firstErrorElement = null;
                    let hasErrors = false;
                    
                    // Hilfsfunktion zum Anzeigen von Fehlern
                    function showFieldError(element, message) {
                        hasErrors = true;
                        if (!firstErrorElement) {
                            firstErrorElement = element;
                        }
                        
                        // Füge roten Rahmen hinzu
                        if (element.classList.contains('rounded')) {
                            element.classList.add('border-red-500');
                        }
                        
                        // Erstelle Fehlermeldung
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error text-red-600 text-sm mt-1';
                        errorDiv.textContent = message;
                        
                        // Füge Fehlermeldung nach dem Element oder seinem Container ein
                        const parent = element.closest('.grid') || element.parentElement;
                        parent.appendChild(errorDiv);
                    }
                    
                    // Pflichtfelder prüfen
                    const requiredFields = [
                        { name: 'vorname', label: 'Vorname' },
                        { name: 'nachname', label: 'Nachname' },
                        { name: 'strasse', label: 'Straße' },
                        { name: 'plz', label: 'PLZ' },
                        { name: 'ort', label: 'Ort' },
                        { name: 'land', label: 'Land' },
                        { name: 'mobil', label: 'Mobilnummer' },
                        { name: 'email', label: 'E-Mail' }
                    ];
                    
                    requiredFields.forEach(field => {
                        const input = form.querySelector(`[name="${field.name}"]`);
                        if (!input || !input.value.trim()) {
                            showFieldError(input, `${field.label} ist ein Pflichtfeld.`);
                        }
                    });
                    
                    // E-Mail Format prüfen
                    const emailInput = form.querySelector('[name="email"]');
                    if (emailInput && emailInput.value) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(emailInput.value)) {
                            showFieldError(emailInput, 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
                        }
                    }
                    
                    // Telefonnummern Format prüfen
                    const mobilInput = form.querySelector('[name="mobil"]');
                    if (mobilInput && mobilInput.value) {
                        const phoneRegex = /^\+[1-9]\d{1,14}$/;
                        if (!phoneRegex.test(mobilInput.value)) {
                            showFieldError(mobilInput, 'Mobilnummer muss mit + beginnen (z.B. +49123456789).');
                        }
                    }
                    
                    const telefonInput = form.querySelector('[name="telefon"]');
                    if (telefonInput && telefonInput.value) {
                        const phoneRegex = /^\+[1-9]\d{1,14}$/;
                        if (!phoneRegex.test(telefonInput.value)) {
                            showFieldError(telefonInput, 'Telefonnummer muss mit + beginnen (z.B. +49123456789).');
                        }
                    }
                    
                    // Warenangebot prüfen (mindestens eine Kategorie)
                    const warenangebotCheckboxes = form.querySelectorAll('input[name="warenangebot[]"]:checked');
                    const warenangebotSection = form.querySelector('input[name="warenangebot[]"]');
                    if (warenangebotCheckboxes.length === 0 && warenangebotSection) {
                        const warenangebotContainer = warenangebotSection.closest('.bg-white');
                        if (warenangebotContainer) {
                            // Fehlermeldung direkt nach der Überschrift einfügen
                            const heading = warenangebotContainer.querySelector('h2');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'field-error bg-red-100 text-red-600 text-sm p-2 rounded mt-2';
                            errorDiv.textContent = 'Bitte wählen Sie mindestens eine Warenkategorie aus.';
                            
                            if (heading && heading.nextSibling) {
                                heading.parentNode.insertBefore(errorDiv, heading.nextSibling);
                            } else {
                                warenangebotContainer.appendChild(errorDiv);
                            }
                            
                            hasErrors = true;
                            if (!firstErrorElement) {
                                firstErrorElement = warenangebotContainer;
                            }
                        }
                    }
                    
                    // Herkunft Summe prüfen
                    const eigenfertigungInput = form.querySelector('[name="herkunft[eigenfertigung]"]');
                    const industriewareInput = form.querySelector('[name="herkunft[industrieware]"]');
                    if (eigenfertigungInput && industriewareInput) {
                        const eigen = parseInt(eigenfertigungInput.value) || 0;
                        const industrie = parseInt(industriewareInput.value) || 0;
                        if (eigen + industrie !== 100) {
                            const herkunftContainer = eigenfertigungInput.closest('.grid');
                            if (herkunftContainer) {
                                showFieldError(herkunftContainer, 'Die Summe von Eigenfertigung und Industrieware muss genau 100% ergeben.');
                            }
                        }
                    }
                    
                    // Stand-Informationen prüfen
                    const standLaenge = form.querySelector('[name="stand[laenge]"]');
                    const standTiefe = form.querySelector('[name="stand[tiefe]"]');
                    if ((!standLaenge || !standLaenge.value) && (!standTiefe || !standTiefe.value)) {
                        const standContainer = standLaenge ? standLaenge.closest('.grid') : null;
                        if (standContainer) {
                            showFieldError(standContainer, 'Bitte geben Sie mindestens Länge oder Tiefe des Standes an.');
                        }
                    }
                    
                    // Dateigröße prüfen
                    const imageInputs = form.querySelectorAll('input[type="file"][accept*="image"]');
                    imageInputs.forEach(input => {
                        if (input.files) {
                            Array.from(input.files).forEach(file => {
                                if (file.size > 5 * 1024 * 1024) { // 5MB
                                    showFieldError(input, `Die Datei "${file.name}" ist zu groß. Maximal 5MB erlaubt.`);
                                }
                            });
                        }
                    });
                    
                    const pdfInput = form.querySelector('input[name="lebenslauf_vita"]');
                    if (pdfInput && pdfInput.files && pdfInput.files[0]) {
                        if (pdfInput.files[0].size > 10 * 1024 * 1024) { // 10MB
                            showFieldError(pdfInput, 'Die PDF-Datei ist zu groß. Maximal 10MB erlaubt.');
                        }
                    }
                    
                    // Wenn Fehler vorhanden, Formular nicht absenden
                    if (hasErrors) {
                        e.preventDefault();
                        
                        // Zum ersten Fehler scrollen
                        if (firstErrorElement) {
                            const elementTop = firstErrorElement.getBoundingClientRect().top + window.pageYOffset - 100;
                            window.scrollTo({ top: elementTop, behavior: 'smooth' });
                        }
                    }
                });
            }

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
            const termineForm = document.querySelector('form');
            if (termineForm && !form) { // Nur wenn noch kein Event Listener existiert
                termineForm.addEventListener('submit', function(e) {
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