<?php

namespace App\Filament\Resources\BlogCategories;

use App\Filament\Resources\BlogCategories\Pages\ManageBlogCategories;
use App\Models\BlogCategory;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    protected static ?string $model = BlogCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Blog Kategorileri';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Blog Kategorileri';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Kategori Adı')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') || $set('slug', Str::slug($state, language: 'tr'))),
                TextInput::make('slug')
                    ->label('URL (slug)')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Kategori Adı')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('posts_count')->label('Yazı Sayısı')->counts('posts')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageBlogCategories::route('/'),
        ];
    }
}
