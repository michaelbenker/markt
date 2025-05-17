<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StandortResource\Pages;
use App\Filament\Resources\StandortResource\RelationManagers;
use App\Models\Standort;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Select::make('markt_id')
                            ->label('Markt')
                            ->relationship('markt', 'name')
                            ->required(),
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
                TextColumn::make('markt.name')->label('Markt'),
                TextColumn::make('flaeche')->label('Fläche'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListStandorts::route('/'),
            'create' => Pages\CreateStandort::route('/create'),
            'edit' => Pages\EditStandort::route('/{record}/edit'),
        ];
    }
}
