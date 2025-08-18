<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'E-Mail-Templates';

    protected static ?string $navigationGroup = 'Einstellungen';
    
    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template-Informationen')
                    ->schema([
                        Forms\Components\Hidden::make('key'),

                        Forms\Components\TextInput::make('name')
                            ->label('Template-Name')
                            ->required()
                            ->helperText('Anzeigename für das Template'),

                        Forms\Components\Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(2)
                            ->helperText('Kurze Beschreibung des Templates'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('E-Mail-Inhalt')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('E-Mail-Betreff')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Verwende Platzhalter wie {{aussteller_name}} oder {{markt_name}}'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\MarkdownEditor::make('content')
                                    ->label('E-Mail-Inhalt')
                                    ->required()
                                    ->live()
                                    ->helperText('Verwende Markdown-Syntax und Platzhalter')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'link',
                                        'heading',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'undo',
                                        'redo',
                                    ])
                                    ->extraAttributes(['style' => 'min-height:700px; max-height:700px; overflow-y:auto;']),

                                Forms\Components\Placeholder::make('content_preview')
                                    ->label('Vorschau')
                                    ->content(function (Forms\Get $get) {
                                        $content = $get('content') ?? '';
                                        $templateKey = $get('key') ?? '';

                                        if (empty($content)) {
                                            return 'Markdown-Inhalt eingeben für Vorschau';
                                        }

                                        // Dummy-Daten für Vorschau generieren
                                        $mailService = new \App\Services\MailService();
                                        $reflection = new \ReflectionClass($mailService);
                                        $getDummyDataMethod = $reflection->getMethod('getDummyData');
                                        $getDummyDataMethod->setAccessible(true);
                                        $dummyData = $getDummyDataMethod->invoke($mailService, $templateKey ?: 'aussteller_bestaetigung');

                                        $prepareTemplateDataMethod = $reflection->getMethod('prepareTemplateData');
                                        $prepareTemplateDataMethod->setAccessible(true);
                                        $processedData = $prepareTemplateDataMethod->invoke($mailService, $templateKey ?: 'aussteller_bestaetigung', $dummyData);

                                        // Platzhalter mit Dummy-Daten ersetzen
                                        $previewContent = $content;
                                        foreach ($processedData as $key => $value) {
                                            $previewContent = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $value, $previewContent);
                                        }

                                        $markdownRenderer = new \League\CommonMark\CommonMarkConverter();
                                        $rendered = $markdownRenderer->convert($previewContent);

                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="prose prose-sm max-w-none p-4 bg-gray-50 rounded-lg border" style="height:700px;overflow-y:auto;">' .
                                                '<div class="mb-2 text-xs text-blue-600 font-medium">Vorschau mit Beispiel-Daten</div>' .
                                                $rendered .
                                                '</div>'
                                        );
                                    }),
                            ]),
                    ]),

                Forms\Components\Section::make('Verfügbare Platzhalter')
                    ->schema([
                        Forms\Components\Placeholder::make('variables_help')
                            ->label(false)
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-blue-900 mb-3">Verfügbare Variablen:</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <h5 class="font-medium text-blue-800 mb-2">Allgemein:</h5>
                                            <ul class="space-y-1 text-blue-700">
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{aussteller_name}}</code> - Name des Ausstellers</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{markt_name}}</code> - Name des Marktes</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{firma}}</code> - Firmenname</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{app_name}}</code> - App-Name</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-blue-800 mb-2">Spezifisch:</h5>
                                            <ul class="space-y-1 text-blue-700">
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{rechnung_nummer}}</code> - Rechnungsnummer</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{betrag}}</code> - Rechnungsbetrag</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{termine}}</code> - Markttermine</li>
                                                <li><code class="bg-blue-100 px-2 py-1 rounded">{{standplatz}}</code> - Standplatz</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            ')),
                    ]),
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
