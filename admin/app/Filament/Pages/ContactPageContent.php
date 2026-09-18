<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\MapPicker;
use App\Models\Page as PageModel;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContactPageContent extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::CONTENT) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.contact-page-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $navigationLabel = 'İletişim Sayfası İçeriği';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(PageModel::query()->firstWhere('slug', 'iletisim')?->data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('İletişim Sayfası')
                    ->description('Telefon, e-posta ve adres bilgileri Ayarlar → Site Bilgileri üzerinden yönetilir.')
                    ->schema([
                        Textarea::make('intro_text')->label('Giriş Açıklaması')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('Ofis Konumu')
                    ->description('Seçtiğiniz konum iletişim sayfasındaki haritada gösterilir ve "Konuma Git" butonu bu noktayı açar.')
                    ->schema([
                        MapPicker::make('map_location')
                            ->label('Harita Üzerinde Konum')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        PageModel::query()->updateOrCreate(
            ['slug' => 'iletisim'],
            ['title' => 'İletişim', 'data' => $this->form->getState()],
        );

        Notification::make()->title('İletişim sayfası içeriği kaydedildi')->success()->send();
    }
}
