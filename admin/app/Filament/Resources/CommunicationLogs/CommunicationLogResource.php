<?php

namespace App\Filament\Resources\CommunicationLogs;

use App\Filament\Resources\CommunicationLogs\Pages\ManageCommunicationLogs;
use App\Models\CommunicationLog;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunicationLogResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TEMPLATES) ?? false;
    }

    protected static ?string $model = CommunicationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'İletişim Şablonları';

    protected static ?string $navigationLabel = 'Gönderim Geçmişi';

    protected static ?string $modelLabel = 'Gönderim Kaydı';

    protected static ?string $pluralModelLabel = 'Gönderim Geçmişi';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('client.first_name')->label('Müvekkil')->formatStateUsing(fn ($record) => $record->client?->fullName() ?? '—'),
            TextEntry::make('channel')->label('Kanal')->formatStateUsing(fn (string $state) => $state === 'mail' ? 'E-posta' : 'SMS'),
            TextEntry::make('to')->label('Alıcı'),
            TextEntry::make('subject')->label('Konu')->visible(fn ($record) => filled($record->subject)),
            TextEntry::make('body')->label('İçerik')->html()->columnSpanFull(),
            TextEntry::make('status')->label('Durum')->badge(),
            TextEntry::make('error_message')->label('Hata')->visible(fn ($record) => filled($record->error_message)),
            TextEntry::make('sender.name')->label('Gönderen'),
            TextEntry::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('client.first_name')
                    ->label('Müvekkil')
                    ->formatStateUsing(fn ($record) => $record->client?->fullName() ?? '—'),
                TextColumn::make('channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'mail' ? 'E-posta' : 'SMS'),
                TextColumn::make('template_name')->label('Şablon'),
                TextColumn::make('to')->label('Alıcı'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'gonderildi' ? 'Gönderildi' : 'Başarısız')
                    ->color(fn (string $state) => $state === 'gonderildi' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('channel')->options(['mail' => 'E-posta', 'sms' => 'SMS']),
                SelectFilter::make('status')->options(['gonderildi' => 'Gönderildi', 'basarisiz' => 'Başarısız']),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommunicationLogs::route('/'),
        ];
    }
}
