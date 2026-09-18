<?php

namespace App\Filament\Resources\ClientDebts\Pages;

use App\Filament\Resources\ClientDebts\ClientDebtResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageClientDebts extends ManageRecords
{
    protected static string $resource = ClientDebtResource::class;

    public function getTitle(): string
    {
        return 'Alacak Listesi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Alacak Ekle'),
        ];
    }
}
