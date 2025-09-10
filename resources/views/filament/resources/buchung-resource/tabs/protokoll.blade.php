@php($protokolle = $getRecord() ? $getRecord()->protokolle()->latest()->get() : collect())
<div class="overflow-x-auto">
    <table class="min-w-full w-full text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-2 py-1 text-left">Datum</th>
                <th class="px-2 py-1 text-left">Aktion</th>
                <th class="px-2 py-1 text-left">von</th>
                <th class="px-2 py-1 text-left">nach</th>
                <th class="px-2 py-1 text-left">User</th>
                <th class="px-2 py-1 text-left">Details</th>
                <th class="px-2 py-1 text-left">Aktion</th>
            </tr>
        </thead>
        <tbody>
            @forelse($protokolle as $p)
            <tr class="border-b">
                <td class="px-2 py-1 whitespace-nowrap">{{ $p->created_at->format('d.m.Y H:i') }}</td>
                <td class="px-2 py-1">{{ $p->aktion }}</td>
                <td class="px-2 py-1">{{ $p->from_status }}</td>
                <td class="px-2 py-1">{{ $p->to_status }}</td>
                <td class="px-2 py-1">{{ $p->user?->name ?? '-' }}</td>
                <td class="px-2 py-1">
                    {{ $p->details }}
                    @if($p->aktion === 'bestaetigung_gesendet' || $p->aktion === 'absage_gesendet')
                        <?php
                            // Suche nach dem zugehörigen Mail-Log
                            $mailReport = \App\Models\MailReport::where('source_type', 'Buchung')
                                ->where('source_id', $getRecord()->id)
                                ->where('created_at', '>=', $p->created_at->subMinutes(2))
                                ->where('created_at', '<=', $p->created_at->addMinutes(2))
                                ->orderBy('created_at', 'desc')
                                ->first();
                        ?>
                        @if($mailReport)
                            <br>
                            <a href="{{ route('filament.admin.resources.mail-reports.view', $mailReport->id) }}" 
                               target="_blank"
                               class="text-sm text-blue-600 hover:text-blue-800 underline">
                                <svg class="inline-block w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                E-Mail Log anzeigen
                            </a>
                        @endif
                    @endif
                </td>
                <td class="px-2 py-1">
                    @if(!empty($p->daten))
                    <div x-data="{ open: false }" @keydown.window.escape="open = false">
                        <button type="button" class="text-blue-600 underline hover:text-blue-800" @click="open = true">Ansehen</button>
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @click.self="open = false">
                            <!-- Hintergrund -->
                            <div class="fixed inset-0 w-full h-full bg-black bg-opacity-60 z-40"></div>
                            <!-- Modal -->
                            <div class="relative z-50 flex flex-col bg-white rounded shadow-lg max-w-2xl w-full max-h-screen" style="max-height:100vh;">
                                <div class="flex items-center justify-between p-6 pb-2 border-b">
                                    <h2 class="text-lg font-semibold pr-4 mb-0">Daten</h2>
                                    <button type="button" class="text-2xl text-gray-500 hover:text-gray-700 ml-4" @click="open = false" style="line-height:1;">&times;</button>
                                </div>
                                <div class="overflow-auto p-6 pt-4" style="max-height:60vh;">
                                    <pre class="whitespace-pre-wrap text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ is_array($p->daten) ? json_encode($p->daten, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $p->daten }}</pre>
                                </div>
                                <div class="flex justify-end p-6 pt-2 border-t">
                                    <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm" @click="open = false">Schließen</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-2 py-2 text-center text-gray-400">Keine Protokoll-Einträge vorhanden.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>