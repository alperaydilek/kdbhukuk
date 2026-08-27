<?php

namespace App\Filament\Resources\CashboxTransactions\Pages;

use App\Filament\Resources\CashboxTransactions\CashboxTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashboxTransaction extends EditRecord
{
    protected static string $resource = CashboxTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
