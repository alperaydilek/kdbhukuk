<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class MailSettings extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::SETTINGS) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.mail-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'E-posta (Google Mail)';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $defaults = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
        ];

        $this->form->fill([...$defaults, ...Setting::getGroup('mail')]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Google Mail SMTP Bilgileri')
                    ->description('Google hesabınızda "Uygulama Şifresi" (App Password) oluşturup buraya girin. 2 Adımlı Doğrulama açık olmalıdır: myaccount.google.com/apppasswords')
                    ->columns(2)
                    ->schema([
                        TextInput::make('host')->label('SMTP Sunucu')->default('smtp.gmail.com')->required(),
                        TextInput::make('port')->label('Port')->numeric()->default(587)->required(),
                        Select::make('encryption')
                            ->label('Şifreleme')
                            ->options(['tls' => 'TLS', 'ssl' => 'SSL'])
                            ->default('tls')
                            ->required(),
                        TextInput::make('username')->label('Google E-posta Adresi')->email()->required(),
                        TextInput::make('app_password')
                            ->label('Uygulama Şifresi (App Password)')
                            ->password()
                            ->revealable()
                            ->required(),
                        TextInput::make('from_name')->label('Gönderen Adı')->default('KDB Hukuk'),
                        TextInput::make('from_address')->label('Gönderen E-posta')->email(),
                        Placeholder::make('info')
                            ->label('')
                            ->content('Normal Google şifreniz burada çalışmaz; mutlaka bir "Uygulama Şifresi" oluşturmanız gerekir.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::putGroup('mail', $this->form->getState());

        Notification::make()->title('E-posta ayarları kaydedildi')->success()->send();
    }
}
