<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeistungResource\Pages;
use App\Models\Leistung;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class LeistungResource extends Resource
{
    protected static ?string $model = Leistung::class;

    protected static ?string $label = 'Leistung';
    protected static ?string $pluralLabel = 'Leistungen';
    protected static ?string $navigationLabel = 'Leistungen';

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 20;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('kategorie')
                            ->required()
                            ->options(self::getKategorienOptions())
                            ->label('Kategorie'),

                        Textarea::make('bemerkung')
                            ->rows(3)
                            ->maxLength(255),

                        Forms\Components\Select::make('einheit')
                            ->required()
                            ->options(self::getEinheitenOptions())
                            ->label('Einheit'),

                        TextInput::make('preis')
                            ->required()
                            ->numeric()
                            ->label('Preis in €')
                            ->step(0.01)
                            ->formatStateUsing(fn($state) => $state / 100)
                            ->dehydrateStateUsing(fn($state) => (int) round($state * 100))
                    ])
                    ->columns(1),
            ]);
    }

    public static function getEinheitenOptions(): array
    {
        return [
            'stk' => 'Stück',
            'm' => 'Meter',
            'pauschal' => 'Pauschal',
        ];
    }

    public static function getKategorienOptions(): array
    {
        return [
            'miete' => 'Miete',
            'nebenkosten' => 'Nebenkosten',
            'mobiliar' => 'Mobiliar',
        ];
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('kategorie')
                ->label('Kategorie')
                ->formatStateUsing(fn($state) => self::getKategorienOptions()[$state] ?? $state),

            TextColumn::make('bemerkung')->limit(50),
            TextColumn::make('einheit')
                ->label('Einheit')
                ->formatStateUsing(fn($state) => self::getEinheitenOptions()[$state] ?? $state),
            TextColumn::make('preis')
                ->formatStateUsing(fn($state) => number_format($state / 100, 2, ',', '.') . ' €')

        ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeistung::route('/'),
            'create' => Pages\CreateLeistung::route('/create'),
            'edit' => Pages\EditLeistung::route('/{record}/edit'),
        ];
    }
}
