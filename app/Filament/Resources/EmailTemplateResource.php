<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'E-Mail-Templates';

    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template-Informationen')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Template-Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Eindeutiger Schlüssel (z.B. aussteller_absage, rechnung_versand)'),

                        Forms\Components\TextInput::make('name')
                            ->label('Template-Name')
                            ->required()
                            ->helperText('Anzeigename für das Template'),

                        Forms\Components\Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(2)
                            ->helperText('Kurze Beschreibung des Templates'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('E-Mail-Inhalt')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('E-Mail-Betreff')
                            ->required()
                            ->helperText('Verwende Platzhalter wie {{aussteller_name}} oder {{markt_name}}'),

                        TiptapEditor::make('content')
                            ->label('E-Mail-Inhalt')
                            ->required()
                            ->columnSpanFull()
                            ->profile('simple')
                            ->tools([
                                'heading',
                                '|',
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                '|',
                                'bullet-list',
                                'ordered-list',
                                '|',
                                'align-left',
                                'align-center',
                                'align-right',
                                '|',
                                'link',
                                'blockquote',
                                'hr',
                                '|',
                                'table',
                                '|',
                                'undo',
                                'redo',
                                'source',
                            ])
                            ->extraInputAttributes([
                                'style' => 'min-height: 300px;'
                            ])
                            ->helperText('HTML-Inhalt der E-Mail. Verwende Platzhalter wie {{aussteller_name}}, {{markt_name}}, etc. Keine Bilder erlaubt.'),
                    ]),

                Forms\Components\Section::make('Verfügbare Variablen')
                    ->schema([
                        Forms\Components\Repeater::make('available_variables')
                            ->label('Platzhalter')
                            ->schema([
                                Forms\Components\TextInput::make('variable')
                                    ->label('Variable')
                                    ->required()
                                    ->helperText('z.B. aussteller_name'),
                                Forms\Components\TextInput::make('description')
                                    ->label('Beschreibung')
                                    ->required()
                                    ->helperText('z.B. Name des Ausstellers'),
                            ])
                            ->columns(2)
                            ->helperText('Liste der verfügbaren Platzhalter für dieses Template'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Template-Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Betreff')
                    ->limit(50)
                    ->tooltip(function (EmailTemplate $record): ?string {
                        return $record->subject;
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Zuletzt geändert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Nur aktive')
                    ->falseLabel('Nur inaktive')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('Vorschau')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn(EmailTemplate $record) => view('filament.email-template-preview', ['template' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Schließen'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
