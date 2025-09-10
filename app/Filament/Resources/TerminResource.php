<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TerminResource\Pages;
use App\Filament\Resources\TerminResource\RelationManagers;
use App\Models\Termin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TerminResource extends Resource
{
    protected static ?string $model = Termin::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $label = 'Termin';
    protected static ?string $pluralLabel = 'Termine';
    protected static ?string $navigationLabel = 'Termine';
    protected static ?string $navigationGroup = 'Einstellungen';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'termin';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('markt_id')
                    ->label('Markt')
                    ->relationship('markt', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('bemerkung')
                    ->label('Bemerkung')
                    ->maxLength(500)
                    ->rows(3),
                Forms\Components\DatePicker::make('start')
                    ->label('Startdatum')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            // Setze Anmeldeschluss auf 30 Tage vor Start
                            $anmeldeschluss = \Carbon\Carbon::parse($state)->subDays(30);
                            $set('anmeldeschluss', $anmeldeschluss->format('Y-m-d'));
                        }
                    }),
                Forms\Components\DatePicker::make('ende')
                    ->label('Enddatum')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->after('start'),
                Forms\Components\DatePicker::make('anmeldeschluss')
                    ->label('Anmeldeschluss')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->before('start')
                    ->helperText('Letzter Tag für Anmeldungen'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('markt.name')
                    ->label('Markt')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start')
                    ->label('Start')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ende')
                    ->label('Ende')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('anmeldeschluss')
                    ->label('Anmeldeschluss')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bemerkung')
                    ->label('Bemerkung')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('markt')
                    ->relationship('markt', 'name')
                    ->label('Markt'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start', 'desc');
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
            'index' => Pages\ListTermins::route('/'),
            'create' => Pages\CreateTermin::route('/create'),
            'edit' => Pages\EditTermin::route('/{record}/edit'),
        ];
    }
}
