<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuchungResource\Pages;
use App\Filament\Resources\BuchungResource\RelationManagers;
use App\Models\Buchung;
use App\Models\Leistung;
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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\ToggleButtons;

class BuchungResource extends Resource
{
    protected static ?string $model = Buchung::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Buchung';
    protected static ?string $pluralLabel = 'Buchungen';
    protected static ?string $navigationLabel = 'Buchungen';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {

        return $form->schema([
            Tabs::make('Buchung')
                ->columnSpan('full')
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Allgemein')
                        ->schema([
                            ToggleButtons::make('status')
                                ->options([
                                    'anfrage' => 'Anfrage',
                                    'bestätigt' => 'Bestätigt',
                                    'abgelehnt' => 'Abgelehnt',
                                ])
                                ->colors([
                                    'anfrage' => 'info',
                                    'bestätigt' => 'success',
                                    'abgelehnt' => 'danger',
                                ])
                                ->icons([
                                    'anfrage' => 'heroicon-o-clock',
                                    'bestätigt' => 'heroicon-o-check-circle',
                                    'abgelehnt' => 'heroicon-o-x-circle',
                                ])
                                ->inline()
                                ->required(),
                            Select::make('termin_id')
                                ->relationship('termin', 'start')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->markt->name} | " . self::formatDateRange($record->start, $record->ende))
                                ->required(),
                            Select::make('standort_id')->relationship('standort', 'name')->required(),
                            TextInput::make('standplatz')->required(),
                            Select::make('aussteller_id')
                                ->relationship('aussteller', 'name')
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $parts = [];

                                    if ($record->firma) {
                                        $parts[] = $record->firma;
                                    }

                                    if ($record->vorname && $record->name) {
                                        $parts[] = "{$record->name}, {$record->vorname}";
                                    } elseif ($record->name) {
                                        $parts[] = $record->name;
                                    }

                                    return implode(' | ', $parts);
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Tab::make('Waren')
                        ->schema([
                            Section::make('Stand')
                                ->schema([
                                    Select::make('stand.art')
                                        ->label('Art')
                                        ->options([
                                            'klein' => 'Klein',
                                            'mittel' => 'Mittel',
                                            'groß' => 'Groß',
                                        ]),
                                    TextInput::make('stand.laenge')
                                        ->label('Länge (m)')
                                        ->numeric(),
                                    TextInput::make('stand.flaeche')
                                        ->label('Fläche (m²)')
                                        ->numeric(),
                                ])
                                ->columns(3),
                            Select::make('warenangebot')
                                ->label('Warenangebot')
                                ->multiple()
                                ->options([
                                    'kleidung' => 'Kleidung',
                                    'schmuck' => 'Schmuck',
                                    'kunst' => 'Kunst',
                                    'accessoires' => 'Accessoires',
                                    'dekoration' => 'Dekoration',
                                    'lebensmittel' => 'Lebensmittel',
                                    'getraenke' => 'Getränke',
                                    'handwerk' => 'Handwerk',
                                    'antiquitäten' => 'Antiquitäten',
                                    'sonstiges' => 'Sonstiges',
                                ])
                                ->searchable()
                                ->preload(),
                            Section::make('Herkunft der Waren')
                                ->schema([
                                    TextInput::make('herkunft.eigenfertigung')
                                        ->label('Eigenfertigung (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%'),
                                    TextInput::make('herkunft.industrieware_nicht_entwicklungslaender')
                                        ->label('Industrieware (nicht Entwicklungsland) (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%'),
                                    TextInput::make('herkunft.industrieware_entwicklungslaender')
                                        ->label('Industrieware (Entwicklungsland) (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%'),
                                ])
                                ->columns(3),
                        ]),
                    Tab::make('Gebuchte Leistungen')
                        ->schema([
                            Forms\Components\Repeater::make('leistungen')
                                ->relationship('leistungen')
                                ->label(false)
                                ->schema([
                                    Select::make('leistung_id')
                                        ->label('Leistung')
                                        ->relationship('leistung', 'name')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $leistung = \App\Models\Leistung::find($state);
                                                if ($leistung) {
                                                    $set('preis', $leistung->preis / 100);
                                                }
                                            }
                                        }),

                                    TextInput::make('preis')
                                        ->label('Preis (€)')
                                        ->numeric()
                                        ->formatStateUsing(fn($state) => $state / 100)
                                        ->dehydrateStateUsing(fn($state) => (int) round($state * 100))
                                        ->default(fn($record) => $record?->preis),

                                    TextInput::make('menge')
                                        ->label('Menge')
                                        ->numeric()
                                        ->default(fn($record) => $record?->menge ?? 1),
                                ])
                                ->columns(3)
                                ->addActionLabel('Leistung hinzufügen')
                                ->reorderable(true)
                                ->defaultItems(0)
                                ->helperText('Bitte nach dem Hinzufügen und Entfernen einer Leistung manuell speichern.'),
                        ]),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'anfrage' => 'info',
                        'bestätigt' => 'success',
                        'abgelehnt' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'anfrage' => 'heroicon-o-clock',
                        'bestätigt' => 'heroicon-o-check-circle',
                        'abgelehnt' => 'heroicon-o-x-circle',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('termin.markt.name')
                    ->label('Markt')
                    ->sortable(),
                TextColumn::make('termin.start')
                    ->label('Termin')
                    ->formatStateUsing(fn($record) => self::formatDateRange($record->termin->start, $record->termin->ende))
                    ->sortable(),
                TextColumn::make('standort.name'),
                TextColumn::make('standplatz'),
                TextColumn::make('aussteller.name'),
            ])
            ->defaultSort('created_at', 'desc')
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
            ])->defaultSort('created_at', 'desc');
    }

    protected static function formatDateRange($start, $ende): string
    {
        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($ende);

        if ($startDate->format('m') === $endDate->format('m')) {
            // Gleicher Monat
            return $startDate->format('d.') . '-' . $endDate->format('d.m.Y');
        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
            // Gleiches Jahr, aber unterschiedlicher Monat
            return $startDate->format('d.m.') . '-' . $endDate->format('d.m.Y');
        } else {
            // Unterschiedliche Jahre
            return $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuchungs::route('/'),
            'create' => Pages\CreateBuchung::route('/create'),
            'edit' => Pages\EditBuchung::route('/{record}/edit'),
        ];
    }
}
