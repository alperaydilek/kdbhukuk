<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Models\BlogPost;
use App\Services\Ai\AiContentGenerator;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Throwable;

class BlogPostResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Blog Yazıları';

    protected static ?string $modelLabel = 'Blog Yazısı';

    protected static ?string $pluralModelLabel = 'Blog Yazıları';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Yazı')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('İçerik')
                            ->schema([
                                SchemaActions::make([
                                    FilamentAction::make('generateWithAi')
                                        ->label('AI ile İçerik Üret')
                                        ->icon(Heroicon::Sparkles)
                                        ->color('primary')
                                        ->schema([
                                            Textarea::make('topic')
                                                ->label('Konu')
                                                ->placeholder('Örn: Kira uyuşmazlıklarında arabuluculuk süreci')
                                                ->helperText('Kısaca konuyu yazın; yapay zekâ SEO/AEO/GEO uyumlu tam metni oluştursun.')
                                                ->required()
                                                ->rows(2),
                                        ])
                                        ->modalHeading('Yapay Zekâ ile Blog İçeriği Üret')
                                        ->modalSubmitActionLabel('Oluştur')
                                        ->action(function (array $data, $set, $record): void {
                                            try {
                                                $result = app(AiContentGenerator::class)->generateBlogPost(
                                                    $data['topic'],
                                                    $record?->id,
                                                );

                                                $set('title', $result['title']);
                                                $set('slug', Str::slug($result['title'], language: 'tr'));
                                                $set('excerpt', $result['excerpt']);
                                                $set('content', $result['content']);
                                                $set('meta_title', $result['meta_title']);
                                                $set('meta_description', $result['meta_description']);
                                                $set('ai_generated', true);
                                                $set('ai_topic_prompt', $data['topic']);

                                                Notification::make()
                                                    ->title('İçerik oluşturuldu')
                                                    ->body('Başlık, özet, içerik ve SEO alanları dolduruldu. Yayınlamadan önce gözden geçirin.')
                                                    ->success()
                                                    ->send();
                                            } catch (Throwable $e) {
                                                Notification::make()
                                                    ->title('İçerik üretilemedi')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])->alignEnd(),
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Başlık')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') || $set('slug', Str::slug($state, language: 'tr')))
                                            ->columnSpanFull(),
                                        TextInput::make('slug')
                                            ->label('URL (slug)')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Select::make('blog_category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')->label('Kategori Adı')->required(),
                                            ]),
                                        FileUpload::make('cover_image')
                                            ->label('Kapak Görseli')
                                            ->image()
                                            ->disk('r2')
                                            ->visibility('public')
                                            ->directory('blog')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                        Textarea::make('excerpt')
                                            ->label('Kısa Özet')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        RichEditor::make('content')
                                            ->label('İçerik')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Yayın')
                            ->schema([
                                Select::make('status')
                                    ->label('Durum')
                                    ->options(['taslak' => 'Taslak', 'yayinda' => 'Yayında'])
                                    ->default('taslak')
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label('Öne Çıkan Yazı'),
                                TextInput::make('author_name')
                                    ->label('Yazar')
                                    ->default('Av. Kaan Durali Bulut'),
                                TextInput::make('reading_minutes')
                                    ->label('Okuma Süresi (dk)')
                                    ->numeric()
                                    ->helperText('Boş bırakılırsa içerik uzunluğuna göre otomatik hesaplanır.'),
                                Placeholder::make('ai_generated_info')
                                    ->label('Yapay Zekâ')
                                    ->content(fn ($get) => $get('ai_generated') ? '✅ Bu yazı yapay zekâ ile üretildi.' : '—'),
                            ]),
                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')->label('Meta Başlık')->maxLength(70)->columnSpanFull(),
                                Textarea::make('meta_description')->label('Meta Açıklama')->rows(2)->maxLength(160)->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')->label('')->square(),
                TextColumn::make('title')->label('Başlık')->searchable()->weight('semibold')->limit(40),
                TextColumn::make('category.name')->label('Kategori')->badge(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'yayinda' ? 'Yayında' : 'Taslak')
                    ->color(fn (string $state) => $state === 'yayinda' ? 'success' : 'gray'),
                IconColumn::make('ai_generated')->label('AI')->boolean(),
                IconColumn::make('is_featured')->label('Öne Çıkan')->boolean(),
                TextColumn::make('published_at')->label('Yayın Tarihi')->date('d.m.Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['taslak' => 'Taslak', 'yayinda' => 'Yayında']),
                SelectFilter::make('blog_category_id')->label('Kategori')->relationship('category', 'name'),
                TernaryFilter::make('is_featured')->label('Öne Çıkan'),
                TernaryFilter::make('ai_generated')->label('AI Üretimi'),
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
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
