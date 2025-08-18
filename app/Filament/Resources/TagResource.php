<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static ?string $label = 'Tag';
    protected static ?string $pluralLabel = 'Tags';
    protected static ?string $navigationLabel = 'Tags';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 60;
    protected static ?string $navigationGroup = 'Einstellungen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan('full'),

                // Forms\Components\Select::make('type')
                //     ->label('Typ')
                //     ->options([
                //         'positiv' => 'Positiv',
                //         'negativ' => 'Negativ',
                //         'neutral' => 'Neutral',
                //     ])
                //     ->required()
                //     ->default('neutral')
                //     ->reactive()
                //     ->afterStateUpdated(function (callable $set, $state) {
                //         // Automatisch Farbe basierend auf Typ setzen
                //         if ($state === 'positiv') {
                //             $set('color', 'success');
                //         } elseif ($state === 'negativ') {
                //             $set('color', 'danger');
                //         } else {
                //             $set('color', 'gray');
                //         }
                //     }),

                // Forms\Components\Select::make('color')
                //     ->label('Farbe')
                //     ->options([
                //         'gray' => 'Grau',
                //         'success' => 'Grün',
                //         'danger' => 'Rot',
                //         'warning' => 'Gelb',
                //         'info' => 'Blau',
                //         'primary' => 'Primär',
                //         'secondary' => 'Sekundär',
                //     ])
                //     ->required()
                //     ->default('gray')
                //     ->preload(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($record) => self::formatTagWithIcon($record)),

                // Tables\Columns\BadgeColumn::make('type')
                //     ->label('Typ')
                //     ->formatStateUsing(fn(string $state): string => match ($state) {
                //         'positiv' => 'Positiv',
                //         'negativ' => 'Negativ',
                //         'neutral' => 'Neutral',
                //         default => $state,
                //     })
                //     ->color(fn(string $state): string => match ($state) {
                //         'positiv' => 'success',
                //         'negativ' => 'danger',
                //         'neutral' => 'gray',
                //         default => 'gray',
                //     })
                //     ->icon(fn(string $state): string => match ($state) {
                //         'positiv' => 'heroicon-o-check-circle',
                //         'negativ' => 'heroicon-o-x-circle',
                //         'neutral' => 'heroicon-o-minus-circle',
                //         default => 'heroicon-o-tag',
                //     }),

                // Tables\Columns\BadgeColumn::make('color')
                //     ->label('Farbe')
                //     ->formatStateUsing(fn(string $state): string => match ($state) {
                //         'gray' => 'Grau',
                //         'success' => 'Grün',
                //         'danger' => 'Rot',
                //         'warning' => 'Gelb',
                //         'info' => 'Blau',
                //         'primary' => 'Primär',
                //         'secondary' => 'Sekundär',
                //         default => $state,
                //     })
                //     ->color(fn(string $state): string => $state),

                // Tables\Columns\TextColumn::make('aussteller_count')
                //     ->label('Aussteller')
                //     ->counts('aussteller')
                //     ->formatStateUsing(fn($state) => $state . ' Aussteller')
                //     ->sortable(),


            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('type')
            //         ->label('Typ')
            //         ->options([
            //             'positiv' => 'Positiv',
            //             'negativ' => 'Negativ',
            //             'neutral' => 'Neutral',
            //         ])
            //         ->multiple(),

            //     Tables\Filters\Filter::make('has_aussteller')
            //         ->label('Mit Ausstellern')
            //         ->query(fn(Builder $query): Builder => $query->has('aussteller')),

            //     Tables\Filters\Filter::make('no_aussteller')
            //         ->label('Ohne Aussteller')
            //         ->query(fn(Builder $query): Builder => $query->doesntHave('aussteller')),
            // ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tag $record) {
                        // Tags von allen Ausstellern entfernen vor dem Löschen
                        $record->aussteller()->detach();
                    }),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make()
            //             ->before(function ($records) {
            //                 // Tags von allen Ausstellern entfernen vor dem Löschen
            //                 foreach ($records as $record) {
            //                     $record->aussteller()->detach();
            //                 }
            //             }),
            //     ]),
            // ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    /**
     * Formatiert einen Tag mit Icon basierend auf dem Typ
     */
    public static function formatTagWithIcon(Tag $tag): string
    {
        // $icon = match ($tag->type) {
        //     'positiv' => '✅',
        //     'negativ' => '❌',
        //     default => '➖'
        // };

        // return $icon . ' ' . $tag->name;
        return $tag->name;
    }
}
