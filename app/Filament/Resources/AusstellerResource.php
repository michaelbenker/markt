<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AusstellerResource\Pages;
use App\Filament\Resources\AusstellerResource\RelationManagers;
use App\Models\Aussteller;
use App\Models\Kategorie;
use App\Services\CountryService;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Textarea, Select, KeyValue, Grid, Section, Toggle};
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Exports\AusstellerExport;
use Mokhosh\FilamentRating\Components\Rating;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Filament\Forms\Components\CheckboxList;

class AusstellerResource extends Resource
{
    protected static ?string $model = Aussteller::class;
    protected static ?string $label = 'Aussteller';
    protected static ?string $pluralLabel = 'Aussteller';
    protected static ?string $navigationLabel = 'Aussteller';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'aussteller';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Aussteller')
                    ->columnSpan('full')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Allgemein')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('firma')
                                        ->label('Firma')
                                        ->columnSpan(2)
                                        ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                    Grid::make(2)->schema([
                                        Select::make('anrede')
                                            ->label('Anrede')
                                            ->options([
                                                'Herr' => 'Herr',
                                                'Frau' => 'Frau',
                                                'Divers' => 'Divers',
                                            ])
                                            ->nullable(),
                                        TextInput::make('briefanrede')
                                            ->label('Briefanrede')
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                    ])->columnSpan(2),
                                    TextInput::make('vorname')
                                        ->label('Vorname')
                                        ->required()
                                        ->dehydrateStateUsing(fn(string $state): string => trim($state)),
                                    TextInput::make('name')
                                        ->label('Name')
                                        ->required()
                                        ->dehydrateStateUsing(fn(string $state): string => trim($state)),
                                    TextInput::make('strasse')
                                        ->label('Straße')
                                        ->required()
                                        ->dehydrateStateUsing(fn(string $state): string => trim($state)),
                                    TextInput::make('hausnummer')
                                        ->label('Hausnummer')
                                        ->nullable()
                                        ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                    TextInput::make('plz')
                                        ->label('PLZ')
                                        ->required()
                                        ->dehydrateStateUsing(fn(string $state): string => trim($state)),
                                    TextInput::make('ort')
                                        ->label('Ort')
                                        ->required()
                                        ->dehydrateStateUsing(fn(string $state): string => trim($state)),
                                    Select::make('land')
                                        ->label('Land')
                                        ->options(function () {
                                            $countries = CountryService::getCountriesForSelect();
                                            unset($countries['---']); // Trennlinie entfernen für Filament Select
                                            return $countries;
                                        })
                                        ->searchable()
                                        ->default('Deutschland')
                                        ->columnSpan(2),
                                    Grid::make(2)->schema([
                                        TextInput::make('telefon')
                                            ->label('Telefon')
                                            ->tel()
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                        TextInput::make('mobil')
                                            ->label('Mobil')
                                            ->tel()
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                    ])->columnSpan(2),
                                    Grid::make(2)->schema([
                                        TextInput::make('email')
                                            ->label('E-Mail')
                                            ->required()
                                            ->email()
                                            ->unique(
                                                table: Aussteller::class,
                                                ignoreRecord: true
                                            )
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? mb_strtolower(trim($state)) : null),
                                    ])->columnSpan(2),
                                    Grid::make(2)->schema([
                                        TextInput::make('steuer_id')
                                            ->label('Steuer-ID')
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                        TextInput::make('handelsregisternummer')
                                            ->label('Handelsregisternummer')
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state ? trim($state) : null),
                                    ])->columnSpan(2),
                                ]),
                            ]),
                        Tab::make('Kategorie & Stand')
                            ->schema([
                                Select::make('filterKategorie')
                                    ->label('Kategorie wählen')
                                    ->options(function () {
                                        return Kategorie::pluck('name', 'id');
                                    })
                                    ->default(fn($record) => $record?->subkategorien()->first()?->kategorie_id)
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        if ($state === null && $record) {
                                            $set('filterKategorie', $record->subkategorien()->first()?->kategorie_id);
                                        }
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('subkategorien', [])),

                                Select::make('subkategorien')
                                    ->label('Subkategorien')
                                    ->multiple()
                                    ->relationship('subkategorien', 'name')
                                    ->preload()
                                    ->options(function (callable $get) {
                                        $kategorieId = $get('filterKategorie');
                                        return \App\Models\Subkategorie::query()
                                            ->when($kategorieId, fn($query) => $query->where('kategorie_id', $kategorieId))
                                            ->pluck('name', 'id');
                                    }),
                                Toggle::make('vorfuehrung_am_stand')
                                    ->label('Vorführung am Stand')
                                    ->helperText('Bietet der Aussteller Vorführungen am Stand an?'),
                                Textarea::make('bemerkung')->label('Bemerkung')->rows(4),
                                Section::make('Stand')
                                    ->schema([
                                        TextInput::make('stand.laenge')
                                            ->label('Länge (m)')
                                            ->numeric(),
                                        TextInput::make('stand.tiefe')
                                            ->label('Tiefe (m)')
                                            ->numeric(),
                                        TextInput::make('stand.flaeche')
                                            ->label('Fläche (m²)')
                                            ->numeric(),
                                        Textarea::make('stand.aufbau')
                                            ->label('Standaufbau')
                                            ->placeholder('Unser Aufbau erfolgt durch Zelt/Pavillon, Verkaufshütte, Verkaufsanhänger, Marktschirm...')
                                            ->rows(3)
                                            ->columnSpan('full')
                                            ->helperText('Angabe nur für Standplatz im Außenbereich erforderlich.'),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Soziale Medien')
                            ->schema([
                                Repeater::make('soziale_medien')
                                    ->label(false)
                                    ->schema([
                                        Select::make('plattform')
                                            ->label('Plattform')
                                            ->options([
                                                'facebook' => 'Facebook',
                                                'instagram' => 'Instagram',
                                                'x' => 'X (Twitter)',
                                                'linkedin' => 'LinkedIn',
                                                'youtube' => 'YouTube',
                                                'tiktok' => 'TikTok',
                                                'pinterest' => 'Pinterest',
                                                'xing' => 'Xing',
                                                'other' => 'Website/Andere',
                                            ]),
                                        TextInput::make('url')
                                            ->label('URL')
                                            ->url(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('hinzufügen')
                                    ->deletable()
                                    ->reorderable(false),
                            ]),
                        Tab::make('Medien')
                            ->schema([
                                Forms\Components\ViewField::make('medien_manager')
                                    ->label('Medien verwalten')
                                    ->view('filament.components.medien-manager')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Bewertung')
                            ->schema([
                                Section::make('Bewertung')
                                    ->schema([
                                        Rating::make('rating')
                                            ->label('Bewertung')
                                            ->stars(5)
                                            ->size('lg')
                                            ->allowZero()
                                            ->default(0)
                                            ->helperText('Klicke auf die Sterne für eine Bewertung (1-5 Sterne). Aktuell: {{ $state ?? 0 }} Sterne'),
                                        Textarea::make('rating_bemerkung')
                                            ->label('Bemerkung zur Bewertung')
                                            ->rows(4)
                                            ->placeholder('Notizen zur Bewertung des Ausstellers...')
                                            ->columnSpan('full'),
                                    ])
                                    ->columns(1),
                                // Tags nur beim Bearbeiten anzeigen, nicht beim Erstellen
                                Forms\Components\Group::make([
                                    Select::make('tags')
                                        ->label('Tags')
                                        ->multiple()
                                        ->relationship('tags', 'name')
                                        ->getOptionLabelFromRecordUsing(function (\App\Models\Tag $record) {
                                            $icon = match ($record->type) {
                                                'positiv' => '✅',
                                                'negativ' => '❌',
                                                default => '➖'
                                            };
                                            return $icon . ' ' . $record->name;
                                        })
                                        ->preload()
                                        ->searchable()
                                        ->helperText('Wähle passende Tags für diesen Aussteller'),
                                ])->visible(fn($livewire) => !($livewire instanceof Pages\CreateAussteller)),
                            ]),
                        Tab::make('Buchungen')
                            ->schema([
                                Forms\Components\View::make('filament.resources.aussteller.buchungen-liste')
                                    ->viewData(function ($record) {
                                        return ['aussteller' => $record];
                                    }),
                            ])
                            ->visible(fn($livewire) => !($livewire instanceof Pages\CreateAussteller)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['subkategorien.kategorie']))
            ->columns([
                TextColumn::make('id')
                    ->label('Kunden-Nr.')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kundennummer kopiert'),
                TextColumn::make('firma')->label('Firma')->searchable()->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(
                        fn($record) => "{$record->vorname} {$record->name}"
                    ),
                TextColumn::make('email')->label('E-Mail')->sortable(),
                TextColumn::make('telefon')->label('Telefon'),
                TextColumn::make('ort')->label('Ort'),
                TextColumn::make('land')->label('Land'),
                RatingColumn::make('rating')
                    ->label('Bewertung')
                    ->sortable(),
                TextColumn::make('hauptkategorie')
                    ->label('Hauptkategorie')
                    ->getStateUsing(function ($record) {
                        if (!$record->subkategorien || $record->subkategorien->isEmpty()) {
                            return '';
                        }

                        $kategorien = $record->subkategorien
                            ->map(function ($subkategorie) {
                                return $subkategorie->kategorie ? $subkategorie->kategorie->name : null;
                            })
                            ->filter()
                            ->unique();

                        return $kategorien->count() > 0 ? $kategorien->implode(', ') : '';
                    })
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('maerkte')
                    ->label('Märkte')
                    ->formatStateUsing(function ($record) {
                        $maerkte = [];
                        foreach ($record->buchungen as $buchung) {
                            if ($buchung->markt) {
                                $maerkte[] = $buchung->markt->name;
                            }
                        }
                        return !empty($maerkte) ? implode(', ', array_unique($maerkte)) : 'Keine Buchungen';
                    })
                    ->searchable(false)
                    ->sortable(false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('buchungsjahr')
                    ->label('Buchungsjahr')
                    ->options(function () {
                        // Hole alle Jahre aus den Buchungen
                        $jahre = \App\Models\Buchung::query()
                            ->selectRaw('YEAR(created_at) as jahr')
                            ->distinct()
                            ->orderBy('jahr', 'desc')
                            ->pluck('jahr');

                        $options = [];
                        foreach ($jahre as $jahr) {
                            $options[$jahr] = $jahr;
                        }

                        // Falls noch keine Buchungen existieren, aktuelles Jahr anbieten
                        if (empty($options)) {
                            $options[date('Y')] = date('Y');
                        }

                        return $options;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $jahr) => $query->whereHas(
                                'buchungen',
                                fn(Builder $query) => $query->whereYear('created_at', $jahr)
                            )
                        );
                    })
                    ->default(date('Y')),

                Tables\Filters\SelectFilter::make('markt')
                    ->label('Markt')
                    ->options(function () {
                        return \App\Models\Markt::pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['values'] ?? null,
                            fn(Builder $query, $marktIds) => $query->whereHas(
                                'buchungen',
                                fn(Builder $query) => $query->whereIn('markt_id', $marktIds)
                            )
                        );
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('hauptkategorie')
                    ->label('Hauptkategorie')
                    ->options(function () {
                        return Kategorie::pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['values'] ?? null,
                            fn(Builder $query, $kategorieIds) => $query->whereHas(
                                'subkategorien',
                                fn(Builder $query) => $query->whereIn('kategorie_id', $kategorieIds)
                            )
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\Action::make('testEmail')
                    ->label('E-Mail testen')
                    ->icon('heroicon-o-envelope')
                    ->action(function ($record) {
                        try {
                            \Illuminate\Support\Facades\Mail::raw('Dies ist eine Test-E-Mail von der Markt-App.', function ($message) use ($record) {
                                $message->to($record->email)
                                    ->subject('Test-E-Mail Markt-App');
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('E-Mail erfolgreich versendet')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Fehler beim E-Mail-Versand')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('exportExcel')
                        ->label('Excel Export')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $filename = 'aussteller_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

                            return Excel::download(new AusstellerExport($records), $filename);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Aussteller exportieren')
                        ->modalDescription('Möchten Sie die ausgewählten Aussteller als Excel-Datei (XLSX) exportieren?')
                        ->modalSubmitActionLabel('Exportieren'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Keine RelationManagers - Buchungen werden als Tab angezeigt
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAussteller::route('/'),
            'create' => Pages\CreateAussteller::route('/create'),
            'edit' => Pages\EditAussteller::route('/{record}/edit'),
        ];
    }
}
