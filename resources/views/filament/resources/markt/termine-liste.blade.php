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
        
        {{-- Link zum öffentlichen Anfrageformular - nur wenn Termine vorhanden --}}
        @if($markt->slug)
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md border border-blue-200 dark:border-blue-800">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Öffentliches Anfrageformular:</p>
                        <a href="{{ url('/anfrage?markt=' . $markt->slug) }}" 
                           target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline break-all">
                            {{ url('/anfrage?markt=' . $markt->slug) }}
                        </a>
                    </div>
                    <button type="button"
                            onclick="navigator.clipboard.writeText('{{ url('/anfrage?markt=' . $markt->slug) }}'); 
                                     this.innerHTML = '✓ Kopiert!'; 
                                     setTimeout(() => this.innerHTML = 'Kopieren', 2000);"
                            class="px-3 py-1 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Kopieren
                    </button>
                </div>
            </div>
        @endif
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