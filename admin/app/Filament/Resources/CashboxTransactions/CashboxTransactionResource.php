<?php

namespace App\Filament\Resources\CashboxTransactions;

use App\Filament\Resources\CashboxTransactions\Pages\ListCashboxTransactions;
use App\Models\CashboxTransaction;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashboxTransactionResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::ACCOUNTING) ?? false;
    }

    protected static ?string $model = CashboxTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Muhasebe';

    protected static ?string $navigationLabel = 'Ana Kasa';

    protected static ?string $modelLabel = 'Kasa Hareketi';

    protected static ?string $pluralModelLabel = 'Kasa Hareketleri';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Hidden::make('type'),
                Select::make('client_id')
                    ->label('Müvekkil (opsiyonel)')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->fullName())
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->label('Tutar (₺)')
                    ->numeric()
                    ->required(),
                DatePicker::make('date')
                    ->label('Tarih')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->default(now())
                    ->required(),
                TextInput::make('category')
                    ->label('Kategori')
                    ->placeholder('Örn: Ofis Kirası, Kırtasiye, Vekalet Ücreti...'),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        CashboxTransaction::TYPE_INCOME => 'Gelir',
                        CashboxTransaction::TYPE_EXPENSE => 'Gider',
                        CashboxTransaction::TYPE_DEPOSIT => 'Kasaya Yatırma',
                        default => 'Kasadan Çekme',
                    })
                    ->color(fn (string $state) => match ($state) {
                        CashboxTransaction::TYPE_INCOME, CashboxTransaction::TYPE_DEPOSIT => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('client.first_name')
                    ->label('Müvekkil')
                    ->formatStateUsing(fn ($record) => $record->client?->fullName() ?? '—'),
                TextColumn::make('category')->label('Kategori')->toggleable(),
                TextColumn::make('description')->label('Açıklama')->limit(40)->toggleable(),
                TextColumn::make('amount')
                    ->label('Tutar')
                    ->money('TRY')
                    ->color(fn (CashboxTransaction $record) => in_array($record->type, CashboxTransaction::increasingTypes()) ? 'success' : 'danger')
                    ->formatStateUsing(fn (CashboxTransaction $record) => (in_array($record->type, CashboxTransaction::increasingTypes()) ? '+ ' : '- ').number_format((float) $record->amount, 2, ',', '.').' ₺'),
                TextColumn::make('source')
                    ->label('Kaynak')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'client_payment' => 'Müvekkil Tahsilatı',
                        'client_debt' => 'Müvekkil Masrafı',
                        default => 'Manuel',
                    })
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tür')->options([
                    CashboxTransaction::TYPE_INCOME => 'Gelir',
                    CashboxTransaction::TYPE_EXPENSE => 'Gider',
                    CashboxTransaction::TYPE_DEPOSIT => 'Kasaya Yatırma',
                    CashboxTransaction::TYPE_WITHDRAWAL => 'Kasadan Çekme',
                ]),
                SelectFilter::make('client_id')
                    ->label('Müvekkil')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->fullName())
                    ->searchable(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('Başlangıç')->native(false)->displayFormat('d.m.Y'),
                        DatePicker::make('until')->label('Bitiş')->native(false)->displayFormat('d.m.Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
                    })
                    ->columnSpan(2),
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
            'index' => ListCashboxTransactions::route('/'),
        ];
    }
}
