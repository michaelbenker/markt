<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailReportResource\Pages;
use App\Models\MailReport;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MailReportResource extends Resource
{
    protected static ?string $model = MailReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Report';

    protected static ?string $pluralLabel = 'E-Mail Report';

    protected static ?string $label = 'E-Mail Report';

    protected static ?string $navigationGroup = 'E-Mail';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Zeitpunkt')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        'opened' => 'info',
                        'clicked' => 'primary',
                        'bounced' => 'gray',
                        'complained' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Ausstehend',
                        'sent' => 'Versendet',
                        'failed' => 'Fehlgeschlagen',
                        'bounced' => 'Zurückgewiesen',
                        'complained' => 'Beschwerde',
                        'opened' => 'Geöffnet',
                        'clicked' => 'Geklickt',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('to_email')
                    ->label('Empfänger')
                    ->searchable()
                    ->copyable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('to_name')
                    ->label('Name')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Betreff')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (MailReport $record): string {
                        return $record->subject;
                    }),

                Tables\Columns\TextColumn::make('template_key')
                    ->label('Template')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('source_type')
                    ->label('Quelle')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Anfrage' => 'info',
                        'Buchung' => 'success',
                        'Rechnung' => 'warning',
                        'Aussteller' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (?string $state, MailReport $record): string {
                        if (!$state) return '-';
                        return $state . ($record->source_id ? ' #' . $record->source_id : '');
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('mail_driver')
                    ->label('Provider')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Versendet')
                    ->dateTime('d.m.Y H:i:s')
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('opened_at')
                    ->label('Geöffnet')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Fehler')
                    ->limit(30)
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('send_duration_ms')
                    ->label('Dauer')
                    ->formatStateUsing(fn($state) => $state ? $state . ' ms' : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('Größe')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $units = ['B', 'KB', 'MB'];
                        $factor = floor((strlen($state) - 1) / 3);
                        return sprintf("%.2f", $state / pow(1024, $factor)) . ' ' . $units[$factor];
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Ausstehend',
                        'sent' => 'Versendet',
                        'failed' => 'Fehlgeschlagen',
                        'bounced' => 'Zurückgewiesen',
                        'complained' => 'Beschwerde',
                        'opened' => 'Geöffnet',
                        'clicked' => 'Geklickt',
                    ]),

                Tables\Filters\SelectFilter::make('template_key')
                    ->label('Template')
                    ->options(function () {
                        return MailReport::query()
                            ->whereNotNull('template_key')
                            ->distinct()
                            ->pluck('template_key', 'template_key')
                            ->toArray();
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Quelle')
                    ->options([
                        'Anfrage' => 'Anfrage',
                        'Buchung' => 'Buchung',
                        'Rechnung' => 'Rechnung',
                        'Aussteller' => 'Aussteller',
                        'User' => 'User',
                    ]),

                Tables\Filters\Filter::make('failed')
                    ->label('Nur Fehlgeschlagene')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'failed')),

                Tables\Filters\Filter::make('today')
                    ->label('Heute')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),

                Tables\Filters\Filter::make('this_week')
                    ->label('Diese Woche')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                Tables\Actions\Action::make('retry')
                    ->label('Erneut senden')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn(MailReport $record): bool => $record->status === 'failed' && $record->canRetry())
                    ->action(function (MailReport $record) {
                        // TODO: Implementiere Retry-Logik
                        $record->incrementRetry();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn(): bool => auth()->user()?->is_admin ?? false),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListMailReports::route('/'),
            'view' => Pages\ViewMailReport::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $failedCount = MailReport::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return $failedCount > 0 ? (string) $failedCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user']);
    }
}
