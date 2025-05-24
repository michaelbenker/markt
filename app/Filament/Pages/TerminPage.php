<?php

namespace App\Filament\Pages;

use App\Models\Markt;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class TerminPage extends Page implements HasTable
{
    use InteractsWithTable;
    public Markt $markt;

    protected static ?string $slug = 'markt-slug/{markt}/termine';
    protected static ?string $routeName = 'filament.admin.pages.termine';
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Termine';
    protected static string $view = 'filament.pages.termin-page';
    protected static bool $shouldRegisterNavigation = false;

    public function mount(Markt $markt): void
    {
        $this->markt = $markt;
    }

    protected function getTableQuery()
    {
        return $this->markt->termine()->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('start')->date(),
            Tables\Columns\TextColumn::make('ende')->date(),
            Tables\Columns\TextColumn::make('bemerkung')->limit(50),
        ];
    }

    public function getBreadcrumbs(): array
    {
        if (! $this->markt?->exists) {
            return [];
        }

        return [
            route('filament.admin.resources.markt.index') => 'Märkte',
            route('filament.admin.resources.markt.edit', $this->markt) => $this->markt->name,
            url()->current() => 'Termine',
        ];
    }
}
