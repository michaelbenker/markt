<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SystemInfo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'System-Info';
    protected static ?string $title = 'System-Informationen';
    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.system-info';

    public array $systemInfo = [];

    public function mount(): void
    {
        $this->systemInfo = $this->gatherSystemInfo();
    }

    protected function gatherSystemInfo(): array
    {
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
        
        // Finde installierte Versionen aus composer.lock
        $packages = collect($composerLock['packages'] ?? []);
        
        $laravelVersion = $packages->firstWhere('name', 'laravel/framework')['version'] ?? 'Unbekannt';
        $filamentVersion = $packages->firstWhere('name', 'filament/filament')['version'] ?? 'Unbekannt';
        
        // Bereinige Versionsnummern (entferne v-Prefix wenn vorhanden)
        $laravelVersion = ltrim($laravelVersion, 'v');
        $filamentVersion = ltrim($filamentVersion, 'v');
        
        $gitInfo = $this->getGitInfo();
        
        return [
            'app' => [
                'name' => config('app.name', 'Markt-Verwaltung'),
                'version' => $this->getAppVersion(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug') ? 'Aktiviert' : 'Deaktiviert',
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            
            'git' => $gitInfo,
            
            'server' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unbekannt',
                'operating_system' => php_uname('s') . ' ' . php_uname('r'),
                'architecture' => php_uname('m'),
                'max_execution_time' => ini_get('max_execution_time') . ' Sekunden',
                'memory_limit' => ini_get('memory_limit'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            
            'framework' => [
                'laravel_version' => $laravelVersion,
                'filament_version' => $filamentVersion,
                'php_requirement' => $composerJson['require']['php'] ?? 'Nicht definiert',
            ],
            
            'database' => [
                'connection' => config('database.default'),
                'driver' => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName(),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'charset' => config('database.connections.' . config('database.default') . '.charset'),
                'collation' => config('database.connections.' . config('database.default') . '.collation'),
                'prefix' => config('database.connections.' . config('database.default') . '.prefix') ?: 'Kein Prefix',
            ],
            
            'cache' => [
                'driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
                'mail_driver' => config('mail.default'),
            ],
            
            'paths' => [
                'base_path' => base_path(),
                'app_path' => app_path(),
                'storage_path' => storage_path(),
                'public_path' => public_path(),
                'database_path' => database_path(),
                'resource_path' => resource_path(),
            ],
            
            'dependencies' => $this->getMainDependencies($packages),
            
            'extensions' => [
                'bcmath' => extension_loaded('bcmath') ? 'Installiert' : 'Nicht installiert',
                'ctype' => extension_loaded('ctype') ? 'Installiert' : 'Nicht installiert',
                'curl' => extension_loaded('curl') ? 'Installiert' : 'Nicht installiert',
                'dom' => extension_loaded('dom') ? 'Installiert' : 'Nicht installiert',
                'fileinfo' => extension_loaded('fileinfo') ? 'Installiert' : 'Nicht installiert',
                'json' => extension_loaded('json') ? 'Installiert' : 'Nicht installiert',
                'mbstring' => extension_loaded('mbstring') ? 'Installiert' : 'Nicht installiert',
                'openssl' => extension_loaded('openssl') ? 'Installiert' : 'Nicht installiert',
                'pcre' => extension_loaded('pcre') ? 'Installiert' : 'Nicht installiert',
                'pdo' => extension_loaded('pdo') ? 'Installiert' : 'Nicht installiert',
                'pdo_mysql' => extension_loaded('pdo_mysql') ? 'Installiert' : 'Nicht installiert',
                'tokenizer' => extension_loaded('tokenizer') ? 'Installiert' : 'Nicht installiert',
                'xml' => extension_loaded('xml') ? 'Installiert' : 'Nicht installiert',
                'zip' => extension_loaded('zip') ? 'Installiert' : 'Nicht installiert',
                'gd' => extension_loaded('gd') ? 'Installiert' : 'Nicht installiert',
                'imagick' => extension_loaded('imagick') ? 'Installiert' : 'Nicht installiert',
            ],
        ];
    }
    
    protected function getGitInfo(): array
    {
        if (!is_dir(base_path('.git'))) {
            return [];
        }
        
        return [
            'branch' => trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?? 'unbekannt'),
            'commit' => trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? 'unbekannt'),
            'commit_date' => trim(shell_exec('git log -1 --format=%ci 2>/dev/null') ?? 'unbekannt'),
            'author' => trim(shell_exec('git log -1 --format=%an 2>/dev/null') ?? 'unbekannt'),
            'tag' => trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? 'kein Tag'),
        ];
    }
    
    protected function getAppVersion(): string
    {
        // Versuche Version aus verschiedenen Quellen zu ermitteln
        
        // 1. Aus Git-Tag (beste Methode)
        if (is_dir(base_path('.git'))) {
            // Letzter Tag
            $gitTag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? '');
            
            // Wenn kein Tag, dann aktuelle Commit-Hash (kurz)
            if (empty($gitTag)) {
                $gitCommit = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');
                if (!empty($gitCommit)) {
                    // Prüfe ob es uncommitted changes gibt
                    $gitStatus = trim(shell_exec('git status --porcelain 2>/dev/null') ?? '');
                    $isDirty = !empty($gitStatus);
                    
                    return 'dev-' . $gitCommit . ($isDirty ? '-dirty' : '');
                }
            } else {
                // Prüfe ob wir genau auf dem Tag sind oder commits danach haben
                $tagCommit = trim(shell_exec("git rev-list -n 1 {$gitTag} 2>/dev/null") ?? '');
                $currentCommit = trim(shell_exec('git rev-parse HEAD 2>/dev/null') ?? '');
                
                if ($tagCommit === $currentCommit) {
                    return $gitTag;
                } else {
                    // Anzahl der Commits seit dem Tag
                    $commitsSinceTag = trim(shell_exec("git rev-list {$gitTag}..HEAD --count 2>/dev/null") ?? '0');
                    $shortCommit = substr($currentCommit, 0, 7);
                    
                    if ($commitsSinceTag > 0) {
                        return "{$gitTag}-{$commitsSinceTag}-g{$shortCommit}";
                    }
                    
                    return $gitTag;
                }
            }
        }
        
        // 2. Aus .env Variable (falls definiert)
        if (config('app.version')) {
            return config('app.version');
        }
        
        // 3. Aus VERSION Datei (falls vorhanden)
        if (file_exists(base_path('VERSION'))) {
            $version = trim(file_get_contents(base_path('VERSION')));
            if (!empty($version)) {
                return $version;
            }
        }
        
        // 4. Aus composer.json (falls dort definiert)
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        if (isset($composerJson['version'])) {
            return $composerJson['version'];
        }
        
        // 5. Standard-Fallback
        return '1.0.0';
    }
    
    protected function getMainDependencies($packages): array
    {
        $mainPackages = [
            'filament/filament' => 'Filament',
            'laravel/framework' => 'Laravel Framework',
            'livewire/livewire' => 'Livewire',
            'barryvdh/laravel-dompdf' => 'DomPDF',
            'maatwebsite/excel' => 'Laravel Excel',
            'spatie/laravel-medialibrary' => 'Media Library',
            'filament/spatie-laravel-media-library-plugin' => 'Filament Media Library',
            'awcodes/filament-tiptap-editor' => 'Tiptap Editor',
            'mokhosh/filament-rating' => 'Rating Field',
            'parallax/filament-comments' => 'Comments',
        ];
        
        $dependencies = [];
        
        foreach ($mainPackages as $package => $label) {
            $packageInfo = $packages->firstWhere('name', $package);
            if ($packageInfo) {
                $dependencies[$label] = ltrim($packageInfo['version'] ?? 'Unbekannt', 'v');
            }
        }
        
        return $dependencies;
    }
}