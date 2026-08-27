<?php

namespace App\Filament\Resources\Tasks;

use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Models\Task;
use App\Models\User;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Görevler';

    protected static ?string $navigationLabel = 'Tüm Görevler';

    protected static ?string $modelLabel = 'Görev';

    protected static ?string $pluralModelLabel = 'Görevler';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TASKS) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('assigned_to', auth()->id())
            ->where('status', '!=', Task::STATUS_DONE)
            ->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Görev Başlığı')
                            ->required()
                            ->columnSpanFull(),
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
                        Select::make('status')
                            ->label('Durum')
                            ->options(Task::STATUSES)
                            ->default('yapilacak')
                            ->required(),
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->columns([
                TextColumn::make('title')->label('Görev')->searchable()->weight('semibold'),
                TextColumn::make('assignee.name')->label('Atanan')->badge(),
                TextColumn::make('assigner.name')->label('Atayan')->toggleable(),
                TextColumn::make('due_date')
                    ->label('Son Tarih')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (Task $record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn (Task $record) => $record->isOverdue() ? 'bold' : null),
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
                    ->color(fn (string $state) => match ($state) {
                        Task::STATUS_DONE => 'success',
                        Task::STATUS_IN_PROGRESS => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('assigned_to')->label('Atanan')->options(fn () => User::query()->pluck('name', 'id')),
                SelectFilter::make('status')->options(Task::STATUSES),
                SelectFilter::make('priority')->options(Task::PRIORITIES),
                TernaryFilter::make('overdue')
                    ->label('Süresi Geçmiş')
                    ->queries(
                        true: fn (Builder $query) => $query->where('status', '!=', Task::STATUS_DONE)->whereDate('due_date', '<', now()),
                        false: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
