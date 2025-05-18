<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HauptkategorieResource\Pages;
use App\Filament\Resources\HauptkategorieResource\RelationManagers;
use App\Models\Hauptkategorie;
use App\Models\Kategorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HauptkategorieResource extends Resource
{
    protected static ?string $model = Kategorie::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListHauptkategories::route('/'),
            'create' => Pages\CreateHauptkategorie::route('/create'),
            'edit' => Pages\EditHauptkategorie::route('/{record}/edit'),
        ];
    }
}
