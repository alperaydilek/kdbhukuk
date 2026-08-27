<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Hizmetler';

    protected static ?string $modelLabel = 'Hizmet';

    protected static ?string $pluralModelLabel = 'Hizmetler';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hizmet')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Genel')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Hizmet Adı')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') || $set('slug', Str::slug($state, language: 'tr')))
                                            ->columnSpan(1),
                                        TextInput::make('slug')
                                            ->label('URL (slug)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(1),
                                        TextInput::make('order')
                                            ->label('Sıra No')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Ana sayfada ve hizmetler sayfasında görünme sırası (01, 02...).'),
                                        Toggle::make('is_published')
                                            ->label('Yayında')
                                            ->default(true),
                                        FileUpload::make('cover_image')
                                            ->label('Kapak Görseli')
                                            ->image()
                                            ->disk('r2')
                                            ->visibility('public')
                                            ->directory('services')
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                        Textarea::make('short_description')
                                            ->label('Kısa Açıklama')
                                            ->helperText('Hizmetler listesinde kart altında görünür.')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('İçerik')
                            ->schema([
                                RichEditor::make('description')
                                    ->label('Hizmet Açıklaması')
                                    ->columnSpanFull(),
                                RichEditor::make('covers')
                                    ->label('Hangi Konuları Kapsar?')
                                    ->columnSpanFull(),
                                RichEditor::make('scope')
                                    ->label('Hizmet Kapsamı')
                                    ->columnSpanFull(),
                                RichEditor::make('why_important')
                                    ->label('Neden Hukuki Destek Önemlidir?')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Süreç Adımları')
                            ->schema([
                                Repeater::make('process_steps')
                                    ->label('')
                                    ->addActionLabel('Adım Ekle')
                                    ->reorderableWithButtons()
                                    ->schema([
                                        TextInput::make('title')->label('Başlık')->required(),
                                        Textarea::make('description')->label('Açıklama')->rows(2)->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0),
                            ]),
                        Tab::make('Sık Sorulan Sorular')
                            ->schema([
                                Repeater::make('faqs')
                                    ->relationship()
                                    ->label('')
                                    ->addActionLabel('Soru Ekle')
                                    ->reorderableWithButtons()
                                    ->orderColumn('order')
                                    ->schema([
                                        TextInput::make('question')->label('Soru')->required()->columnSpanFull(),
                                        Textarea::make('answer')->label('Cevap')->rows(3)->required()->columnSpanFull(),
                                    ])
                                    ->defaultItems(0),
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
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('cover_image')->label('')->square(),
                TextColumn::make('order')->label('Sıra')->sortable(),
                TextColumn::make('title')->label('Hizmet')->searchable()->weight('semibold'),
                IconColumn::make('is_published')->label('Yayında')->boolean(),
                TextColumn::make('faqs_count')->label('SSS')->counts('faqs')->badge(),
                TextColumn::make('updated_at')->label('Güncellendi')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Yayın Durumu'),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
