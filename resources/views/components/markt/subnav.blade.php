<nav class="mb-6 border-b border-gray-200 dark:border-gray-700 -mb-px flex space-x-4" aria-label="Tabs">
    <a
        href="{{ route('filament.admin.resources.markt.edit', $markt) }}"
        class="text-sm font-medium border-b-2 px-3 py-2 {{ request()->routeIs('filament.admin.resources.markt.edit') ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        Allgemein
    </a>
    <a
        href="{{ route('filament.admin.pages.markt-slug.{markt}.termine', ['markt' => $markt->slug]) }}"
        class="text-sm font-medium border-b-2 px-3 py-2 {{ request()->routeIs('filament.admin.pages.markt-slug.{markt}.termine') ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        Termine
    </a>
</nav>