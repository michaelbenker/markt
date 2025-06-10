<?php

namespace App\Notifications;

use App\Models\Anfrage;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class NeueAnfrageNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Anfrage $anfrage)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Neue Standanfrage')
            ->line('Eine neue Standanfrage wurde eingereicht.')
            ->line('Name: ' . $this->anfrage->vorname . ' ' . $this->anfrage->nachname)
            ->line('Markt: ' . $this->anfrage->markt->name)
            ->action('Anfrage ansehen', route('filament.admin.resources.anfrage.view', $this->anfrage))
            ->line('Bitte prüfen Sie die Anfrage und nehmen Sie Kontakt mit dem Anfragesteller auf.');
    }

    public function toDatabase($notifiable): array
    {
        return Notification::make()
            ->title('Neue Standanfrage')
            ->icon('heroicon-o-document-text')
            ->body('Eine neue Standanfrage von ' . $this->anfrage->vorname . ' ' . $this->anfrage->nachname . ' für ' . $this->anfrage->markt->name)
            ->actions([
                \Filament\Notifications\Actions\Action::make('ansehen')
                    ->button()
                    ->url(route('filament.admin.resources.anfrage.view', $this->anfrage))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
