@php($a = $this->record ?? null)
<x-filament::page>
    @if(!$a)
    <div class="text-center text-gray-500">Anfrage nicht gefunden</div>
    @else
    <x-filament::card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <div>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <dt class="font-semibold">Markt</dt>
                    <dd>{{ $a->markt->name ?? 'Unbekannt' }}</dd>

                    <dt class="font-semibold">Gewünschte Termine</dt>
                    <dd>
                        @if($a->termine && count($a->termine) > 0)
                        @foreach($a->termine as $termin)
                        <div>{{ \Carbon\Carbon::parse($termin->start)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($termin->ende)->format('d.m.Y') }}</div>
                        @endforeach
                        @else
                        Keine Termine ausgewählt
                        @endif
                    </dd>

                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>

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

                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>

                    <dt class="font-semibold">Stand</dt>
                    <dd>
                        @if(is_array($a->stand))
                        Länge: {{ $a->stand['laenge'] ?? '-' }}m, Tiefe: {{ $a->stand['tiefe'] ?? '-' }}m, Fläche: {{ $a->stand['flaeche'] ?? '-' }}m²
                        @else
                        {{ $a->stand }}
                        @endif
                    </dd>

                    <dt class="font-semibold">Warenangebot</dt>
                    <dd>{{ $this->getWarenangebotText() }}</dd>

                    <dt class="font-semibold">Herkunft</dt>
                    <dd>
                        @if(is_array($a->herkunft))
                        Eigenfertigung: {{ $a->herkunft['eigenfertigung'] ?? '-' }}%,
                        Industrieware: {{ $a->herkunft['industrieware'] ?? '-' }}%
                        @else
                        {{ $a->herkunft }}
                        @endif
                    </dd>

                    <dt class="font-semibold">Bereits ausgestellt?</dt>
                    <dd>{{ $a->bereits_ausgestellt ? $a->bereits_ausgestellt : 'Nein' }}</dd>

                    <dt class="font-semibold">Vorführung des Handwerkes am eigenen Stand?</dt>
                    <dd>{{ $a->vorfuehrung_am_stand ? 'Ja' : 'Nein' }}</dd>

                    @if($a->wunschStandort)
                    <dt class="font-semibold">Wunschstandort</dt>
                    <dd>{{ $a->wunschStandort->name }}</dd>
                    @endif

                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>

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

                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>

                    <dt class="font-semibold">Wünsche für Zusatzleistungen</dt>
                    <dd>
                        @foreach($a->gewuenschteLeistungen() as $leistung)
                        <span class="inline-block bg-gray-100 rounded px-2 py-1 text-sm mr-2 mb-1">
                            {{ $leistung->name }}
                            <span class="text-gray-600">({{ number_format($leistung->preis / 100, 2, ',', '.') }} € / {{ $leistung->einheit }})</span>
                        </span>
                        @endforeach
                    </dd>
                    @endif

                    @if($a->werbematerial)
                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>
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

                    <dt class="md:col-span-2 border-t pt-2"></dt>
                    <dd class="hidden"></dd>

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

                <!-- Tags und Bewertung Section -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold mb-3">Bewertung & Tags für neuen Aussteller</h3>

                    <!-- Rating -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bewertung</label>
                        <div class="flex gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                wire:click="$set('selectedRating', {{ $i }})"
                                class="text-2xl transition-colors duration-200 {{ $i <= ($this->selectedRating ?? 0) ? 'text-primary-500' : 'text-gray-300' }} hover:text-yellow-400">
                                ★
                                </button>
                                @endfor
                                @if($this->selectedRating ?? 0)
                                <button type="button"
                                    wire:click="$set('selectedRating', 0)"
                                    class="ml-2 text-sm text-gray-500 hover:text-gray-700">
                                    Zurücksetzen
                                </button>
                                @endif
                        </div>
                        <span class="text-sm text-gray-600">{{ $this->selectedRating ?? 0 }} von 5 Sternen</span>
                    </div>

                    <!-- Tags als MultiSelect mit Alpine.js -->
                    <div class="fi-fo-field-wrp"
                        x-data="{ 
                             open: false,
                             selectedTags: @entangle('selectedTags'),
                             tags: @js(\App\Models\Tag::pluck('name', 'id'))
                         }">
                        <div class="grid gap-y-2">
                            <label class="fi-fo-field-wrp-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Tags
                            </label>
                            <div class="relative">
                                <div @click="open = !open" class="fi-input-wrp flex min-h-[2.625rem] w-full rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 transition duration-75 hover:bg-gray-50 dark:bg-white/5 dark:ring-white/20 dark:hover:bg-white/10 cursor-pointer">
                                    <div class="flex-1 flex flex-wrap gap-1">
                                        <template x-for="tagId in selectedTags" :key="tagId">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10">
                                                <span x-text="tags[tagId]"></span>
                                                <button @click.stop="selectedTags = selectedTags.filter(id => id !== tagId)" type="button" class="hover:text-primary-900">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                        <span x-show="selectedTags.length === 0" class="text-gray-400 text-sm">Tags auswählen...</span>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="max-h-60 overflow-auto py-1">
                                        @foreach(\App\Models\Tag::orderBy('type')->orderBy('name')->get() as $tag)
                                        <div @click="selectedTags.includes({{ $tag->id }}) ? selectedTags = selectedTags.filter(id => id !== {{ $tag->id }}) : selectedTags.push({{ $tag->id }})"
                                            class="px-3 py-2 cursor-pointer transition-colors"
                                            :class="selectedTags.includes({{ $tag->id }}) ? 'bg-primary-100 text-primary-900' : 'hover:bg-gray-100'">
                                            {{ $tag->name }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 lg:gap-5">
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
                        <form wire:submit.prevent="aufWartelisteSetzen()">
                            <x-filament::button color="warning" class="w-full" type="submit" icon="heroicon-o-clock">
                                Auf Warteliste setzen
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