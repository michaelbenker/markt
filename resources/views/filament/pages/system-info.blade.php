<x-filament-panels::page>
    <div class="space-y-6">

        {{-- App Informationen --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cube class="w-5 h-5 text-primary-500" />
                    Anwendung
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['app'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- Git Informationen --}}
        @if(isset($systemInfo['git']) && count($systemInfo['git']) > 0)
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-code-bracket-square class="w-5 h-5 text-primary-500" />
                    Git-Informationen
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['git'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>
        @endif

        {{-- Framework Versionen --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-code-bracket class="w-5 h-5 text-primary-500" />
                    Framework & Versionen
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        PHP Version
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $systemInfo['server']['php_version'] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Laravel Version
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $systemInfo['framework']['laravel_version'] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Filament Version
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $systemInfo['framework']['filament_version'] }}
                    </dd>
                </div>
            </dl>
        </x-filament::section>

        {{-- Wichtige Pakete --}}
        @if(count($systemInfo['dependencies']) > 0)
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-puzzle-piece class="w-5 h-5 text-primary-500" />
                    Installierte Pakete
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['dependencies'] as $package => $version)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $package }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $version }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>
        @endif

        {{-- Server Informationen --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-server class="w-5 h-5 text-primary-500" />
                    Server-Umgebung
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['server'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- Datenbank --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-circle-stack class="w-5 h-5 text-primary-500" />
                    Datenbank
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['database'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- Cache & Treiber --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog class="w-5 h-5 text-primary-500" />
                    Cache & Treiber
                </div>
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systemInfo['cache'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- PHP Extensions --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-puzzle-piece class="w-5 h-5 text-primary-500" />
                    PHP Extensions
                </div>
            </x-slot>

            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($systemInfo['extensions'] as $extension => $status)
                <div class="flex items-center gap-2">
                    @if($status === 'Installiert')
                    <x-heroicon-o-check-circle class="w-4 h-4 text-success-500" />
                    @else
                    <x-heroicon-o-x-circle class="w-4 h-4 text-danger-500" />
                    @endif
                    <span class="text-sm {{ $status === 'Installiert' ? 'text-gray-900 dark:text-gray-100' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ strtoupper($extension) }}
                    </span>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- System-Pfade --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-folder class="w-5 h-5 text-primary-500" />
                    System-Pfade
                </div>
            </x-slot>

            <dl class="space-y-2">
                @foreach($systemInfo['paths'] as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ str_replace('_', ' ', ucfirst($key)) }}
                    </dt>
                    <dd class="mt-1 text-xs font-mono text-gray-700 dark:text-gray-300 break-all">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-filament::section>

    </div>
</x-filament-panels::page>