<?php

namespace App\Filament\Widgets;

use App\Models\ClientPayment;
use App\Support\AccessArea;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingIncomingPayments extends TableWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can(AccessArea::CLIENTS) ?? false;
    }

    public function getHeading(): string
    {
        return 'Yaklaşan Gelecek Ödemeler';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ClientPayment::query()
                    ->where('status', ClientPayment::STATUS_PENDING)
                    ->orderBy('payment_date'),
            )
            ->heading('Yaklaşan Gelecek Ödemeler')
            ->description('Müvekkillerden tahsil edilmeyi bekleyen vadeli çek/senetler')
            ->paginated(false)
            ->columns([
                TextColumn::make('client.first_name')
                    ->label('Müvekkil')
                    ->formatStateUsing(fn (ClientPayment $record) => $record->client?->fullName()),
                TextColumn::make('payment_date')
                    ->label('Vade')
                    ->date('d.m.Y')
                    ->color(fn (ClientPayment $record) => $record->payment_date->isPast() ? 'danger' : null),
                TextColumn::make('amount')->label('Tutar')->money('TRY'),
                TextColumn::make('method')
                    ->label('Yöntem')
                    ->formatStateUsing(fn (string $state) => ClientPayment::METHODS[$state] ?? $state)
                    ->badge(),
            ])
            ->recordActions([
                Action::make('markCollected')
                    ->label('Tahsil Et')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ClientPayment $record): void {
                        $record->markCollected();

                        Notification::make()->title('Tahsilat işlendi')->success()->send();
                    }),
            ]);
    }
}
