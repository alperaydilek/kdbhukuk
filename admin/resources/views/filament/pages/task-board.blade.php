<x-filament-panels::page>
    @php
        $columns = $this->getColumns();
        $columnMeta = [
            \App\Models\Task::STATUS_TODO => ['label' => 'Yapılacak', 'dot' => 'bg-gray-400'],
            \App\Models\Task::STATUS_IN_PROGRESS => ['label' => 'Yapılıyor', 'dot' => 'bg-blue-500'],
        ];
        $statusOrder = array_keys($columnMeta);
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($columnMeta as $status => $meta)
            @php $tasks = $columns[$status]; @endphp
            <div class="flex flex-col gap-3 rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $meta['label'] }}</span>
                    </div>
                    <span class="text-xs text-gray-400">{{ $tasks->count() }}</span>
                </div>

                <div class="flex flex-col gap-2">
                    @forelse ($tasks as $task)
                        <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $task->title }}</p>
                                <x-filament::badge
                                    :color="match ($task->priority) {
                                        'yuksek' => 'danger',
                                        'dusuk' => 'gray',
                                        default => 'warning',
                                    }"
                                    size="xs"
                                >
                                    {{ \App\Models\Task::PRIORITIES[$task->priority] ?? $task->priority }}
                                </x-filament::badge>
                            </div>

                            @if ($task->description)
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $task->description }}</p>
                            @endif

                            <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-user-circle" class="h-4 w-4" />
                                    {{ $task->assignee?->name }}
                                </span>
                                @if ($task->due_date)
                                    <span @class([
                                        'inline-flex items-center gap-1',
                                        'font-semibold text-danger-600 dark:text-danger-400' => $task->isOverdue(),
                                    ])>
                                        <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                                        {{ $task->due_date->format('d.m.Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center gap-1.5 border-t border-gray-100 pt-2 dark:border-white/5">
                                @foreach ($statusOrder as $target)
                                    @continue($target === $status)
                                    <button
                                        type="button"
                                        wire:click="moveTask({{ $task->id }}, '{{ $target }}')"
                                        class="rounded-md px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10"
                                    >
                                        → {{ $columnMeta[$target]['label'] }}
                                    </button>
                                @endforeach

                                <button
                                    type="button"
                                    wire:click="moveTask({{ $task->id }}, '{{ \App\Models\Task::STATUS_DONE }}')"
                                    wire:confirm="Bu görevi tamamlandı olarak işaretlemek istediğinize emin misiniz? Görev panodan kaldırılacak."
                                    class="ms-auto inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-success-600 transition hover:bg-success-50 dark:text-success-400 dark:hover:bg-success-500/10"
                                >
                                    <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                                    Tamamlandı
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="px-1 py-6 text-center text-xs text-gray-400">Bu sütunda görev yok.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
