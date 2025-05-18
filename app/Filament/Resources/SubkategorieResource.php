<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubkategorieResource\Pages;
use App\Filament\Resources\SubkategorieResource\RelationManagers;
use App\Models\Subkategorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubkategorieResource extends Resource
{
    protected static ?string $model = Subkategorie::class;

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
            'index' => Pages\ListSubkategories::route('/'),
            'create' => Pages\CreateSubkategorie::route('/create'),
            'edit' => Pages\EditSubkategorie::route('/{record}/edit'),
        ];
    }
}
