<?php

namespace App\Filament\Pages;

use App\Models\Page as PageModel;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomePageContent extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.home-page-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'Ana Sayfa İçeriği';

    protected static ?string $title = 'Ana Sayfa İçeriği';

    protected static ?int $navigationSort = 0;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(PageModel::query()->firstWhere('slug', 'home')?->data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Ana Sayfa')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Hero')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('hero_eyebrow')->label('Üst Etiket (Eyebrow)')->default('KDB Hukuk'),
                                        TextInput::make('hero_title')->label('Başlık')->required()->columnSpanFull(),
                                        Textarea::make('hero_desc')->label('Alt Açıklama')->rows(3)->columnSpanFull(),
                                        TextInput::make('hero_cta_primary_label')->label('Birincil Buton Metni')->default('Hukuki Hizmetlerimizi İnceleyin'),
                                        TextInput::make('hero_cta_secondary_label')->label('İkincil Buton Metni')->default('İletişime Geçin'),
                                    ]),
                            ]),
                        Tab::make('Tanıtım')
                            ->schema([
                                TextInput::make('intro_label')->label('Üst Etiket')->default('KDB Hukuk'),
                                Textarea::make('intro_text')->label('Kısa Marka Açıklaması')->rows(3)->columnSpanFull(),
                                Textarea::make('intro_statement')->label('Büyük Tipografik Cümle')->rows(2)->columnSpanFull(),
                            ]),
                        Tab::make('Avukat')
                            ->schema([
                                TextInput::make('lawyer_title')->label('Ünvan Satırı')->default('KDB Hukuk Kurucusu'),
                                RichEditor::make('lawyer_bio')->label('Kısa Biyografi')->columnSpanFull(),
                                TextInput::make('lawyer_cta_label')->label('Buton Metni')->default('Kaan Durali Bulut Hakkında'),
                            ]),
                        Tab::make('CTA')
                            ->schema([
                                TextInput::make('cta_title')->label('Başlık')->columnSpanFull(),
                                Textarea::make('cta_desc')->label('Açıklama')->rows(2)->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        PageModel::query()->updateOrCreate(
            ['slug' => 'home'],
            ['title' => 'Ana Sayfa', 'data' => $this->form->getState()],
        );

        Notification::make()->title('Ana sayfa içeriği kaydedildi')->success()->send();
    }
}
