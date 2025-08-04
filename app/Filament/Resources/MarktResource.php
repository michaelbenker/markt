<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarktResource\Pages;
use App\Filament\Resources\MarktResource\RelationManagers;
use App\Models\Markt;
use App\Models\Subkategorie;
use App\Models\Kategorie;
use App\Models\Standort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\View;

class MarktResource extends Resource
{
    protected static ?string $model = Markt::class;
    protected static ?string $label = 'Markt';
    protected static ?string $pluralLabel = 'Märkte';
    protected static ?string $navigationLabel = 'Markt';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'markt';


    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Tabs::make('Markt')
                    ->columnSpan('full')
                    ->persistTabInQueryString()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Allgemein')
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->regex('/^[a-zA-Z0-9_-]+$/')
                                    ->helperText('Nur Buchstaben, Zahlen, Unterstriche und Bindestriche erlaubt. Wird für die URL verwendet.'),

                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required(),

                                Forms\Components\Textarea::make('bemerkung')
                                    ->label('Bemerkung')
                                    ->rows(4)
                                    ->nullable(),

                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->nullable(),

                                Forms\Components\View::make('filament.resources.markt.termine-liste')
                                    ->viewData(function ($record) {
                                        return ['markt' => $record];
                                    }),
                            ]),

                        Forms\Components\Tabs\Tab::make('Kategorien')
                            ->schema([
                                Forms\Components\CheckboxList::make('subkategorien')
                                    ->label('Zugelassene Subkategorien')
                                    ->options(function () {
                                        $kategorien = Kategorie::with('subkategorien')->get();
                                        $options = [];

                                        foreach ($kategorien as $kategorie) {
                                            foreach ($kategorie->subkategorien as $subkategorie) {
                                                $options[$subkategorie->id] = $kategorie->name . ' → ' . $subkategorie->name;
                                            }
                                        }

                                        return $options;
                                    })
                                    ->columns(2)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->helperText('Wählen Sie die Subkategorien aus, die für diesen Markt zugelassen sind.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Standorte')
                            ->schema([
                                Forms\Components\CheckboxList::make('standorte')
                                    ->label('Zugewiesene Standorte')
                                    ->relationship('standorte', 'name')
                                    ->options(Standort::all()->pluck('name', 'id'))
                                    ->columns(2)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->helperText('Wählen Sie die Standorte aus, die für diesen Markt verfügbar sind.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Leistungen')
                            ->schema([
                                Forms\Components\CheckboxList::make('leistungen')
                                    ->label('Verfügbare Leistungen')
                                    ->relationship('leistungen', 'name')
                                    ->options(function () {
                                        $leistungen = \App\Models\Leistung::all();
                                        $options = [];

                                        foreach ($leistungen as $leistung) {
                                            $preis = number_format($leistung->preis / 100, 2, ',', '.') . ' €';
                                            $options[$leistung->id] = $leistung->name . ' (' . $preis . ' / ' . $leistung->einheit . ')';
                                        }

                                        return $options;
                                    })
                                    ->columns(2)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->helperText('Wählen Sie die Leistungen aus, die für diesen Markt verfügbar sind.'),
                            ]),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('bemerkung')->label('Bemerkung')->limit(100),
                TextColumn::make('url')->label('URL'),
                TextColumn::make('termine')
                    ->label('Termine')
                    ->formatStateUsing(function ($record) {
                        $termine = $record->termine;
                        if ($termine->isEmpty()) {
                            return 'Keine';
                        }
                        return $termine->count() . ' Termine';
                    })
                    ->tooltip(function ($record) {
                        $termine = $record->termine;
                        if ($termine->isEmpty()) {
                            return 'Keine Termine geplant';
                        }
                        return $termine->map(function ($termin) {
                            return $termin->start->format('d.m.Y') . ' - ' . $termin->ende->format('d.m.Y');
                        })->join(', ');
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            // Termine sind jetzt eine separate Resource
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarkt::route('/'),
            'create' => Pages\CreateMarkt::route('/create'),
            'edit' => Pages\EditMarkt::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'slug';
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }
}
