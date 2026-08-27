<?php

namespace App\Filament\Resources\AppointmentRequests;

use App\Filament\Resources\AppointmentRequests\Pages\EditAppointmentRequest;
use App\Filament\Resources\AppointmentRequests\Pages\ListAppointmentRequests;
use App\Models\AppointmentRequest;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentRequestResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::COMMUNICATIONS) ?? false;
    }

    protected static ?string $model = AppointmentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'İletişim';

    protected static ?string $navigationLabel = 'Randevu Talepleri';

    protected static ?string $modelLabel = 'Randevu Talebi';

    protected static ?string $pluralModelLabel = 'Randevu Talepleri';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'bekliyor')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Talep Bilgileri')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('İsim Soyisim')->disabled(),
                        TextInput::make('phone')->label('Telefon')->disabled(),
                        TextInput::make('email')->label('E-posta')->disabled()->columnSpanFull(),
                    ]),
                Section::make('Takip')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Durum')
                            ->options([
                                'bekliyor' => 'Bekliyor',
                                'onaylandi' => 'Onaylandı',
                                'iptal' => 'İptal',
                            ])
                            ->required(),
                        Textarea::make('note')->label('Not')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('İsim Soyisim')->searchable(),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'bekliyor' => 'Bekliyor',
                        'onaylandi' => 'Onaylandı',
                        default => 'İptal',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'bekliyor' => 'warning',
                        'onaylandi' => 'success',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'bekliyor' => 'Bekliyor',
                    'onaylandi' => 'Onaylandı',
                    'iptal' => 'İptal',
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
            'index' => ListAppointmentRequests::route('/'),
            'edit' => EditAppointmentRequest::route('/{record}/edit'),
        ];
    }
}
