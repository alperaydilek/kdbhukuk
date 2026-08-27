<?php

namespace App\Filament\Resources\PlannedPayments\Pages;

use App\Filament\Resources\PlannedPayments\PlannedPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlannedPayment extends EditRecord
{
    protected static string $resource = PlannedPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
