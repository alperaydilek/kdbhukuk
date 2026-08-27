<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SmsSettings extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::SETTINGS) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.sms-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'SMS';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::getGroup('sms'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SMS Sağlayıcısı')
                    ->description('Kullanacağınız SMS sağlayıcısını seçin ve hesap bilgilerinizi girin.')
                    ->schema([
                        Radio::make('active_provider')
                            ->label('Aktif Sağlayıcı')
                            ->options([
                                'mutlucell' => 'Mutlucell',
                                'netgsm' => 'Netgsm',
                            ])
                            ->live()
                            ->inline(),
                    ]),
                Section::make('Mutlucell Bilgileri')
                    ->columns(2)
                    ->visible(fn ($get) => $get('active_provider') === 'mutlucell')
                    ->schema([
                        TextInput::make('mutlucell_username')->label('Kullanıcı Adı'),
                        TextInput::make('mutlucell_password')->label('Şifre')->password()->revealable(),
                        TextInput::make('mutlucell_originator')->label('Gönderici Başlığı (Originator)')->columnSpanFull(),
                    ]),
                Section::make('Netgsm Bilgileri')
                    ->columns(2)
                    ->visible(fn ($get) => $get('active_provider') === 'netgsm')
                    ->schema([
                        TextInput::make('netgsm_username')->label('Kullanıcı Adı'),
                        TextInput::make('netgsm_password')->label('Şifre')->password()->revealable(),
                        TextInput::make('netgsm_header')->label('Mesaj Başlığı (Onaylı)')->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::putGroup('sms', $this->form->getState());

        Notification::make()->title('SMS ayarları kaydedildi')->success()->send();
    }
}
