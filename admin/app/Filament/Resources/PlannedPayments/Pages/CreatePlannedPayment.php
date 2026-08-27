<?php

namespace App\Filament\Resources\PlannedPayments\Pages;

use App\Filament\Resources\PlannedPayments\PlannedPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlannedPayment extends CreateRecord
{
    protected static string $resource = PlannedPaymentResource::class;
}
