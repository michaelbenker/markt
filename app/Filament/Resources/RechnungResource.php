<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RechnungResource\Pages;
use App\Models\Rechnung;
use App\Models\Aussteller;
use App\Models\Buchung;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class RechnungResource extends Resource
{
    protected static ?string $model = Rechnung::class;
    protected static ?string $label = 'Rechnung';
    protected static ?string $pluralLabel = 'Rechnungen';
    protected static ?string $navigationLabel = 'Rechnungen';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'rechnungen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Rechnung')
                    ->columnSpan('full')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Grunddaten')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('rechnungsnummer')
                                        ->label('Rechnungsnummer')
                                        ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                        ->unique(ignoreRecord: true)
                                        ->disabled(fn($record) => $record && !$record->isEditable())
                                        ->placeholder('Wird automatisch generiert')
                                        ->helperText('Leer lassen für automatische Generierung'),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft' => 'Entwurf',
                                            'sent' => 'Versendet',
                                            'paid' => 'Bezahlt',
                                            'overdue' => 'Überfällig',
                                            'canceled' => 'Storniert',
                                            'partial' => 'Teilweise bezahlt',
                                        ])
                                        ->required()
                                        ->default('draft')
                                        ->disabled(fn($record) => $record && !$record->isEditable() && $record->status !== 'sent'),
                                ]),

                                Grid::make(2)->schema([
                                    Select::make('buchung_id')
                                        ->label('Buchung')
                                        ->relationship('buchung', 'id')
                                        ->getOptionLabelFromRecordUsing(fn($record) => self::formatBuchungLabel($record))
                                        ->searchable()
                                        ->preload()
                                        ->nullable()
                                        ->helperText('Optional - leer lassen für manuelle Rechnung')
                                        ->disabled(fn($record) => $record && !$record->isEditable()),

                                    Select::make('aussteller_id')
                                        ->label('Aussteller')
                                        ->relationship('aussteller', 'name')
                                        ->getOptionLabelFromRecordUsing(fn($record) => self::formatAusstellerLabel($record))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->disabled(fn($record) => $record && !$record->isEditable())
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $aussteller = \App\Models\Aussteller::find($state);
                                                if ($aussteller) {
                                                    $set('empf_firma', $aussteller->firma);
                                                    $set('empf_anrede', $aussteller->anrede);
                                                    $set('empf_vorname', $aussteller->vorname);
                                                    $set('empf_name', $aussteller->name);
                                                    $set('empf_strasse', $aussteller->strasse);
                                                    $set('empf_hausnummer', $aussteller->hausnummer);
                                                    $set('empf_plz', $aussteller->plz);
                                                    $set('empf_ort', $aussteller->ort);
                                                    $set('empf_land', $aussteller->land ?? 'Deutschland');
                                                    $set('empf_email', $aussteller->email);
                                                }
                                            }
                                        }),
                                ]),

                                Grid::make(3)->schema([
                                    DatePicker::make('rechnungsdatum')
                                        ->label('Rechnungsdatum')
                                        ->required()
                                        ->default(now())
                                        ->disabled(fn($record) => $record && !$record->isEditable()),

                                    DatePicker::make('lieferdatum')
                                        ->label('Lieferdatum')
                                        ->nullable()
                                        ->disabled(fn($record) => $record && !$record->isEditable()),

                                    DatePicker::make('faelligkeitsdatum')
                                        ->label('Fälligkeitsdatum')
                                        ->required()
                                        ->default(now()->addDays(14))
                                        ->disabled(fn($record) => $record && !$record->isEditable()),
                                ]),

                                TextInput::make('betreff')
                                    ->label('Betreff')
                                    ->required()
                                    ->columnSpan('full')
                                    ->disabled(fn($record) => $record && !$record->isEditable()),

                                Grid::make(2)->schema([
                                    Textarea::make('anschreiben')
                                        ->label('Anschreiben')
                                        ->rows(4)
                                        ->disabled(fn($record) => $record && !$record->isEditable()),

                                    Textarea::make('schlussschreiben')
                                        ->label('Schlussschreiben')
                                        ->rows(4)
                                        ->disabled(fn($record) => $record && !$record->isEditable()),
                                ]),

                                TextInput::make('zahlungsziel')
                                    ->label('Zahlungsziel')
                                    ->placeholder('z.B. 14 Tage netto')
                                    ->disabled(fn($record) => $record && !$record->isEditable()),
                            ]),

                        Tabs\Tab::make('Empfänger')
                            ->schema([
                                Section::make('Rechnungsempfänger')
                                    ->description('Diese Daten werden zum Rechnungszeitpunkt eingefroren')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('empf_firma')
                                                ->label('Firma')
                                                ->disabled(fn($record) => $record && !$record->isEditable()),

                                            Select::make('empf_anrede')
                                                ->label('Anrede')
                                                ->options([
                                                    'Herr' => 'Herr',
                                                    'Frau' => 'Frau',
                                                    'Divers' => 'Divers',
                                                ])
                                                ->disabled(fn($record) => $record && !$record->isEditable()),
                                        ]),

                                        Grid::make(2)->schema([
                                            TextInput::make('empf_vorname')
                                                ->label('Vorname')
                                                ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                                ->disabled(fn($record) => $record && !$record->isEditable()),

                                            TextInput::make('empf_name')
                                                ->label('Nachname')
                                                ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                                ->disabled(fn($record) => $record && !$record->isEditable()),
                                        ]),

                                        Grid::make(3)->schema([
                                            TextInput::make('empf_strasse')
                                                ->label('Straße')
                                                ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                                ->disabled(fn($record) => $record && !$record->isEditable()),

                                            TextInput::make('empf_hausnummer')
                                                ->label('Hausnummer')
                                                ->disabled(fn($record) => $record && !$record->isEditable()),

                                            TextInput::make('empf_plz')
                                                ->label('PLZ')
                                                ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                                ->disabled(fn($record) => $record && !$record->isEditable()),
                                        ]),

                                        Grid::make(2)->schema([
                                            TextInput::make('empf_ort')
                                                ->label('Ort')
                                                ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                                ->disabled(fn($record) => $record && !$record->isEditable()),

                                            TextInput::make('empf_land')
                                                ->label('Land')
                                                ->default('Deutschland')
                                                ->disabled(fn($record) => $record && !$record->isEditable()),
                                        ]),

                                        TextInput::make('empf_email')
                                            ->label('E-Mail')
                                            ->nullable()
                                            ->required(fn($record) => $record !== null) // Nur bei bestehenden Rechnungen required
                                            ->disabled(fn($record) => $record && !$record->isEditable())
                                            ->validationAttribute('E-Mail-Adresse'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Positionen')
                            ->schema([
                                Repeater::make('positionen')
                                    ->relationship()
                                    ->disabled(fn($record) => $record && !$record->isEditable())
                                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                                        // Position wird automatisch über den Index gesetzt
                                        $existingPositions = collect($get('../../positionen') ?? []);
                                        $data['position'] = $existingPositions->count() + 1;
                                        return $data;
                                    })
                                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $get): array {
                                        // Position wird automatisch über den Index gesetzt
                                        $allPositions = collect($get('../../positionen') ?? []);
                                        $currentIndex = $allPositions->search(function ($item) use ($data) {
                                            return isset($item['id']) && isset($data['id']) && $item['id'] === $data['id'];
                                        });

                                        if ($currentIndex === false) {
                                            // Neues Element, Position am Ende setzen
                                            $data['position'] = $allPositions->count() + 1;
                                        } else {
                                            // Bestehendes Element, Position basierend auf Index setzen
                                            $data['position'] = $currentIndex + 1;
                                        }

                                        return $data;
                                    })
                                    ->schema([
                                        Hidden::make('position'),

                                        Grid::make(5)->schema([
                                            TextInput::make('bezeichnung')
                                                ->label('Bezeichnung')
                                                ->required()
                                                ->columnSpan(2),

                                            TextInput::make('menge')
                                                ->label('Menge')
                                                ->numeric()
                                                ->default(1)
                                                ->required(),

                                            TextInput::make('einzelpreis')
                                                ->label('Einzelpreis €')
                                                ->numeric()
                                                ->step(0.01)
                                                ->required()
                                                ->formatStateUsing(fn($state) => $state ? $state / 100 : 0)
                                                ->dehydrateStateUsing(fn($state) => $state ? round($state * 100) : 0),

                                            TextInput::make('steuersatz')
                                                ->label('MwSt %')
                                                ->numeric()
                                                ->default(19.00)
                                                ->step(0.01),
                                        ]),

                                        Textarea::make('beschreibung')
                                            ->label('Beschreibung')
                                            ->columnSpan('full')
                                            ->rows(2),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel('Position hinzufügen')
                                    ->reorderable('position')
                                    ->collapsed()
                                    ->itemLabel(fn(array $state): ?string => $state['bezeichnung'] ?? null),
                            ]),

                        Tabs\Tab::make('Beträge & Zahlung')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('gesamtrabatt_prozent')
                                        ->label('Gesamtrabatt %')
                                        ->numeric()
                                        ->step(0.01)
                                        ->default(0)
                                        ->disabled(fn($record) => $record && !$record->isEditable()),

                                    TextInput::make('gesamtrabatt_betrag')
                                        ->label('Rabattbetrag €')
                                        ->numeric()
                                        ->step(0.01)
                                        ->default(0)
                                        ->disabled()
                                        ->formatStateUsing(fn($state) => $state ? $state / 100 : 0),
                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('nettobetrag')
                                        ->label('Nettobetrag €')
                                        ->numeric()
                                        ->step(0.01)
                                        ->disabled()
                                        ->formatStateUsing(fn($state) => $state ? $state / 100 : 0),

                                    TextInput::make('steuerbetrag')
                                        ->label('Steuerbetrag €')
                                        ->numeric()
                                        ->step(0.01)
                                        ->disabled()
                                        ->formatStateUsing(fn($state) => $state ? $state / 100 : 0),

                                    TextInput::make('bruttobetrag')
                                        ->label('Bruttobetrag €')
                                        ->numeric()
                                        ->step(0.01)
                                        ->disabled()
                                        ->formatStateUsing(fn($state) => $state ? $state / 100 : 0),
                                ]),

                                Grid::make(3)->schema([
                                    Forms\Components\DateTimePicker::make('versendet_am')
                                        ->label('Versendet am')
                                        ->nullable(),

                                    Forms\Components\DateTimePicker::make('bezahlt_am')
                                        ->label('Bezahlt am')
                                        ->nullable(),

                                    TextInput::make('bezahlter_betrag')
                                        ->label('Bezahlter Betrag €')
                                        ->numeric()
                                        ->step(0.01)
                                        ->default(0)
                                        ->formatStateUsing(fn($state) => $state ? $state / 100 : 0)
                                        ->dehydrateStateUsing(fn($state) => $state ? round($state * 100) : 0),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rechnungsnummer')
                    ->label('Nr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'paid' => 'Bezahlt',
                        'overdue' => 'Überfällig',
                        'canceled' => 'Storniert',
                        'partial' => 'Teilweise bezahlt',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'canceled' => 'secondary',
                        'partial' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil',
                        'sent' => 'heroicon-o-paper-airplane',
                        'paid' => 'heroicon-o-check-circle',
                        'overdue' => 'heroicon-o-exclamation-circle',
                        'canceled' => 'heroicon-o-x-circle',
                        'partial' => 'heroicon-o-clock',
                        default => 'heroicon-o-document',
                    }),

                TextColumn::make('buchung.id')
                    ->label('Buchung')
                    ->formatStateUsing(fn($record) => $record->buchung_id ? "#{$record->buchung_id}" : 'Manuell')
                    ->badge()
                    ->color(fn($record) => $record->buchung_id ? 'success' : 'gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('aussteller.firma')
                    ->label('Aussteller')
                    ->formatStateUsing(fn($record) => self::formatAusstellerName($record->aussteller))
                    ->searchable(['aussteller.firma', 'aussteller.name', 'aussteller.vorname'])
                    ->limit(30),

                TextColumn::make('betreff')
                    ->label('Betreff')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('rechnungsdatum')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('faelligkeitsdatum')
                    ->label('Fällig')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : null),

                TextColumn::make('bruttobetrag')
                    ->label('Betrag €')
                    ->formatStateUsing(fn($state) => number_format($state / 100, 2, ',', '.') . ' €')
                    ->sortable(),

                TextColumn::make('bezahlter_betrag')
                    ->label('Bezahlt €')
                    ->formatStateUsing(fn($state) => number_format($state / 100, 2, ',', '.') . ' €')
                    ->color(fn($record) => $record->bezahlter_betrag > 0 ? 'success' : null),

                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'paid' => 'Bezahlt',
                        'overdue' => 'Überfällig',
                        'canceled' => 'Storniert',
                        'partial' => 'Teilweise bezahlt',
                    ]),

                Tables\Filters\SelectFilter::make('buchung_typ')
                    ->label('Typ')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'buchung' => $query->whereNotNull('buchung_id'),
                            'manuell' => $query->whereNull('buchung_id'),
                            default => $query,
                        };
                    })
                    ->options([
                        'buchung' => 'Aus Buchung',
                        'manuell' => 'Manuell',
                    ]),

                Tables\Filters\Filter::make('buchung_id')
                    ->label('Buchung-ID')
                    ->form([
                        Forms\Components\TextInput::make('buchung_id')
                            ->label('Buchung-ID')
                            ->numeric()
                            ->placeholder('z.B. 88'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['buchung_id'],
                            fn(Builder $query, $buchungId): Builder => $query->where('buchung_id', $buchungId)
                        );
                    }),

                Tables\Filters\Filter::make('ueberfaellig')
                    ->label('Überfällig')
                    ->query(fn(Builder $query): Builder => $query->where('faelligkeitsdatum', '<', now())
                        ->where('status', 'sent')
                        ->whereColumn('bezahlter_betrag', '<', 'bruttobetrag')),

                Tables\Filters\Filter::make('rechnungsdatum')
                    ->form([
                        Forms\Components\DatePicker::make('von')
                            ->label('Von'),
                        Forms\Components\DatePicker::make('bis')
                            ->label('Bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['von'],
                                fn(Builder $query, $date): Builder => $query->whereDate('rechnungsdatum', '>=', $date),
                            )
                            ->when(
                                $data['bis'],
                                fn(Builder $query, $date): Builder => $query->whereDate('rechnungsdatum', '<=', $date),
                            );
                    })
                    ->label('Rechnungsdatum'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('Rechnung bearbeiten')
                    ->visible(fn($record) => $record->isEditable()),

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->tooltip('Rechnung anzeigen')
                    ->visible(fn($record) => !$record->isEditable()),

                Action::make('pdf')
                    ->label('')
                    ->icon('heroicon-o-document-arrow-down')
                    ->tooltip('PDF herunterladen')
                    ->action(function ($record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', ['rechnung' => $record]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'rechnung-' . $record->rechnungsnummer . '.pdf');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Prüfe ob alle Rechnungen editierbar sind
                            foreach ($records as $record) {
                                if (!$record->isEditable()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Einige Rechnungen können nicht gelöscht werden')
                                        ->body('Nur Rechnungen im Status "Entwurf" können gelöscht werden.')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                            }
                            // Lösche nur editierbare Rechnungen
                            $records->each(fn($record) => $record->delete());

                            \Filament\Notifications\Notification::make()
                                ->title('Rechnungen wurden gelöscht')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->paginated([15, 30, 50, 100])
            ->defaultPaginationPageOption(15);
    }

    // Helper-Methoden
    protected static function formatBuchungLabel($buchung): string
    {
        if (!$buchung) return '';

        $markt = $buchung->termin?->markt?->name ?? 'Unbekannt';
        $aussteller = self::formatAusstellerName($buchung->aussteller);

        return "#{$buchung->id} - {$markt} | {$aussteller}";
    }

    protected static function formatAusstellerLabel($aussteller): string
    {
        if (!$aussteller) return '';
        return self::formatAusstellerName($aussteller);
    }

    protected static function formatAusstellerName($aussteller): string
    {
        if (!$aussteller) return '';

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
            'index' => Pages\ListRechnungs::route('/'),
            'create' => Pages\CreateRechnung::route('/create'),
            'edit' => Pages\EditRechnung::route('/{record}/edit'),
        ];
    }
}
