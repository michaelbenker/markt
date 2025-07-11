<div class="space-y-4">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold text-lg mb-2">{{ $template->name }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ $template->description }}</p>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <strong>Template-Key:</strong> {{ $template->key }}
            </div>
            <div>
                <strong>Status:</strong>
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $template->is_active ? 'Aktiv' : 'Inaktiv' }}
                </span>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        <div>
            <h4 class="font-medium text-gray-900 mb-1">E-Mail-Betreff:</h4>
            <div class="bg-blue-50 p-3 rounded border-l-4 border-blue-400">
                {{ $template->subject }}
            </div>
        </div>

        <div>
            <h4 class="font-medium text-gray-900 mb-1">E-Mail-Inhalt:</h4>
            <div class="bg-white border rounded p-4 max-h-96 overflow-y-auto">
                {!! $template->content !!}
            </div>
        </div>

        @if($template->key === 'rechnung_versand')
        <div class="bg-green-50 p-4 rounded border border-green-200">
            <h4 class="font-medium text-green-800 mb-2">Test mit echter RechnungMail-Klasse:</h4>
            <p class="text-sm text-green-700 mb-3">Testet das Template mit der echten Mail-Klasse und einer Test-Rechnung.</p>
            <button
                onclick="testRealMail()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                📧 Echte Mail testen
            </button>
            <div id="test-result" class="mt-3 hidden"></div>
        </div>

        <script>
            function testRealMail() {
                const button = event.target;
                const resultDiv = document.getElementById('test-result');

                button.disabled = true;
                button.textContent = 'Teste...';

                fetch('/admin/test-real-mail/rechnung', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.className = data.success ?
                            'mt-3 p-3 bg-green-100 text-green-800 rounded' :
                            'mt-3 p-3 bg-red-100 text-red-800 rounded';
                        resultDiv.textContent = data.message;
                        resultDiv.classList.remove('hidden');
                    })
                    .catch(error => {
                        resultDiv.className = 'mt-3 p-3 bg-red-100 text-red-800 rounded';
                        resultDiv.textContent = 'Fehler: ' + error.message;
                        resultDiv.classList.remove('hidden');
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.textContent = '📧 Echte Mail testen';
                    });
            }
        </script>
        @endif

        @if($template->available_variables && count($template->available_variables) > 0)
        <div>
            <h4 class="font-medium text-gray-900 mb-2">Verfügbare Platzhalter:</h4>
            <div class="bg-yellow-50 p-3 rounded">
                <div class="grid grid-cols-1 gap-2">
                    @foreach($template->available_variables as $variable)
                    <div class="flex items-center space-x-2">
                        <code class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $variable['variable'] ?? $variable }}</code>
                        <span class="text-sm text-gray-600">{{ $variable['description'] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>