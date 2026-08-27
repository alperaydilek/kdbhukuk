<?php

namespace App\Filament\Pages;

use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Services\Ai\AiPricing;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AiSettings extends Page implements HasSchemas
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::SETTINGS) ?? false;
    }

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.ai-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Yapay Zekâ';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::getGroup('ai'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kullanım Özeti')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('total_spend')
                                    ->label('Toplam Tahmini Harcama')
                                    ->content(fn () => '$'.number_format((float) AiUsageLog::query()->sum('estimated_cost_usd'), 4)),
                                Placeholder::make('month_spend')
                                    ->label('Bu Ay Tahmini Harcama')
                                    ->content(fn () => '$'.number_format((float) AiUsageLog::query()
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->sum('estimated_cost_usd'), 4)),
                                Placeholder::make('total_generations')
                                    ->label('Toplam Üretim Sayısı')
                                    ->content(fn () => (string) AiUsageLog::query()->count()),
                            ]),
                    ]),
                Section::make('Sağlayıcı Seçimi')
                    ->description('Blog yazılarında "AI ile İçerik Üret" butonu bu ayarları kullanır.')
                    ->schema([
                        Radio::make('active_provider')
                            ->label('Aktif Sağlayıcı')
                            ->options([
                                'gemini' => 'Google Gemini',
                                'openai' => 'OpenAI (ChatGPT)',
                            ])
                            ->live()
                            ->inline(),
                    ]),
                Section::make('Google Gemini')
                    ->columns(2)
                    ->visible(fn ($get) => $get('active_provider') === 'gemini')
                    ->schema([
                        TextInput::make('gemini_api_key')->label('API Anahtarı')->password()->revealable(),
                        Select::make('gemini_model')
                            ->label('Model')
                            ->options(array_combine(AiPricing::modelsFor('gemini'), AiPricing::modelsFor('gemini')))
                            ->default('gemini-2.0-flash'),
                    ]),
                Section::make('OpenAI (ChatGPT)')
                    ->columns(2)
                    ->visible(fn ($get) => $get('active_provider') === 'openai')
                    ->schema([
                        TextInput::make('openai_api_key')->label('API Anahtarı')->password()->revealable(),
                        Select::make('openai_model')
                            ->label('Model')
                            ->options(array_combine(AiPricing::modelsFor('openai'), AiPricing::modelsFor('openai')))
                            ->default('gpt-4o-mini'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::putGroup('ai', $this->form->getState());

        Notification::make()->title('Yapay zekâ ayarları kaydedildi')->success()->send();
    }
}
