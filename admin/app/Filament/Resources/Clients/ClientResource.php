<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Clients\RelationManagers\CommunicationLogsRelationManager;
use App\Filament\Resources\Clients\RelationManagers\DebtsRelationManager;
use App\Filament\Resources\Clients\RelationManagers\PaymentsRelationManager;
use App\Models\Client;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CLIENTS) ?? false;
    }

    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Müvekkiller';

    protected static ?string $navigationLabel = 'Müvekkiller';

    protected static ?string $modelLabel = 'Müvekkil';

    protected static ?string $pluralModelLabel = 'Müvekkiller';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kimlik Bilgileri')
                    ->columns(2)
                    ->schema([
                        Select::make('client_type')
                            ->label('Müvekkil Türü')
                            ->options(['bireysel' => 'Bireysel', 'kurumsal' => 'Kurumsal'])
                            ->default('bireysel')
                            ->live()
                            ->required(),
                        Select::make('status')
                            ->label('Durum')
                            ->options(['aktif' => 'Aktif', 'pasif' => 'Pasif'])
                            ->default('aktif')
                            ->required(),
                        TextInput::make('first_name')->label('Ad')->required(),
                        TextInput::make('last_name')->label('Soyad')->required(),
                        TextInput::make('company_name')
                            ->label('Şirket Unvanı')
                            ->visible(fn ($get) => $get('client_type') === 'kurumsal')
                            ->columnSpanFull(),
                        TextInput::make('tc_no')->label('TC Kimlik No'),
                        TextInput::make('tax_no')
                            ->label('Vergi No')
                            ->visible(fn ($get) => $get('client_type') === 'kurumsal'),
                        TextInput::make('case_no')->label('Dosya / Esas No'),
                    ]),
                Section::make('İletişim')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->label('Telefon')->tel()->required(),
                        TextInput::make('email')->label('E-posta')->email(),
                        Textarea::make('address')->label('Adres')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('Notlar')
                    ->schema([
                        Textarea::make('notes')->label('Notlar')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        TextEntry::make('total_debt')
                            ->label('Toplam Borçlandırma')
                            ->state(fn (Client $record) => $record->totalDebt())
                            ->money('TRY')
                            ->weight('bold'),
                        TextEntry::make('total_collected')
                            ->label('Toplam Tahsilat')
                            ->state(fn (Client $record) => $record->totalCollected())
                            ->money('TRY')
                            ->color('success')
                            ->weight('bold'),
                        TextEntry::make('total_pending')
                            ->label('Bekleyen (Vadeli) Tahsilat')
                            ->state(fn (Client $record) => $record->totalPending())
                            ->money('TRY')
                            ->color('warning')
                            ->weight('bold'),
                        TextEntry::make('balance')
                            ->label('Bakiye (Kalan Alacak)')
                            ->state(fn (Client $record) => $record->balance())
                            ->money('TRY')
                            ->color(fn (Client $record) => $record->balance() > 0 ? 'danger' : 'success')
                            ->weight('bold'),
                    ]),
                Section::make('Kimlik ve İletişim')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('fullName')->label('Ad Soyad / Unvan')->state(fn (Client $record) => $record->fullName()),
                        TextEntry::make('phone')->label('Telefon'),
                        TextEntry::make('email')->label('E-posta'),
                        TextEntry::make('case_no')->label('Dosya No')->placeholder('—'),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('address')->label('Adres')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')
                    ->label('Müvekkil')
                    ->formatStateUsing(fn (Client $record) => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'company_name'])
                    ->weight('semibold'),
                TextColumn::make('client_type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'kurumsal' ? 'Kurumsal' : 'Bireysel'),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('case_no')->label('Dosya No')->toggleable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'aktif' ? 'Aktif' : 'Pasif')
                    ->color(fn (string $state) => $state === 'aktif' ? 'success' : 'gray'),
                TextColumn::make('debts_sum_amount')
                    ->label('Toplam Borç')
                    ->sum('debts', 'amount')
                    ->money('TRY'),
            ])
            ->filters([
                SelectFilter::make('client_type')->label('Tür')->options(['bireysel' => 'Bireysel', 'kurumsal' => 'Kurumsal']),
                SelectFilter::make('status')->label('Durum')->options(['aktif' => 'Aktif', 'pasif' => 'Pasif']),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DebtsRelationManager::class,
            PaymentsRelationManager::class,
            CommunicationLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
