<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubkategorieResource\Pages;
use App\Filament\Resources\SubkategorieResource\RelationManagers;
use App\Models\Subkategorie;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubkategorieResource extends Resource
{
    protected static ?string $model = Subkategorie::class;
    protected static ?string $label = 'Subkategorie';
    protected static ?string $pluralLabel = 'Subkategorien';
    protected static ?string $navigationLabel = 'Subkategorie';
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'subkategorie';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Select::make('kategorie_id')
                            ->label('Kategorie')
                            ->relationship('kategorie', 'name')
                            ->required(),
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
                TextColumn::make('kategorie.name')->label('Hauptkategorie'),
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
            'index' => Pages\ListSubkategorie::route('/'),
            'create' => Pages\CreateSubkategorie::route('/create'),
            'edit' => Pages\EditSubkategorie::route('/{record}/edit'),
        ];
    }
}
