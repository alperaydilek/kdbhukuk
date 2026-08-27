<?php

namespace App\Filament\Resources\PlannedPayments;

use App\Filament\Resources\PlannedPayments\Pages\CreatePlannedPayment;
use App\Filament\Resources\PlannedPayments\Pages\EditPlannedPayment;
use App\Filament\Resources\PlannedPayments\Pages\ListPlannedPayments;
use App\Models\PlannedPayment;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlannedPaymentResource extends Resource
{
    protected static ?string $model = PlannedPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static string|\UnitEnum|null $navigationGroup = 'Muhasebe';

    protected static ?string $navigationLabel = 'Planlı Ödemeler';

    protected static ?string $modelLabel = 'Planlı Ödeme';

    protected static ?string $pluralModelLabel = 'Planlı Ödemeler';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::ACCOUNTING) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Başlık')
                    ->placeholder('Örn: Ofis Kirası — Eylül')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->label('Tutar (₺)')
                    ->numeric()
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Ödeme Tarihi')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->required(),
                TextInput::make('category')
                    ->label('Kategori')
                    ->placeholder('Örn: Kira, Sigorta, Abonelik...'),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->columns([
                TextColumn::make('title')->label('Başlık')->searchable(),
                TextColumn::make('category')->label('Kategori')->toggleable(),
                TextColumn::make('due_date')
                    ->label('Ödeme Tarihi')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (PlannedPayment $record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn (PlannedPayment $record) => $record->isOverdue() ? 'bold' : null),
                TextColumn::make('amount')->label('Tutar')->money('TRY')->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'odendi' => 'Ödendi',
                        'iptal' => 'İptal',
                        default => 'Bekliyor',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'odendi' => 'success',
                        'iptal' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'bekliyor' => 'Bekliyor',
                    'odendi' => 'Ödendi',
                    'iptal' => 'İptal',
                ]),
                TernaryFilter::make('overdue')
                    ->label('Vadesi Geçmiş')
                    ->queries(
                        true: fn (Builder $query) => $query->where('status', 'bekliyor')->whereDate('due_date', '<', now()),
                        false: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Ödendi Olarak İşaretle')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (PlannedPayment $record) => $record->status === 'bekliyor')
                    ->requiresConfirmation()
                    ->modalDescription('Bu tutar ana kasadan gider olarak düşülecektir.')
                    ->action(function (PlannedPayment $record): void {
                        $record->markPaid();

                        Notification::make()->title('Ödeme işlendi')->success()->send();
                    }),
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
            'index' => ListPlannedPayments::route('/'),
            'create' => CreatePlannedPayment::route('/create'),
            'edit' => EditPlannedPayment::route('/{record}/edit'),
        ];
    }
}
