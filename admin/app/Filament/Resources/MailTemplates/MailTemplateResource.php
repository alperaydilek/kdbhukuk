<?php

namespace App\Filament\Resources\MailTemplates;

use App\Filament\Resources\MailTemplates\Pages\CreateMailTemplate;
use App\Filament\Resources\MailTemplates\Pages\EditMailTemplate;
use App\Filament\Resources\MailTemplates\Pages\ListMailTemplates;
use App\Models\MailTemplate;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MailTemplateResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TEMPLATES) ?? false;
    }

    protected static ?string $model = MailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'İletişim Şablonları';

    protected static ?string $navigationLabel = 'Mail Şablonları';

    protected static ?string $modelLabel = 'Mail Şablonu';

    protected static ?string $pluralModelLabel = 'Mail Şablonları';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Şablon Adı')->required()->columnSpanFull(),
                        TextInput::make('subject')->label('E-posta Konusu')->required()->columnSpanFull(),
                        RichEditor::make('body')->label('İçerik')->required()->columnSpanFull(),
                        Textarea::make('description')->label('Kullanım Notu')->rows(2)->columnSpanFull(),
                        Placeholder::make('placeholders')
                            ->label('Kullanılabilir Alanlar')
                            ->content('{{client_name}}, {{client_phone}}, {{client_email}}, {{case_no}}, {{balance}}, {{office_name}}, {{lawyer_name}}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Şablon Adı')->searchable(),
                TextColumn::make('subject')->label('Konu')->limit(40),
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
            'index' => ListMailTemplates::route('/'),
            'create' => CreateMailTemplate::route('/create'),
            'edit' => EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
