<?php

namespace App\Filament\Resources\CashboxTransactions\Pages;

use App\Filament\Resources\CashboxTransactions\CashboxTransactionResource;
use App\Filament\Resources\CashboxTransactions\Widgets\CashboxOverview;
use App\Models\CashboxTransaction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCashboxTransactions extends ListRecords
{
    protected static string $resource = CashboxTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('income')
                ->label('Gelir Ekle')
                ->icon(Heroicon::PlusCircle)
                ->color('success')
                ->modalHeading('Gelir Ekle')
                ->mutateFormDataUsing(fn (array $data) => [...$data, 'type' => CashboxTransaction::TYPE_INCOME, 'source' => 'manuel']),
            CreateAction::make('expense')
                ->label('Gider Ekle')
                ->icon(Heroicon::MinusCircle)
                ->color('danger')
                ->modalHeading('Gider Ekle')
                ->mutateFormDataUsing(fn (array $data) => [...$data, 'type' => CashboxTransaction::TYPE_EXPENSE, 'source' => 'manuel']),
            CreateAction::make('deposit')
                ->label('Kasaya Yatır')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->modalHeading('Kasaya Para Yatır')
                ->mutateFormDataUsing(fn (array $data) => [...$data, 'type' => CashboxTransaction::TYPE_DEPOSIT, 'source' => 'manuel']),
            CreateAction::make('withdrawal')
                ->label('Kasadan Çek')
                ->icon(Heroicon::ArrowUpTray)
                ->color('gray')
                ->modalHeading('Kasadan Para Çek')
                ->mutateFormDataUsing(fn (array $data) => [...$data, 'type' => CashboxTransaction::TYPE_WITHDRAWAL, 'source' => 'manuel']),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashboxOverview::class,
        ];
    }
}
