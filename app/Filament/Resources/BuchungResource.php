<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuchungResource\Pages;
use App\Filament\Resources\BuchungResource\RelationManagers;
use App\Models\Buchung;
use App\Models\Leistung;
use App\Models\Subkategorie;
use App\Models\Kategorie;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;

class BuchungResource extends Resource
{
    protected static ?string $model = Buchung::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Buchung';
    protected static ?string $pluralLabel = 'Buchungen';
    protected static ?string $navigationLabel = 'Buchungen';
    protected static ?string $slug = 'buchung';


    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->disabled(fn($record) => $record && $record->status === 'abgelehnt')
            ->schema([
                Tabs::make('Buchung')
                    ->columnSpan('full')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Allgemein')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->options([
                                        'anfrage' => 'Anfrage',
                                        'bearbeitung' => 'Bearbeitung',
                                        'bestätigt' => 'Bestätigt',
                                        'erledigt' => 'Erledigt',
                                        'abgelehnt' => 'Abgelehnt',
                                    ])
                                    ->colors([
                                        'anfrage' => 'info',
                                        'bearbeitung' => 'warning',
                                        'bestätigt' => 'success',
                                        'erledigt' => 'gray',
                                        'abgelehnt' => 'danger',
                                    ])
                                    ->icons([
                                        'anfrage' => 'heroicon-o-clock',
                                        'bearbeitung' => 'heroicon-o-arrow-path',
                                        'bestätigt' => 'heroicon-o-check-circle',
                                        'erledigt' => 'heroicon-o-check-badge',
                                        'abgelehnt' => 'heroicon-o-x-circle',
                                    ])
                                    ->inline()
                                    ->required(),
                                Select::make('markt_id')
                                    ->label('Markt')
                                    ->options(function () {
                                        // Nur Märkte mit zukünftigen Terminen anzeigen
                                        return \App\Models\Markt::whereHas('termine', function ($query) {
                                            $query->where('start', '>', now());
                                        })->pluck('name', 'id');
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Wenn Markt gewechselt wird, Termine zurücksetzen
                                        $set('termine', []);
                                    })
                                    ->required(),
                                CheckboxList::make('termine')
                                    ->label('Termine')
                                    ->options(function (callable $get, $record) {
                                        $marktId = $get('markt_id');
                                        if (!$marktId) {
                                            return [];
                                        }

                                        // Bei bestehenden Records: Alle Termine des Marktes anzeigen (nicht nur zukünftige)
                                        $query = \App\Models\Termin::where('markt_id', $marktId);

                                        // Bei neuen Records: Nur zukünftige Termine
                                        if (!$record) {
                                            $query->where('start', '>', now());
                                        }

                                        return $query->orderBy('start')
                                            ->get()
                                            ->mapWithKeys(function ($termin) {
                                                $label = self::formatDateRange($termin->start, $termin->ende);
                                                return [$termin->id => $label];
                                            });
                                    })
                                    ->columns([
                                        'sm' => 1,  // Mobile: 1 Spalte
                                        'md' => 3,  // Tablet: 2 Spalten
                                        'lg' => 6,  // Desktop: 3 Spalten
                                    ])
                                    ->required()
                                    ->visible(fn(callable $get) => $get('markt_id') !== null),
                                Select::make('standort_id')->relationship('standort', 'name')->required(),
                                TextInput::make('standplatz')->required(),
                                Select::make('aussteller_id')
                                    ->relationship('aussteller', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => self::formatAusstellerName($record))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->suffixAction(
                                        Action::make('view_aussteller')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn($record) => route('filament.admin.resources.aussteller.edit', [
                                                'record' => $record?->aussteller_id,
                                                'return' => route('filament.admin.resources.buchung.edit', ['record' => $record?->id])
                                            ]))
                                            ->visible(fn($record) => $record?->aussteller_id !== null)
                                    ),
                                Textarea::make('bemerkung')->label('Bemerkung')->rows(4),
                            ]),
                        Tab::make('Werbematerial')
                            ->schema([
                                Forms\Components\Repeater::make('werbematerial')
                                    ->label(false)
                                    ->schema([
                                        Select::make('typ')
                                            ->label('Typ')
                                            ->options([
                                                'flyer' => 'Flyer',
                                                'brochure' => 'Broschüre',
                                                'plakat_a3' => 'Plakat A3',
                                                'plakat_a1' => 'Plakat A1',
                                                'social_media' => 'Social Media Post',
                                            ])
                                            ->required(),
                                        TextInput::make('anzahl')
                                            ->label('Anzahl')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(1)
                                            ->required(),
                                        Forms\Components\Toggle::make('physisch')
                                            ->label('Physisch')
                                            ->inline(false)
                                            ->default(true),
                                        Forms\Components\Toggle::make('digital')
                                            ->label('Digital')
                                            ->inline(false)
                                            ->default(false),
                                    ])
                                    ->columns(4)
                                    ->addActionLabel('Werbematerial hinzufügen')
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->helperText('Füge verschiedene Werbematerialien hinzu.'),
                            ]),
                        Tab::make('Gebuchte Leistungen')
                            ->schema([
                                Forms\Components\Repeater::make('leistungen')
                                    ->relationship('leistungen')
                                    ->label(false)
                                    ->schema([
                                        Select::make('leistung_id')
                                            ->label('Leistung')
                                            ->options(function (callable $get) {
                                                $record = $get('../../');
                                                if ($record && isset($record['termin_id'])) {
                                                    $termin = \App\Models\Termin::find($record['termin_id']);
                                                    if ($termin && $termin->markt) {
                                                        return $termin->markt->leistungen()->pluck('leistung.name', 'leistung.id');
                                                    }
                                                }
                                                return \App\Models\Leistung::pluck('name', 'id');
                                            })
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
                                    ->helperText('Nach dem Hinzufügen, Sortieren und Entfernen einer Leistung manuell speichern.'),
                            ]),
                        Tab::make('Protokoll')
                            ->schema([
                                \Filament\Forms\Components\View::make('filament.resources.buchung-resource.tabs.protokoll')
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
                        'bearbeitung' => 'warning',
                        'bestätigt' => 'success',
                        'erledigt' => 'gray',
                        'abgelehnt' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'anfrage' => 'heroicon-o-clock',
                        'bearbeitung' => 'heroicon-o-arrow-path',
                        'bestätigt' => 'heroicon-o-check-circle',
                        'erledigt' => 'heroicon-o-check-badge',
                        'abgelehnt' => 'heroicon-o-x-circle',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('markt.name')
                    ->label('Markt')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('termine')
                    ->label('Termine')
                    ->formatStateUsing(function($record) {
                        if (!$record->termine || !is_array($record->termine) || count($record->termine) === 0) {
                            return 'Keine Termine';
                        }
                        
                        $terminObjekte = \App\Models\Termin::whereIn('id', $record->termine)->orderBy('start')->get();
                        $terminStrings = [];
                        
                        foreach ($terminObjekte as $termin) {
                            $terminStrings[] = self::formatDateRange($termin->start, $termin->ende);
                        }
                        
                        return implode(', ', $terminStrings);
                    })
                    ->sortable(false),
                TextColumn::make('standort.name')
                    ->searchable(),
                TextColumn::make('standplatz')
                    ->searchable(),
                TextColumn::make('aussteller.name')
                    ->formatStateUsing(fn($record) => Str::limit(self::formatAusstellerName($record->aussteller), 30))
                    ->tooltip(fn($record) => self::formatAusstellerName($record->aussteller))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'anfrage' => 'Anfrage',
                        'bearbeitung' => 'Bearbeitung',
                        'bestätigt' => 'Bestätigt',
                        'erledigt' => 'Erledigt',
                        'abgelehnt' => 'Abgelehnt',
                    ])
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('markt')
                    ->relationship('markt', 'name')
                    ->label('Markt'),
                Tables\Filters\SelectFilter::make('aussteller')
                    ->relationship('aussteller', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Aussteller'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Von'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label('Erstellt am'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->iconSize('lg')
                    ->tooltip('Buchung bearbeiten'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->iconSize('lg')
                    ->tooltip('Buchung löschen'),
                Tables\Actions\Action::make('E-Mail senden')
                    ->label('')
                    ->action(function ($record) {
                        // Lade die benötigten Relationen explizit
                        $record->load(['markt', 'standort', 'aussteller']);
                        
                        $mailService = new \App\Services\MailService();
                        $result = $mailService->sendAusstellerBestaetigung($record);
                        
                        if ($result) {
                            \Filament\Notifications\Notification::make()
                                ->title('E-Mail erfolgreich versendet')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Fehler beim E-Mail-Versand')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-envelope')
                    ->iconSize('lg')
                    ->tooltip('E-Mail an den Aussteller senden'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([15, 30, 50, 100])
            ->defaultPaginationPageOption(15);
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

    protected static function formatAusstellerName($aussteller): string
    {
        $parts = [];

        if ($aussteller->firma) {
            $parts[] = $aussteller->firma;
        }

        if ($aussteller->vorname && $aussteller->name) {
            $parts[] = "{$aussteller->name}, {$aussteller->vorname}";
        } elseif ($aussteller->name) {
            $parts[] = $aussteller->name;
        }

        return implode(' | ', $parts);
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
