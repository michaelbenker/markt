<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AusstellerResource\Pages;
use App\Filament\Resources\AusstellerResource\RelationManagers;
use App\Models\Aussteller;
use App\Models\Kategorie;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Textarea, Select, KeyValue, FileUpload, Grid};
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AusstellerResource extends Resource
{
    protected static ?string $model = Aussteller::class;
    protected static ?string $label = 'Aussteller';
    protected static ?string $pluralLabel = 'Aussteller';
    protected static ?string $navigationLabel = 'Aussteller';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'aussteller';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Aussteller')
                    ->columnSpan('full')
                    ->tabs([
                        Tab::make('Allgemein')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('firma')->label('Firma')->required(),
                                    Select::make('anrede')
                                        ->label('Anrede')
                                        ->options([
                                            'Herr' => 'Herr',
                                            'Frau' => 'Frau',
                                            'Divers' => 'Divers',
                                        ])
                                        ->nullable(),
                                    TextInput::make('vorname')->label('Vorname')->required(),
                                    TextInput::make('name')->label('Name')->required(),
                                    TextInput::make('strasse')->label('Straße')->required(),
                                    TextInput::make('hausnummer')->label('Hausnummer')->nullable(),
                                    TextInput::make('plz')->label('PLZ')->required(),
                                    TextInput::make('ort')->label('Ort')->required(),
                                    Select::make('land')
                                        ->label('Land')
                                        ->options([
                                            'Deutschland' => 'Deutschland',
                                            'Österreich' => 'Österreich',
                                            'Schweiz' => 'Schweiz',
                                            'Italien' => 'Italien',
                                            'Frankreich' => 'Frankreich',
                                            'Niederlande' => 'Niederlande',
                                        ])
                                        ->searchable()
                                        ->default('Deutschland')
                                        ->columnSpan(2),
                                    Grid::make(2)->schema([
                                        TextInput::make('telefon')->label('Telefon')->tel(),
                                        TextInput::make('mobil')->label('Mobil')->tel(),
                                    ])->columnSpan(2),
                                    Grid::make(2)->schema([
                                        TextInput::make('homepage')->label('Homepage')->url(),
                                        TextInput::make('email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->required(),
                                    ])->columnSpan(2),
                                    TextInput::make('briefanrede')->label('Briefanrede'),
                                ]),
                                Textarea::make('bemerkung')->label('Bemerkung')->rows(4)->columnSpan(2),
                            ]),
                        Tab::make('Kategorie')
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
                                    ->preload()
                                    ->options(function (callable $get) {
                                        $kategorieId = $get('filterKategorie');
                                        return \App\Models\Subkategorie::query()
                                            ->when($kategorieId, fn($query) => $query->where('kategorie_id', $kategorieId))
                                            ->pluck('name', 'id');
                                    })
                                    ->saveRelationshipsUsing(function ($record, $state) {
                                        $record->subkategorien()->sync($state);
                                    })
                                    ->afterStateHydrated(function (callable $set, $state, $record) {
                                        $set('subkategorien', $record?->subkategorien()->pluck('id')->toArray());
                                    }),
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
                                                'x' => 'X',
                                                'linkedin' => 'LinkedIn',
                                                'youtube' => 'YouTube',
                                                'tiktok' => 'TikTok',
                                                'pinterest' => 'Pinterest',
                                                'xing' => 'Xing',
                                                'other' => 'Andere',
                                            ])
                                            ->required(),
                                        TextInput::make('url')
                                            ->label('URL')
                                            ->url()
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('hinzufügen')
                                    ->deletable()
                                    ->reorderable(false),
                            ]),
                        Tab::make('Medien')
                            ->schema([
                                FileUpload::make('bilder')
                                    ->label('Bilder')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->directory('aussteller/bilder')
                                    ->preserveFilenames()
                                    ->visibility('public')
                                    ->disk('public')
                                    ->columnSpanFull()
                                    ->deleteUploadedFileUsing(function ($file) {
                                        Storage::disk('public')->delete($file);
                                    }),
                                FileUpload::make('files')
                                    ->label('Dateien')
                                    ->multiple()
                                    ->directory('aussteller/files')
                                    ->preserveFilenames()
                                    ->visibility('public')
                                    ->disk('public')
                                    ->columnSpanFull()
                                    ->deleteUploadedFileUsing(function ($file) {
                                        Storage::disk('public')->delete($file);
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\Action::make('testEmail')
                    ->label('E-Mail testen')
                    ->icon('heroicon-o-envelope')
                    ->action(function ($record) {
                        try {
                            \Mail::raw('Dies ist eine Test-E-Mail von der Markt-App.', function ($message) use ($record) {
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
            'index' => Pages\ListAussteller::route('/'),
            'create' => Pages\CreateAussteller::route('/create'),
            'edit' => Pages\EditAussteller::route('/{record}/edit'),
        ];
    }
}
