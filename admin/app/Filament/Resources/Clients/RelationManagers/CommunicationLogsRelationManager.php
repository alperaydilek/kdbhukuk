<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunicationLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'communicationLogs';

    protected static ?string $title = 'İletişim Geçmişi';

    protected static ?string $modelLabel = 'Kayıt';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('channel')->label('Kanal')->formatStateUsing(fn (string $state) => $state === 'mail' ? 'E-posta' : 'SMS'),
            TextEntry::make('to')->label('Alıcı'),
            TextEntry::make('subject')->label('Konu')->visible(fn ($record) => filled($record->subject)),
            TextEntry::make('body')->label('İçerik')->html()->columnSpanFull(),
            TextEntry::make('status')->label('Durum')->badge(),
            TextEntry::make('error_message')->label('Hata')->visible(fn ($record) => filled($record->error_message)),
            TextEntry::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'mail' ? 'E-posta' : 'SMS'),
                TextColumn::make('template_name')->label('Şablon'),
                TextColumn::make('to')->label('Alıcı'),
                TextColumn::make('subject')->label('Konu')->limit(30),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'gonderildi' ? 'Gönderildi' : 'Başarısız')
                    ->color(fn (string $state) => $state === 'gonderildi' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('channel')->options(['mail' => 'E-posta', 'sms' => 'SMS']),
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
