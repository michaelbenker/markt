<?php

namespace App\Filament\Resources\TerminResource\Pages;

use App\Filament\Resources\TerminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTermin extends CreateRecord
{
    protected static string $resource = TerminResource::class;
    
    public function mount(): void
    {
        parent::mount();
        
        // Wenn markt_id als Query-Parameter übergeben wurde, ins Formular eintragen
        if ($marktId = request()->query('markt_id')) {
            $this->form->fill([
                'markt_id' => $marktId
            ]);
        }
    }
}
