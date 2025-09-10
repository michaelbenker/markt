<?php

namespace App\Filament\Resources\MailReportResource\Pages;

use App\Filament\Resources\MailReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailReports extends ListRecords
{
    protected static string $resource = MailReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
