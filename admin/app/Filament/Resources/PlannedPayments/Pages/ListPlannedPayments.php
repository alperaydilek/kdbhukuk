<?php

namespace App\Filament\Resources\PlannedPayments\Pages;

use App\Filament\Resources\PlannedPayments\PlannedPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlannedPayments extends ListRecords
{
    protected static string $resource = PlannedPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
