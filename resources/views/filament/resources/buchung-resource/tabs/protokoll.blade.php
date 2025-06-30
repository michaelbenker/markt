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
                <td class="px-2 py-1">{{ $p->details }}</td>
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