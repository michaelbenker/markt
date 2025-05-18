<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarktResource\Pages;
use App\Filament\Resources\MarktResource\RelationManagers;
use App\Models\Markt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarktResource extends Resource
{
    protected static ?string $model = Markt::class;
    protected static ?string $label = 'Markt';
    protected static ?string $pluralLabel = 'Märkte';
    protected static ?string $navigationLabel = 'Markt';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('bemerkung')->label('Bemerkung')->limit(100),
                TextColumn::make('url')->label('URL'),
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
            'index' => Pages\ListMarkts::route('/'),
            'create' => Pages\CreateMarkt::route('/create'),
            'edit' => Pages\EditMarkt::route('/{record}/edit'),
        ];
    }
}
