<?php

namespace App\Filament\Resources\MarktResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TermineRelationManager extends RelationManager
{
    protected static string $relationship = 'termine';

    protected static ?string $recordTitleAttribute = 'start';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('start')
                            ->required()
                            ->label('Start')
                            ->displayFormat('d.m.Y')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('ende', $state);
                                }
                            }),
                        Forms\Components\DatePicker::make('ende')
                            ->required()
                            ->label('Ende')
                            ->displayFormat('d.m.Y')
                            ->minDate(fn(Forms\Get $get) => $get('start')),
                    ]),
                Forms\Components\Textarea::make('bemerkung')
                    ->label('Bemerkung')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start')
                    ->date()
                    ->label('Start')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ende')
                    ->date()
                    ->label('Ende')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bemerkung')
                    ->label('Bemerkung')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
