<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class TaskBoard extends Page
{
    protected string $view = 'filament.pages.task-board';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|\UnitEnum|null $navigationGroup = 'Görevler';

    protected static ?string $navigationLabel = 'Görev Panosu';

    protected static ?string $title = 'Görev Panosu';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TASKS) ?? false;
    }

    /**
     * Pano yalnızca açık görevleri gösterir: "Tamamlandı" işaretlenen bir
     * görev panodan kaybolur, yalnızca "Tüm Görevler" listesinde kalır.
     *
     * @return array<string, Collection<int, Task>>
     */
    public function getColumns(): array
    {
        $tasks = Task::query()
            ->with(['assignee', 'assigner'])
            ->notDone()
            ->orderBy('due_date')
            ->get();

        return [
            Task::STATUS_TODO => $tasks->where('status', Task::STATUS_TODO)->values(),
            Task::STATUS_IN_PROGRESS => $tasks->where('status', Task::STATUS_IN_PROGRESS)->values(),
        ];
    }

    public function moveTask(int $taskId, string $status): void
    {
        $task = Task::query()->find($taskId);

        if (! $task) {
            return;
        }

        $task->markStatus($status);

        Notification::make()
            ->title('Görev durumu güncellendi')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTask')
                ->label('Yeni Görev')
                ->icon(Heroicon::Plus)
                ->schema([
                    TextInput::make('title')
                        ->label('Görev Başlığı')
                        ->required(),
                    Select::make('assigned_to')
                        ->label('Atanan Kişi')
                        ->options(fn () => User::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('due_date')
                        ->label('Son Teslim Tarihi')
                        ->native(false)
                        ->displayFormat('d.m.Y'),
                    Select::make('priority')
                        ->label('Öncelik')
                        ->options(Task::PRIORITIES)
                        ->default('normal')
                        ->required(),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(3),
                ])
                ->modalHeading('Yeni Görev Oluştur')
                ->modalSubmitActionLabel('Oluştur')
                ->action(function (array $data): void {
                    Task::query()->create([...$data, 'status' => Task::STATUS_TODO]);

                    Notification::make()->title('Görev oluşturuldu')->success()->send();
                }),
        ];
    }
}
