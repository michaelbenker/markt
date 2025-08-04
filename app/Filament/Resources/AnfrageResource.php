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
                    ->label('Vom')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('markt')
                    ->label('Markt')
                    ->formatStateUsing(function ($state, $record) {
                        $markt = $record->markt;
                        if (!$markt) return '-';
                        $name = $markt->name ?? '-';
                        $datum = $markt->termine?->sortBy('start')->first()?->start;
                        $datumStr = $datum ? \Carbon\Carbon::parse($datum)->format('d.m.Y') : '-';
                        return $name . ' (' . $datumStr . ')';
                    }),
                Tables\Columns\TextColumn::make('firma')
                    ->label('Firma')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nachname')
                    ->label('Name')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->nachname . ', ' . $record->vorname;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('ort')
                    ->label('Ort')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warenangebot')
                    ->label('Warenangebot')
                    ->getStateUsing(function ($record) {
                        if (!is_array($record->warenangebot) || empty($record->warenangebot)) {
                            return '';
                        }
                        return \App\Models\Subkategorie::whereIn('id', $record->warenangebot)->pluck('name')->implode(', ');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Keine Edit-Action
            ])
            ->headerActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnfrage::route('/'),
            'view' => Pages\ViewAnfrage::route('/{record}'),
        ];
    }
}
