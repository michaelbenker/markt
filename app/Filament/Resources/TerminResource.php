<?php


// namespace App\Filament\Resources;

// use App\Filament\Resources\TerminResource\Pages;
// use App\Models\Termin;
// use Filament\Forms;
// use Filament\Forms\Form;
// use Filament\Resources\Resource;
// use Filament\Tables;
// use Filament\Tables\Table;
// use Filament\Tables\Filters\SelectFilter;

// class TerminResource extends Resource
// {
//     protected static ?string $model = Termin::class;
//     protected static ?string $label = 'Termin';
//     protected static ?string $pluralLabel = 'Termine';
//     protected static ?string $navigationLabel = 'Termine';
//     protected static ?string $navigationIcon = 'heroicon-o-calendar';

//     protected static ?string $navigationGroup = 'Einstellungen';
//     protected static ?int $navigationSort = 2;

//     protected static bool $shouldRegisterNavigation = false;

//     public static function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\Select::make('markt_id')
//                     ->relationship('markt', 'name')
//                     ->required(),
//                 Forms\Components\DatePicker::make('start')
//                     ->required(),
//                 Forms\Components\DatePicker::make('ende')
//                     ->required(),
//                 Forms\Components\Textarea::make('bemerkung')
//                     ->nullable(),
//             ]);
//     }

//     public static function table(Table $table): Table
//     {
//         return $table
//             ->columns([
//                 Tables\Columns\TextColumn::make('markt.name')
//                     ->label('Markt'),
//                 Tables\Columns\TextColumn::make('start')
//                     ->date(),
//                 Tables\Columns\TextColumn::make('ende')
//                     ->date(),
//                 Tables\Columns\TextColumn::make('bemerkung')
//                     ->limit(50),
//             ])
//             ->filters([
//                 //
//             ])
//             ->actions([
//                 Tables\Actions\EditAction::make(),
//                 Tables\Actions\DeleteAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     Tables\Actions\DeleteBulkAction::make(),
//                 ]),
//             ]);
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => Pages\ListTermin::route('/'),
//             'create' => Pages\CreateTermin::route('/create'),
//             'edit' => Pages\EditTermin::route('/{record}/edit'),
//         ];
//     }
// }
