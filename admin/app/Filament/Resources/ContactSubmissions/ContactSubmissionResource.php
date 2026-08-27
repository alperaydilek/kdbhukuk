<?php

namespace App\Filament\Resources\ContactSubmissions;

use App\Filament\Resources\ContactSubmissions\Pages\EditContactSubmission;
use App\Filament\Resources\ContactSubmissions\Pages\ListContactSubmissions;
use App\Models\ContactSubmission;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::COMMUNICATIONS) ?? false;
    }

    protected static ?string $model = ContactSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'İletişim';

    protected static ?string $navigationLabel = 'İletişim Formu';

    protected static ?string $modelLabel = 'İletişim Talebi';

    protected static ?string $pluralModelLabel = 'İletişim Talepleri';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'yeni')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gönderilen Bilgiler')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Ad Soyad')->disabled(),
                        TextInput::make('phone')->label('Telefon')->disabled(),
                        TextInput::make('email')->label('E-posta')->disabled(),
                        TextInput::make('subject')->label('Konu')->disabled(),
                        Textarea::make('message')->label('Mesaj')->disabled()->rows(4)->columnSpanFull(),
                    ]),
                Section::make('Takip')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Durum')
                            ->options([
                                'yeni' => 'Yeni',
                                'iletisime_gecildi' => 'İletişime Geçildi',
                                'kapandi' => 'Kapandı',
                            ])
                            ->required(),
                        Textarea::make('admin_note')->label('Not')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('Ad Soyad')->searchable(),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('subject')->label('Konu'),
                IconColumn::make('kvkk_consent')->label('KVKK')->boolean(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'yeni' => 'Yeni',
                        'iletisime_gecildi' => 'İletişime Geçildi',
                        default => 'Kapandı',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'yeni' => 'warning',
                        'iletisime_gecildi' => 'info',
                        default => 'success',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'yeni' => 'Yeni',
                    'iletisime_gecildi' => 'İletişime Geçildi',
                    'kapandi' => 'Kapandı',
                ]),
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactSubmissions::route('/'),
            'edit' => EditContactSubmission::route('/{record}/edit'),
        ];
    }
}
