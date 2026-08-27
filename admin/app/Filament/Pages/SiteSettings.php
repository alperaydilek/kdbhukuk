<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteSettings extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::SETTINGS) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Site Bilgileri';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::getGroup('site'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('İletişim Bilgileri')
                    ->description('Bu bilgiler site genelinde footer, iletişim sayfası ve CTA alanlarında otomatik olarak görünür.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->label('Telefon'),
                        TextInput::make('whatsapp')->label('WhatsApp'),
                        TextInput::make('email')->label('E-posta')->email(),
                        TextInput::make('working_hours')->label('Çalışma Saatleri')->default('Hafta içi 09.00 — 18.00'),
                        TextInput::make('address')->label('Adres')->columnSpanFull(),
                    ]),
                Section::make('Sosyal Medya')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')->label('Instagram')->url(),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::putGroup('site', $this->form->getState());

        Notification::make()->title('Site bilgileri kaydedildi')->success()->send();
    }
}
