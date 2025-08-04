<div class="space-y-2">
    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Geplante Termine</h3>
    
    @if($markt && $markt->termine && $markt->termine->count() > 0)
        <div class="space-y-1">
            @foreach($markt->termine->sortBy('start') as $termin)
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $termin->start->locale('de')->isoFormat('DD.MM.YYYY') }} - {{ $termin->ende->locale('de')->isoFormat('DD.MM.YYYY') }}
                        @if($termin->bemerkung)
                            <span class="text-xs text-gray-500">({{ Str::limit($termin->bemerkung, 30) }})</span>
                        @endif
                    </span>
                    <a href="{{ route('filament.admin.resources.termin.edit', $termin) }}" 
                       class="text-xs text-primary-600 hover:text-primary-500 underline">
                        Bearbeiten
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-2">
            <a href="{{ route('filament.admin.resources.termin.create', ['markt_id' => $markt->id]) }}" 
               class="text-sm text-primary-600 hover:text-primary-500 underline">
                + Neuen Termin hinzufügen
            </a>
        </div>
    @else
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
            <p class="text-sm text-gray-500 dark:text-gray-400">Keine Termine geplant</p>
            @if($markt)
                <a href="{{ route('filament.admin.resources.termin.create', ['markt_id' => $markt->id]) }}" 
                   class="text-sm text-primary-600 hover:text-primary-500 underline">
                    + Ersten Termin hinzufügen
                </a>
            @endif
        </div>
    @endif
</div>