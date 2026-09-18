<?php

namespace App\Filament\Resources\ClientDebts;

use App\Filament\Resources\ClientDebts\Pages\ManageClientDebts;
use App\Models\Client;
use App\Models\ClientDebt;
use App\Models\ClientPayment;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientDebtResource extends Resource
{
    protected static ?string $model = ClientDebt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Müvekkiller';

    protected static ?string $navigationLabel = 'Alacak Listesi';

    protected static ?string $title = 'Alacak Listesi';

    protected static ?string $modelLabel = 'Alacak';

    protected static ?string $pluralModelLabel = 'Alacaklar';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CLIENTS) ?? false;
    }

    /**
     * Bekleyen alacak sayısı, gecikmişler varsa dikkat çekmek için
     * navigasyonda rozet olarak gösterilir.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = ClientDebt::query()->outstanding()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdue = ClientDebt::query()
            ->outstanding()
            ->whereDate('due_date', '<', now())
            ->exists();

        return $overdue ? 'danger' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('client_id')
                    ->label('Müvekkil')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'company_name'])
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Tür')
                    ->options([
                        ClientDebt::TYPE_FEE => 'Ücret (tahsilat bekleyen alacak)',
                        ClientDebt::TYPE_EXPENSE => 'Masraf (şimdi ödenen, kasadan düşer)',
                    ])
                    ->default(ClientDebt::TYPE_FEE)
                    ->required(),
                Select::make('status')
                    ->label('Durum')
                    ->options(ClientDebt::STATUSES)
                    ->default(ClientDebt::STATUS_PENDING)
                    ->required(),
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->label('Tutar (₺)')
                    ->numeric()
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Vade Tarihi')
                    ->native(false)
                    ->displayFormat('d.m.Y'),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            // Vadesi en yakın olan üstte: vadesi geçmiş alacaklar doğal olarak
            // listenin başında toplanır, vadesiz kayıtlar en sona düşer.
            ->defaultSort('due_date')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('client'))
            ->columns([
                TextColumn::make('due_date')
                    ->label('Vade')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Vade yok')
                    ->color(fn (ClientDebt $record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn (ClientDebt $record) => $record->isOverdue() ? 'bold' : null),
                TextColumn::make('due_state')
                    ->label('Gecikme')
                    ->badge()
                    ->state(fn (ClientDebt $record) => self::dueStateLabel($record))
                    ->color(fn (ClientDebt $record) => self::dueStateColor($record)),
                TextColumn::make('client.first_name')
                    ->label('Müvekkil')
                    ->formatStateUsing(fn (ClientDebt $record) => $record->client?->fullName())
                    ->searchable(['first_name', 'last_name', 'company_name'])
                    ->url(fn (ClientDebt $record) => $record->client
                        ? route('filament.admin.resources.clients.view', ['record' => $record->client_id])
                        : null),
                TextColumn::make('title')->label('Başlık')->searchable()->wrap(),
                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === ClientDebt::TYPE_EXPENSE ? 'Masraf' : 'Ücret')
                    ->color(fn (string $state) => $state === ClientDebt::TYPE_EXPENSE ? 'danger' : 'gray')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Tutar')
                    ->money('TRY')
                    ->sortable()
                    ->summarize(Sum::make()->label('Toplam')->money('TRY')),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ClientDebt::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ClientDebt::STATUS_PAID => 'success',
                        ClientDebt::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(ClientDebt::STATUSES)
                    ->default(ClientDebt::STATUS_PENDING),
                TernaryFilter::make('overdue')
                    ->label('Vadesi Geçmiş')
                    ->queries(
                        true: fn (Builder $query) => $query
                            ->where('status', ClientDebt::STATUS_PENDING)
                            ->whereDate('due_date', '<', now()),
                        false: fn (Builder $query) => $query,
                    ),
                SelectFilter::make('client_id')
                    ->label('Müvekkil')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'company_name'])
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tür')
                    ->options([
                        ClientDebt::TYPE_FEE => 'Ücret',
                        ClientDebt::TYPE_EXPENSE => 'Masraf',
                    ]),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Ödeme Alındı')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (ClientDebt $record) => $record->status === ClientDebt::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Ödeme alındı olarak işaretle')
                    ->modalDescription(fn (ClientDebt $record) => sprintf(
                        '%s müvekkilinden "%s" için %s tahsil edildi olarak işlenecek ve tutar ana kasaya gelir olarak eklenecektir.',
                        $record->client?->fullName() ?? 'Müvekkil',
                        $record->title,
                        number_format((float) $record->amount, 2, ',', '.').' ₺',
                    ))
                    ->modalSubmitActionLabel('Evet, tahsil edildi')
                    ->schema([
                        Select::make('method')
                            ->label('Tahsilat Yöntemi')
                            ->options(ClientPayment::METHODS)
                            ->default('nakit')
                            ->required(),
                        DatePicker::make('payment_date')
                            ->label('Tahsilat Tarihi')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (ClientDebt $record, array $data): void {
                        $record->markPaid($data['method'], $data['payment_date']);

                        Notification::make()
                            ->title('Tahsilat işlendi')
                            ->body('Alacak ödendi olarak işaretlendi ve ana kasaya gelir olarak eklendi.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()->label('Düzenle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Bekleyen alacak yok')
            ->emptyStateDescription('Müvekkil detay sayfasından borçlandırma ekleyebilirsiniz.');
    }

    protected static function dueStateLabel(ClientDebt $record): string
    {
        if ($record->status === ClientDebt::STATUS_PAID) {
            return 'Tahsil edildi';
        }

        if ($record->status === ClientDebt::STATUS_CANCELLED) {
            return 'İptal';
        }

        if ($record->due_date === null) {
            return 'Vade yok';
        }

        if ($record->isOverdue()) {
            return $record->daysOverdue().' gün geçmiş';
        }

        if ($record->due_date->isToday()) {
            return 'Bugün';
        }

        return (int) now()->startOfDay()->diffInDays($record->due_date).' gün kaldı';
    }

    protected static function dueStateColor(ClientDebt $record): string
    {
        if ($record->status === ClientDebt::STATUS_PAID) {
            return 'success';
        }

        if ($record->status === ClientDebt::STATUS_CANCELLED || $record->due_date === null) {
            return 'gray';
        }

        if ($record->isOverdue()) {
            return 'danger';
        }

        return $record->due_date->isToday() ? 'warning' : 'info';
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageClientDebts::route('/'),
        ];
    }
}
