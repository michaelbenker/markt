<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AusstellerResource\Pages;
use App\Filament\Resources\AusstellerResource\RelationManagers;
use App\Models\Aussteller;
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

class AusstellerResource extends Resource
{
    protected static ?string $model = Aussteller::class;
    protected static ?string $label = 'Aussteller';
    protected static ?string $pluralLabel = 'Aussteller';
    protected static ?string $navigationLabel = 'Aussteller';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
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
                        ->default('Deutschland'),
                    TextInput::make('telefon')->label('Telefon')->tel(),
                    TextInput::make('mobil')->label('Mobil')->tel(),
                    TextInput::make('homepage')->label('Homepage')->url(),
                    TextInput::make('email')
                        ->label('E-Mail')
                        ->email()
                        ->required(),
                    TextInput::make('briefanrede')->label('Briefanrede'),
                    Textarea::make('bemerkung')->label('Bemerkung')->rows(4),
                    KeyValue::make('soziale_medien')
                        ->label('Soziale Medien')
                        ->keyLabel('Plattform')
                        ->valueLabel('URL')
                        ->addActionLabel('hinzufügen')
                        ->deleteActionLabel('entfernen'),
                    FileUpload::make('bilder')
                        ->label('Bilder')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->directory('aussteller/bilder'),
                    FileUpload::make('files')
                        ->label('Dateien')
                        ->multiple()
                        ->directory('aussteller/files'),


                    Select::make('kategorien')
                        ->relationship('kategorien', 'name')
                        ->label('Kategorien')
                        ->multiple(),

                    Select::make('subkategorien')
                        ->relationship('subkategorien', 'name')
                        ->label('Subkategorien')
                        ->multiple(),
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
            'index' => Pages\ListAusstellers::route('/'),
            'create' => Pages\CreateAussteller::route('/create'),
            'edit' => Pages\EditAussteller::route('/{record}/edit'),
        ];
    }
}
