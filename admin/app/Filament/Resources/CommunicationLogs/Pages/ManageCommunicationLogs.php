<?php

namespace App\Filament\Resources\CommunicationLogs\Pages;

use App\Filament\Resources\CommunicationLogs\CommunicationLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCommunicationLogs extends ManageRecords
{
    protected static string $resource = CommunicationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
