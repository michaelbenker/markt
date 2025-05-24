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
            // \App\Filament\Resources\MarktResource\RelationManagers\TermineRelationManager::class,
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
        $first = Markt::query()->first();

        return $first
            ? static::getUrl('edit', ['record' => $first->slug])
            : static::getUrl('index');
    }
}
