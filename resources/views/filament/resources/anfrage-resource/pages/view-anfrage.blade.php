@php($a = $this->record ?? null)
<x-filament::page>
    @if(!$a)
    <div class="text-center text-gray-500">Anfrage nicht gefunden</div>
    @else
    <x-filament::card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <div>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <dt class="font-semibold">Firma</dt>
                    <dd>{{ $a->firma }}</dd>

                    <dt class="font-semibold">Name</dt>
                    <dd>{{ $a->nachname }}, {{ $a->vorname }}</dd>

                    <dt class="font-semibold">Anschrift</dt>
                    <dd>{{ $a->strasse }} {{ $a->hausnummer }}</dd>

                    <dt class="font-semibold">Ort</dt>
                    <dd>{{ $a->plz }} {{ $a->ort }} ({{ $a->land }})</dd>

                    <dt class="font-semibold">Telefon</dt>
                    <dd>{{ $a->telefon }}</dd>

                    <dt class="font-semibold">E-Mail</dt>
                    <dd>{{ $a->email }}</dd>

                    <dt class="font-semibold">Stand</dt>
                    <dd>
                        @if(is_array($a->stand))
                        Länge: {{ $a->stand['laenge'] ?? '-' }}m, Tiefe: {{ $a->stand['tiefe'] ?? '-' }}m, Fläche: {{ $a->stand['flaeche'] ?? '-' }}m²
                        @else
                        {{ $a->stand }}
                        @endif
                    </dd>

                    <dt class="font-semibold">Warenangebot</dt>
                    <dd>
                        @if(is_array($a->warenangebot))
                        {{ implode(', ', \App\Models\Subkategorie::whereIn('id', $a->warenangebot)->pluck('name')->toArray()) }}
                        @else
                        {{ $a->warenangebot }}
                        @endif
                    </dd>

                    <dt class="font-semibold">Herkunft</dt>
                    <dd>
                        @if(is_array($a->herkunft))
                        Eigenfertigung: {{ $a->herkunft['eigenfertigung'] ?? '-' }}%,
                        Industrieware (nicht Entwicklungsländer): {{ $a->herkunft['industrieware_nicht_entwicklungslaender'] ?? '-' }}%,
                        Industrieware (Entwicklungsländer): {{ $a->herkunft['industrieware_entwicklungslaender'] ?? '-' }}%
                        @else
                        {{ $a->herkunft }}
                        @endif
                    </dd>

                    <dt class="font-semibold">Bereits ausgestellt?</dt>
                    <dd>{{ $a->bereits_ausgestellt ? 'Ja' : 'Nein' }}</dd>

                    <dt class="font-semibold">Vorführung des Handwerkes am eigenen Stand?</dt>
                    <dd>{{ $a->vorfuehrung_am_stand ? 'Ja' : 'Nein' }}</dd>

                    @if($a->wunschStandort)
                    <dt class="font-semibold">Wunschstandort</dt>
                    <dd>{{ $a->wunschStandort->name }}</dd>
                    @endif

                    @if($a->soziale_medien)
                    <dt class="font-semibold">Soziale Medien</dt>
                    <dd>
                        @if($a->soziale_medien['website'] ?? null)
                        <div>Website: <a href="{{ $a->soziale_medien['website'] }}" target="_blank" class="text-blue-600 hover:underline">{{ $a->soziale_medien['website'] }}</a></div>
                        @endif
                        @if($a->soziale_medien['facebook'] ?? null)
                        <div>Facebook: <a href="{{ $a->soziale_medien['facebook'] }}" target="_blank" class="text-blue-600 hover:underline">{{ $a->soziale_medien['facebook'] }}</a></div>
                        @endif
                        @if($a->soziale_medien['instagram'] ?? null)
                        <div>Instagram: {{ $a->soziale_medien['instagram'] }}</div>
                        @endif
                        @if($a->soziale_medien['twitter'] ?? null)
                        <div>Twitter: {{ $a->soziale_medien['twitter'] }}</div>
                        @endif
                    </dd>
                    @endif

                    @if($a->wuensche_zusatzleistungen && count($a->wuensche_zusatzleistungen) > 0)
                    <dt class="font-semibold">Wünsche für Zusatzleistungen</dt>
                    <dd>{{ implode(', ', $a->wuensche_zusatzleistungen) }}</dd>
                    @endif

                    @if($a->werbematerial)
                    <dt class="font-semibold">Gewünschtes Werbematerial</dt>
                    <dd>
                        @if(is_array($a->werbematerial) && count($a->werbematerial) > 0)
                        @foreach($a->werbematerial as $item)
                        @if(is_array($item) && isset($item['typ']))
                        <div class="mb-1">
                            {{ ucfirst(str_replace('_', ' ', $item['typ'])) }}: {{ $item['anzahl'] }} Stück
                            @if($item['physisch'] ?? false)
                            <span class="text-sm text-gray-600">(physisch)</span>
                            @endif
                            @if($item['digital'] ?? false)
                            <span class="text-sm text-gray-600">(digital)</span>
                            @endif
                        </div>
                        @endif
                        @endforeach
                        @else
                        <div class="text-gray-500">Kein Werbematerial im erwarteten Format gefunden</div>
                        @endif
                    </dd>
                    @endif

                    <dt class="font-semibold">Bemerkung</dt>
                    <dd>{{ $a->bemerkung }}</dd>

                    <dt class="font-semibold text-gray-500 text-sm">Erstellt am</dt>
                    <dd class="text-gray-500 text-sm">{{ $a->created_at?->format('d.m.Y H:i') }}</dd>
                </dl>

                @if($a->medien->count() > 0)
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">📎 Medien</h3>
                    @include('filament.components.medien-manager', [
                    'getRecord' => function() use ($a) { return $a; },
                    'uploadEnabled' => false
                    ])
                </div>
                @endif
            </div>
            <div>
                @if(count($this->matchingAussteller))
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">🔍 Gefundene Aussteller</h3>
                    <div class="grid gap-4">
                        @foreach($this->matchingAussteller as $match)
                        @php($aus = $match['aussteller'])
                        @php($differences = $this->getAusstellerDifferences($aus))
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="font-bold text-lg mb-1">{{ $aus->firma ?? '-' }}</div>
                            <div class="text-gray-700">{{ $aus->name }}, {{ $aus->vorname }}</div>
                            <div class="text-gray-600">{{ $aus->email }} | {{ $aus->telefon }}</div>
                            <div class="text-gray-600">{{ $aus->plz }} {{ $aus->ort }}</div>

                            @if(count($differences) > 0)
                            <div class="mt-2 mb-4 p-2 bg-amber-50 border border-amber-200 rounded text-sm">
                                <div class="font-medium text-amber-800 mb-1">⚠️ Unterschiede gefunden:</div>
                                <ul class="text-amber-700 list-disc list-inside">
                                    @foreach($differences as $diff)
                                    <li>{{ $diff }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="flex gap-2 items-center">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox"
                                        wire:model.defer="updateData.{{ $aus->id }}"
                                        @if(count($differences)> 0) checked @endif
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="text-gray-700">
                                        @if(count($differences) > 0)
                                        Geänderte Daten übernehmen
                                        @else
                                        Aussteller-Daten aus Anfrage aktualisieren
                                        @endif
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="border-t pt-6"></div>
                @endif

                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 lg:gap-5">
                        <form wire:submit.prevent="ausstellerNeuUndBuchung()">
                            <x-filament::button color="success" class="w-full" type="submit" icon="heroicon-o-plus-circle">
                                Buchung anlegen
                            </x-filament::button>
                        </form>
                        <form wire:submit.prevent="ausstellerNeuOhneBuchung()">
                            <x-filament::button color="info" class="w-full" type="submit" icon="heroicon-o-user-plus">
                                Nur Aussteller anlegen
                            </x-filament::button>
                        </form>
                        <form wire:submit.prevent="ausstellerAbsagen()">
                            <x-filament::button color="danger" class="w-full" type="submit" icon="heroicon-o-x-circle">
                                Aussteller absagen
                            </x-filament::button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::card>
    @endif
</x-filament::page>