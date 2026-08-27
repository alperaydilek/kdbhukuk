<?php

namespace App\Filament\Widgets;

use App\Models\PlannedPayment;
use App\Support\AccessArea;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingOutgoingPayments extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can(AccessArea::ACCOUNTING) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PlannedPayment::query()
                    ->where('status', PlannedPayment::STATUS_PENDING)
                    ->orderBy('due_date'),
            )
            ->heading('Yaklaşan Gidecek Ödemeler')
            ->description('Bürodan yapılması planlanan ödemeler (kira, abonelik, sigorta vb.)')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->label('Başlık'),
                TextColumn::make('category')->label('Kategori')->placeholder('—'),
                TextColumn::make('due_date')
                    ->label('Tarih')
                    ->date('d.m.Y')
                    ->color(fn (PlannedPayment $record) => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('amount')->label('Tutar')->money('TRY'),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Ödendi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PlannedPayment $record): void {
                        $record->markPaid();

                        Notification::make()->title('Ödeme işlendi')->success()->send();
                    }),
            ]);
    }
}
