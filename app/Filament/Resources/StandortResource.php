<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StandortResource\Pages;
use App\Filament\Resources\StandortResource\RelationManagers;
use App\Models\Standort;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Models\Markt;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StandortResource extends Resource
{
    protected static ?string $model = Standort::class;
    protected static ?string $label = 'Standort';
    protected static ?string $pluralLabel = 'Standorte';
    protected static ?string $navigationLabel = 'Standorte';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'standort';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        CheckboxList::make('maerkte')
                            ->label('Märkte')
                            ->relationship('maerkte', 'name')
                            ->options(Markt::all()->pluck('name', 'id'))
                            ->columns(2),
                        TextInput::make('name')->label('Name')->required(),
                        Textarea::make('beschreibung')->label('Beschreibung'),
                        TextInput::make('flaeche')
                            ->label('Fläche in m²')
                            ->numeric()
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Standort'),
                TextColumn::make('maerkte_names')
                    ->label('Märkte')
                    ->getStateUsing(function ($record) {
                        return $record->maerkte->pluck('name')->implode(', ');
                    }),
                TextColumn::make('flaeche')->label('Fläche'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStandort::route('/'),
            'create' => Pages\CreateStandort::route('/create'),
            'edit' => Pages\EditStandort::route('/{record}/edit'),
        ];
    }
}
