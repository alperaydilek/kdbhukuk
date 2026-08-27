<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Models\ClientPayment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Tahsilatlar / Ödemeler';

    protected static ?string $modelLabel = 'Ödeme';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('method')
                    ->label('Ödeme Yöntemi')
                    ->options(ClientPayment::METHODS)
                    ->default('nakit')
                    ->live()
                    ->required(),
                TextInput::make('amount')
                    ->label('Tutar (₺)')
                    ->numeric()
                    ->required(),
                DatePicker::make('payment_date')
                    ->label('Tarih')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->default(now())
                    ->helperText('Vadeli çek/senet için vade tarihini girin.')
                    ->required(),
                Toggle::make('is_postdated')
                    ->label('İleri Tarihli (Vadeli Çek / Senet)')
                    ->live()
                    ->helperText('Açıksa ödeme "bekliyor" durumunda kaydedilir; vade tarihinde manuel olarak tahsil edilir.'),
                TextInput::make('instrument_no')
                    ->label('Çek / Senet No')
                    ->visible(fn ($get) => in_array($get('method'), ['cek', 'senet']))
                    ->columnSpan(1),
                TextInput::make('bank_name')
                    ->label('Banka')
                    ->visible(fn ($get) => $get('method') === 'cek')
                    ->columnSpan(1),
                Select::make('client_debt_id')
                    ->label('İlişkili Borçlandırma')
                    ->relationship('debt', 'title')
                    ->searchable()
                    ->preload()
                    ->helperText('Bu ödeme belirli bir borçlandırmayı kapatıyorsa seçin.')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('payment_date', 'desc')
            ->columns([
                TextColumn::make('payment_date')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('method')
                    ->label('Yöntem')
                    ->formatStateUsing(fn (string $state) => ClientPayment::METHODS[$state] ?? $state)
                    ->badge(),
                TextColumn::make('amount')->label('Tutar')->money('TRY')->sortable(),
                IconColumn::make('is_postdated')->label('Vadeli')->boolean(),
                TextColumn::make('instrument_no')->label('Çek/Senet No')->toggleable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'tahsil_edildi' => 'Tahsil Edildi',
                        'karsiliksiz' => 'Karşılıksız',
                        'iptal' => 'İptal',
                        default => 'Bekliyor',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'tahsil_edildi' => 'success',
                        'karsiliksiz' => 'danger',
                        'iptal' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'bekliyor' => 'Bekliyor',
                    'tahsil_edildi' => 'Tahsil Edildi',
                    'karsiliksiz' => 'Karşılıksız',
                    'iptal' => 'İptal',
                ]),
                SelectFilter::make('method')->label('Yöntem')->options(ClientPayment::METHODS),
            ])
            ->headerActions([
                CreateAction::make()->label('Ödeme Ekle'),
            ])
            ->recordActions([
                Action::make('markCollected')
                    ->label('Tahsil Et')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (ClientPayment $record) => $record->status === ClientPayment::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalDescription('Bu tutar ana kasaya gelir olarak işlenecektir.')
                    ->action(function (ClientPayment $record): void {
                        $record->markCollected();

                        Notification::make()->title('Tahsilat işlendi')->success()->send();
                    }),
                Action::make('markBounced')
                    ->label('Karşılıksız')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn (ClientPayment $record) => $record->status === ClientPayment::STATUS_PENDING && in_array($record->method, ['cek', 'senet']))
                    ->requiresConfirmation()
                    ->action(function (ClientPayment $record): void {
                        $record->markBounced();

                        Notification::make()->title('Karşılıksız olarak işaretlendi')->warning()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
