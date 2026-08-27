<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Support\AccessArea;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MyTasksWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can(AccessArea::TASKS) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->where('assigned_to', auth()->id())
                    ->notDone()
                    ->orderBy('due_date'),
            )
            ->heading('Görevlerim')
            ->description('Size atanan, henüz tamamlanmamış görevler')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->label('Görev'),
                TextColumn::make('assigner.name')->label('Atayan')->placeholder('—'),
                TextColumn::make('due_date')
                    ->label('Son Tarih')
                    ->date('d.m.Y')
                    ->color(fn (Task $record) => $record->isOverdue() ? 'danger' : null)
                    ->placeholder('—'),
                TextColumn::make('priority')
                    ->label('Öncelik')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Task::PRIORITIES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'yuksek' => 'danger',
                        'dusuk' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Task::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => $state === Task::STATUS_IN_PROGRESS ? 'info' : 'gray'),
            ])
            ->recordActions([
                Action::make('markDone')
                    ->label('Tamamla')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Task $record) => $record->markStatus(Task::STATUS_DONE)),
            ]);
    }
}
