<div class="space-y-2">
    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Buchungen des Ausstellers</h3>
    
    @if($aussteller && $aussteller->buchungen && $aussteller->buchungen->count() > 0)
        <div class="space-y-2">
            @foreach($aussteller->buchungen->sortByDesc('created_at') as $buchung)
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $buchung->markt->name ?? 'Markt unbekannt' }}
                                </span>
                                @if($buchung->status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($buchung->status === 'bestätigt') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($buchung->status === 'angefragt') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($buchung->status === 'storniert') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($buchung->status) }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="text-xs text-gray-600 dark:text-gray-400 space-y-0.5">
                                @if($buchung->nummer)
                                    <div>Buchungsnummer: {{ $buchung->nummer }}</div>
                                @endif
                                
                                @if($buchung->markt && $buchung->markt->termine && $buchung->markt->termine->count() > 0)
                                    <div>
                                        Termine: 
                                        @foreach($buchung->markt->termine->sortBy('start') as $termin)
                                            @if($termin->ende)
                                                {{ $termin->start->format('d.m.Y') }} - {{ $termin->ende->format('d.m.Y') }}
                                            @else
                                                {{ $termin->start->format('d.m.Y') }}
                                            @endif
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if($buchung->standort)
                                    <div>Standort: {{ $buchung->standort->name }}</div>
                                @endif
                                
                                @if($buchung->staende && $buchung->staende->count() > 0)
                                    <div>
                                        Stände: {{ $buchung->staende->pluck('nummer')->join(', ') }}
                                        ({{ $buchung->staende->count() }} {{ $buchung->staende->count() === 1 ? 'Stand' : 'Stände' }})
                                    </div>
                                @endif
                                
                                @if($buchung->leistungen && $buchung->leistungen->count() > 0)
                                    <div>{{ $buchung->leistungen->count() }} zusätzliche {{ $buchung->leistungen->count() === 1 ? 'Leistung' : 'Leistungen' }}</div>
                                @endif
                                
                                @if($buchung->gesamtpreis)
                                    <div class="font-medium text-gray-700 dark:text-gray-300">
                                        Gesamtpreis: {{ number_format($buchung->gesamtpreis / 100, 2, ',', '.') }} €
                                    </div>
                                @endif
                                
                                <div class="text-gray-500 dark:text-gray-500">
                                    Erstellt: {{ $buchung->created_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-1 ml-4">
                            <a href="{{ route('filament.admin.resources.buchung.edit', $buchung) }}" 
                               target="_blank"
                               class="text-xs text-primary-600 hover:text-primary-500 underline whitespace-nowrap">
                                Bearbeiten
                            </a>
                            @if($buchung->status === 'bestätigt')
                                <a href="{{ route('buchung.pdf', $buchung) }}" 
                                   target="_blank"
                                   class="text-xs text-primary-600 hover:text-primary-500 underline whitespace-nowrap">
                                    PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-md">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <strong>Zusammenfassung:</strong> 
                {{ $aussteller->buchungen->count() }} {{ $aussteller->buchungen->count() === 1 ? 'Buchung' : 'Buchungen' }} insgesamt
                @if($aussteller->buchungen->where('status', 'bestätigt')->count() > 0)
                    ({{ $aussteller->buchungen->where('status', 'bestätigt')->count() }} bestätigt)
                @endif
            </div>
        </div>
    @else
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
            <p class="text-sm text-gray-500 dark:text-gray-400">Keine Buchungen vorhanden</p>
        </div>
    @endif
</div>