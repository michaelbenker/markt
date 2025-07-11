@php
$content = $content ?? '';
$markdownRenderer = new \League\CommonMark\CommonMarkConverter();
$rendered = $content ? $markdownRenderer->convert($content) : '<p class="text-gray-500 italic">Markdown-Inhalt eingeben für Vorschau</p>';
@endphp

<div class="prose prose-sm max-w-none p-4 bg-gray-50 rounded-lg border" style="height: 700px; overflow-y: auto;"
    wire:poll.500ms>
    {!! $rendered !!}
</div>