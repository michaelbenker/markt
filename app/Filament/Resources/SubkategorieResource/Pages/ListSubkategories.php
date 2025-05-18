<?php

namespace App\Filament\Resources\SubkategorieResource\Pages;

use App\Filament\Resources\SubkategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubkategories extends ListRecords
{
    protected static string $resource = SubkategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
