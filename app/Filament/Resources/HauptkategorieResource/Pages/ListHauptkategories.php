<?php

namespace App\Filament\Resources\HauptkategorieResource\Pages;

use App\Filament\Resources\HauptkategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHauptkategories extends ListRecords
{
    protected static string $resource = HauptkategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
