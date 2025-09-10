<?php

namespace App\Filament\Resources\MailReportResource\Pages;

use App\Filament\Resources\MailReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailReport extends EditRecord
{
    protected static string $resource = MailReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
