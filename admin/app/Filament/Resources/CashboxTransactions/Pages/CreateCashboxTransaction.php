<?php

namespace App\Filament\Resources\CashboxTransactions\Pages;

use App\Filament\Resources\CashboxTransactions\CashboxTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashboxTransaction extends CreateRecord
{
    protected static string $resource = CashboxTransactionResource::class;
}
