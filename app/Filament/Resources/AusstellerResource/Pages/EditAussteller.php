<?php

namespace App\Filament\Resources\AusstellerResource\Pages;

use App\Filament\Resources\AusstellerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditAussteller extends EditRecord
{
    protected static string $resource = AusstellerResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        // Lösche die return URL aus der Session, wenn wir nicht von einer Buchung kommen
        if (!request()->has('return')) {
            session()->forget('aussteller_return_url');
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // E-Mail bereinigen: Leerzeichen entfernen und in Kleinbuchstaben konvertieren
        if (isset($data['email']) && $data['email']) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }

        // Telefonnummern bereinigen
        if (isset($data['telefon']) && $data['telefon']) {
            $data['telefon'] = trim($data['telefon']);
        }

        if (isset($data['mobil']) && $data['mobil']) {
            $data['mobil'] = trim($data['mobil']);
        }

        return $data;
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Auch beim Laden bereinigen
        if (isset($data['email']) && $data['email']) {
            $data['email'] = trim($data['email']);
        }
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];



        // Speichere return URL in der Session, wenn sie in der Query ist
        if ($returnUrl = request()->query('return')) {
            session(['aussteller_return_url' => $returnUrl]);
        }

        // Hole return URL aus der Session
        if ($returnUrl = session('aussteller_return_url')) {
            $actions[] = Actions\Action::make('back')
                ->label('Zurück zur Buchung')
                ->icon('heroicon-o-arrow-left')
                ->url($returnUrl);
        }

        $actions[] = CommentsAction::make()
            ->label('Kommentare');

        $actions[] =
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]);

        return $actions;
    }
}
