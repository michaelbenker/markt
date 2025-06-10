<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TestNotifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Test Benachrichtigungen';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.test-notifications';

    public function getHeaderActions(): array
    {
        return [
            Action::make('test_notification_fluent')
                ->label('Test Benachrichtigung (Fluent)')
                ->action(function () {
                    try {
                        $notification = Notification::make()
                            ->title('Test Benachrichtigung')
                            ->success()
                            ->body('Dies ist eine Test-Benachrichtigung über die Fluent API.')
                            ->getDatabaseMessage();

                        DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid(),
                            'type' => \Filament\Notifications\Notification::class,
                            'notifiable_type' => User::class,
                            'notifiable_id' => Auth::id(),
                            'data' => json_encode($notification),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Erfolg')
                            ->success()
                            ->body('Benachrichtigung wurde gespeichert.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler')
                            ->danger()
                            ->body('Fehler beim Speichern der Benachrichtigung: ' . $e->getMessage())
                            ->send();
                    }
                }),
            Action::make('test_notification_fluent2')
                ->label('Test Benachrichtigung (Fluent 2)')
                ->action(function () {
                    try {
                        $notification = Notification::make()
                            ->title('Test Benachrichtigung')
                            ->success()
                            ->body('Dies ist eine weitere Test-Benachrichtigung über die Fluent API.')
                            ->getDatabaseMessage();

                        DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid(),
                            'type' => \Filament\Notifications\Notification::class,
                            'notifiable_type' => User::class,
                            'notifiable_id' => Auth::id(),
                            'data' => json_encode($notification),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Erfolg')
                            ->success()
                            ->body('Benachrichtigung wurde gespeichert.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler')
                            ->danger()
                            ->body('Fehler beim Speichern der Benachrichtigung: ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
