<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnfrageResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Anfrage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnfrageResource extends Resource
{
    protected static ?string $model = Anfrage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Anfragen';
    protected static ?string $pluralLabel = 'Anfragen';
    protected static ?string $navigationLabel = 'Anfragen';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'anfrage';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('importiert', false))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum der Anfrage')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('markt.name')
                    ->label('Markt')
                    ->formatStateUsing(fn($state, $record) => $record->markt?->name ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('markt.start')
                    ->label('Markt-Datum')
                    ->formatStateUsing(fn($state, $record) => $record->markt?->start ? \Carbon\Carbon::parse($record->markt->start)->format('d.m.Y') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('firma')
                    ->label('Firma')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vorname')
                    ->label('Vorname')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nachname')
                    ->label('Nachname')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ort')
                    ->label('Ort')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warenangebot')
                    ->label('Warenangebot')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Keine Edit-Action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnfrage::route('/'),
        ];
    }
}
