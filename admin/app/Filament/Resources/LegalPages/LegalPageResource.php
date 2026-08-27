<?php

namespace App\Filament\Resources\LegalPages;

use App\Filament\Resources\LegalPages\Pages\ManageLegalPages;
use App\Models\LegalPage;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegalPageResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    protected static ?string $model = LegalPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Yasal Sayfalar';

    protected static ?string $modelLabel = 'Yasal Sayfa';

    protected static ?string $pluralModelLabel = 'Yasal Sayfalar';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('İçerik')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Sayfa')->weight('semibold'),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('updated_at')->label('Güncellendi')->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLegalPages::route('/'),
        ];
    }
}
