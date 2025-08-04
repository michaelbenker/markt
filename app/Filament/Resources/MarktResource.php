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
                // View::make('components.markt.subnav')
                //     ->viewData(fn(\Livewire\Component $livewire): array => [
                //         'markt' => $livewire->record,
                //     ]),


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

                Forms\Components\CheckboxList::make('standorte')
                    ->label('Zugewiesene Standorte')
                    ->relationship('standorte', 'name')
                    ->options(Standort::all()->pluck('name', 'id'))
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable()
                    ->gridDirection('row')
                    ->helperText('Wählen Sie die Standorte aus, die für diesen Markt verfügbar sind.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('bemerkung')->label('Bemerkung')->limit(100),
                TextColumn::make('url')->label('URL'),
                TextColumn::make('subkategorien')
                    ->label('Subkategorien')
                    ->formatStateUsing(function ($record) {
                        if (!$record->subkategorien) {
                            return 'Keine';
                        }
                        
                        $subkategorien = Subkategorie::whereIn('id', $record->subkategorien)
                            ->with('kategorie')
                            ->get();
                        
                        return $subkategorien->count() . ' Subkategorien';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->subkategorien) {
                            return 'Keine Subkategorien zugewiesen';
                        }
                        
                        $subkategorien = Subkategorie::whereIn('id', $record->subkategorien)
                            ->with('kategorie')
                            ->get()
                            ->map(fn($sub) => $sub->kategorie->name . ' → ' . $sub->name);
                        
                        return $subkategorien->join(', ');
                    }),
                TextColumn::make('standorte')
                    ->label('Standorte')
                    ->formatStateUsing(function ($record) {
                        $standorte = $record->standorte;
                        if ($standorte->isEmpty()) {
                            return 'Keine';
                        }
                        return $standorte->count() . ' Standorte';
                    })
                    ->tooltip(function ($record) {
                        $standorte = $record->standorte;
                        if ($standorte->isEmpty()) {
                            return 'Keine Standorte zugewiesen';
                        }
                        return $standorte->pluck('name')->join(', ');
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
            \App\Filament\Resources\MarktResource\RelationManagers\TermineRelationManager::class,
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
