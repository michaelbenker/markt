@php($a = $this->record)
<x-filament::page>
    <x-filament::card>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
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
                    Art: {{ $a->stand['art'] ?? '-' }}, Länge: {{ $a->stand['laenge'] ?? '-' }}, Fläche: {{ $a->stand['flaeche'] ?? '-' }}
                    @else
                    {{ $a->stand }}
                    @endif
                </dd>

                <dt class="font-semibold">Warenangebot</dt>
                <dd>
                    @if(is_array($a->warenangebot))
                    {{ implode(', ', $a->warenangebot) }}
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

                <dt class="font-semibold">Bemerkung</dt>
                <dd>{{ $a->bemerkung }}</dd>

                <dt class="font-semibold text-gray-500 text-sm">Erstellt am</dt>
                <dd class="text-gray-500 text-sm">{{ $a->created_at?->format('d.m.Y H:i') }}</dd>
            </dl>
            <div>
                <dd>
                    @if(count($this->matchingAussteller))
                    <div class="grid gap-4 mb-4">
                        @foreach($this->matchingAussteller as $match)
                        @php($aus = $match['aussteller'])
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="font-bold text-lg mb-1">{{ $aus->firma ?? '-' }}</div>
                            <div>{{ $aus->name }}, {{ $aus->vorname }}</div>
                            <div>{{ $aus->email }} | {{ $aus->telefon }}</div>
                            <div>{{ $aus->plz }} {{ $aus->ort }}</div>
                            <div class="text-xs text-gray-500 mt-1">Treffer: {{ implode(', ', $match['criteria']) }}</div>
                            <div class="mt-2 flex flex-col gap-2">
                                @if($match['perfect'])
                                <form wire:submit.prevent="createBuchung({{ $aus->id }})">
                                    <x-filament::button color="success" class="w-full" type="submit">Buchung erzeugen</x-filament::button>
                                </form>
                                @else
                                <form wire:submit.prevent="updateAusstellerUndBuchung({{ $aus->id }})">
                                    <x-filament::button color="warning" class="w-full" type="submit">Aussteller-Daten aktualisieren und Buchung erzeugen</x-filament::button>
                                </form>
                                <form wire:submit.prevent="buchungMitDatenUebernehmen({{ $aus->id }})">
                                    <x-filament::button color="success" class="w-full" type="submit">Buchung erzeugen (Aussteller-Daten werden übernommen)</x-filament::button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-gray-500 mb-4">Kein passender Aussteller gefunden.</div>
                    @endif
                    <div class="flex flex-col gap-2 mt-4">
                        <form wire:submit.prevent="ausstellerNeuUndBuchung()">
                            <x-filament::button color="primary" class="w-full" type="submit">Aussteller neu anlegen und Buchung erzeugen</x-filament::button>
                        </form>
                        <form wire:submit.prevent="ausstellerNeuOhneBuchung()">
                            <x-filament::button color="gray" class="w-full" type="submit">Aussteller anlegen ohne Buchung</x-filament::button>
                        </form>
                        <form wire:submit.prevent="ausstellerAbsagen()">
                            <x-filament::button color="danger" class="w-full" type="submit">Aussteller absagen</x-filament::button>
                        </form>
                    </div>
                </dd>
            </div>
        </dl>
    </x-filament::card>
</x-filament::page>