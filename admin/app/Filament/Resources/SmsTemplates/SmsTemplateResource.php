<?php

namespace App\Filament\Resources\SmsTemplates;

use App\Filament\Resources\SmsTemplates\Pages\CreateSmsTemplate;
use App\Filament\Resources\SmsTemplates\Pages\EditSmsTemplate;
use App\Filament\Resources\SmsTemplates\Pages\ListSmsTemplates;
use App\Models\SmsTemplate;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SmsTemplateResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TEMPLATES) ?? false;
    }

    protected static ?string $model = SmsTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|\UnitEnum|null $navigationGroup = 'İletişim Şablonları';

    protected static ?string $navigationLabel = 'SMS Şablonları';

    protected static ?string $modelLabel = 'SMS Şablonu';

    protected static ?string $pluralModelLabel = 'SMS Şablonları';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')->label('Şablon Adı')->required()->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Mesaj İçeriği')
                            ->required()
                            ->rows(3)
                            ->maxLength(480)
                            ->columnSpanFull(),
                        Textarea::make('description')->label('Kullanım Notu')->rows(2)->columnSpanFull(),
                        Placeholder::make('placeholders')
                            ->label('Kullanılabilir Alanlar')
                            ->content('{{client_name}}, {{client_phone}}, {{case_no}}, {{balance}}, {{office_name}}, {{lawyer_name}}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Şablon Adı')->searchable(),
                TextColumn::make('body')->label('İçerik')->limit(50),
                TextColumn::make('updated_at')->label('Güncellendi')->dateTime('d.m.Y H:i'),
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
            'index' => ListSmsTemplates::route('/'),
            'create' => CreateSmsTemplate::route('/create'),
            'edit' => EditSmsTemplate::route('/{record}/edit'),
        ];
    }
}
