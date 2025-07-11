@php
$content = $getState() ?? '';
$markdownRenderer = new \League\CommonMark\CommonMarkConverter();
$renderedContent = $markdownRenderer->convert($content);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Editor -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $getLabel() }}
        </label>
        <textarea
            wire:model.live="{{ $getStatePath() }}"
            class="w-full h-96 p-3 border border-gray-300 rounded-md font-mono text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Markdown-Inhalt eingeben...">{{ $content }}</textarea>
        @if($getHelperText())
        <p class="mt-1 text-sm text-gray-500">{{ $getHelperText() }}</p>
        @endif
    </div>

    <!-- Vorschau -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Vorschau
        </label>
        <div class="h-96 p-3 border border-gray-300 rounded-md overflow-y-auto bg-gray-50">
            <div class="prose prose-sm max-w-none">
                {!! $renderedContent !!}
            </div>
        </div>
    </div>
</div>