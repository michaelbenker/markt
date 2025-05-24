<x-filament::page>

    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <x-markt.subnav :markt="$markt" />
    </div>

    {{ $this->table }}
</x-filament::page>