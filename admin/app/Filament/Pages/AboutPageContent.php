<?php

namespace App\Filament\Pages;

use App\Models\Page as PageModel;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AboutPageContent extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.about-page-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Hakkımızda İçeriği';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(PageModel::query()->firstWhere('slug', 'hakkimizda')?->data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hakkımızda')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Giriş')
                            ->schema([
                                TextInput::make('subtitle')->label('Alt Başlık')->columnSpanFull(),
                            ]),
                        Tab::make('Avukat Biyografisi')
                            ->schema([
                                RichEditor::make('lawyer_bio')->label('Uzun Biyografi')->columnSpanFull(),
                            ]),
                        Tab::make('İlkelerimiz')
                            ->schema([
                                Repeater::make('principles')
                                    ->label('')
                                    ->addActionLabel('İlke Ekle')
                                    ->reorderableWithButtons()
                                    ->schema([
                                        TextInput::make('title')->label('Başlık')->required(),
                                        Textarea::make('description')->label('Açıklama')->rows(2)->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        PageModel::query()->updateOrCreate(
            ['slug' => 'hakkimizda'],
            ['title' => 'Hakkımızda', 'data' => $this->form->getState()],
        );

        Notification::make()->title('Hakkımızda içeriği kaydedildi')->success()->send();
    }
}
