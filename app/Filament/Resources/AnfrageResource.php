<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnfrageResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Anfrage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

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
                        
                        // Neue Struktur: warenangebot ist ein Array mit 'subkategorien' und optional 'sonstiges'
                        $subkategorienIds = $record->warenangebot['subkategorien'] ?? [];
                        $sonstiges = $record->warenangebot['sonstiges'] ?? null;
                        
                        if (empty($subkategorienIds)) {
                            return $sonstiges ?: '';
                        }
                        
                        $namen = \App\Models\Subkategorie::whereIn('id', $subkategorienIds)->pluck('name')->toArray();
                        
                        // Wenn Sonstiges vorhanden und ID 24 in den Subkategorien ist, füge den Text hinzu
                        if ($sonstiges && in_array(24, $subkategorienIds)) {
                            $namen[] = "Sonstiges: " . $sonstiges;
                        }
                        
                        return implode(', ', $namen);
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'offen' => 'Offen',
                        'gebucht' => 'Gebucht',
                        'warteschlange' => 'Warteliste',
                        'aussteller_importiert' => 'Importiert',
                        'abgesagt' => 'Abgesagt',
                        default => ucfirst($state)
                    })
                    ->color(fn($state) => match($state) {
                        'offen' => 'warning',
                        'gebucht' => 'success',
                        'warteschlange' => 'info',
                        'aussteller_importiert' => 'gray',
                        'abgesagt' => 'danger',
                        default => 'secondary'
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'offen' => 'Offen',
                        'gebucht' => 'Gebucht',
                        'warteschlange' => 'Warteliste',
                        'aussteller_importiert' => 'Importiert',
                        'abgesagt' => 'Abgesagt',
                    ])
                    ->default('offen'),
                SelectFilter::make('markt_id')
                    ->label('Markt')
                    ->relationship('markt', 'name')
                    ->preload()
                    ->searchable(),
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
