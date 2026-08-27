<?php

namespace App\Filament\Resources\CashboxTransactions\Widgets;

use App\Models\CashboxTransaction;
use App\Models\ClientPayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashboxOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $balance = CashboxTransaction::currentBalance();

        $monthlyIncome = (float) CashboxTransaction::query()
            ->where('type', CashboxTransaction::TYPE_INCOME)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $monthlyExpense = (float) CashboxTransaction::query()
            ->whereIn('type', [CashboxTransaction::TYPE_EXPENSE])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $pendingChecks = (float) ClientPayment::query()
            ->where('status', ClientPayment::STATUS_PENDING)
            ->sum('amount');

        return [
            Stat::make('Kasa Bakiyesi', number_format($balance, 2, ',', '.').' ₺')
                ->color($balance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Bu Ay Gelir', number_format($monthlyIncome, 2, ',', '.').' ₺')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Bu Ay Gider', number_format($monthlyExpense, 2, ',', '.').' ₺')
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),
            Stat::make('Bekleyen Çek/Senet', number_format($pendingChecks, 2, ',', '.').' ₺')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->description('Vadesi henüz gelmemiş veya tahsil edilmemiş'),
        ];
    }
}
