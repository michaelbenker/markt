<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategorieResource\Pages;
use App\Models\Kategorie;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KategorieResource extends Resource
{
    protected static ?string $model = Kategorie::class;
    protected static ?string $label = 'Kategorie';
    protected static ?string $pluralLabel = 'Kategorien';
    protected static ?string $navigationLabel = 'Hauptkategorie';
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        TextInput::make('name')->label('Name')->required(),
                        Textarea::make('bemerkung')->label('Bemerkung'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('bemerkung')->label('Bemerkung')->limit(100),
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
            'index' => Pages\ListKategories::route('/'),
            'create' => Pages\CreateKategorie::route('/create'),
            'edit' => Pages\EditKategorie::route('/{record}/edit'),
        ];
    }
}
