<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\ClientPayment;
use App\Models\MailTemplate;
use App\Models\SmsTemplate;
use App\Services\Mail\MailSender;
use App\Services\Sms\SmsSender;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Throwable;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addDebt')
                ->label('Borçlandır')
                ->icon(Heroicon::PlusCircle)
                ->color('danger')
                ->schema([
                    Select::make('type')
                        ->label('Tür')
                        ->options([
                            'ucret' => 'Ücret (tahsilat bekleyen alacak)',
                            'masraf' => 'Masraf (şimdi ödenen, kasadan düşer)',
                        ])
                        ->default('ucret')
                        ->required()
                        ->helperText('"Masraf" seçilirse tutar anında ana kasadan gider olarak işlenir.'),
                    TextInput::make('title')
                        ->label('Başlık')
                        ->required(),
                    TextInput::make('amount')
                        ->label('Tutar (₺)')
                        ->numeric()
                        ->required(),
                    DatePicker::make('due_date')
                        ->label('Vade Tarihi')
                        ->native(false)
                        ->displayFormat('d.m.Y'),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(2),
                ])
                ->modalHeading('Müvekkili Borçlandır')
                ->modalSubmitActionLabel('Kaydet')
                ->action(function (array $data): void {
                    $this->record->debts()->create([...$data, 'status' => 'bekliyor']);

                    Notification::make()->title('Borçlandırma kaydedildi')->success()->send();
                }),

            Action::make('addPayment')
                ->label('Ödeme Ekle')
                ->icon(Heroicon::PlusCircle)
                ->color('success')
                ->schema([
                    Select::make('method')
                        ->label('Ödeme Yöntemi')
                        ->options(ClientPayment::METHODS)
                        ->default('nakit')
                        ->live()
                        ->required(),
                    TextInput::make('amount')
                        ->label('Tutar (₺)')
                        ->numeric()
                        ->required(),
                    DatePicker::make('payment_date')
                        ->label('Tarih')
                        ->native(false)
                        ->displayFormat('d.m.Y')
                        ->default(now())
                        ->helperText('Vadeli çek/senet için vade tarihini girin.')
                        ->required(),
                    Toggle::make('is_postdated')
                        ->label('İleri Tarihli (Vadeli Çek / Senet)')
                        ->live()
                        ->helperText('Açıksa ödeme "bekliyor" durumunda kaydedilir; vade tarihinde manuel olarak tahsil edilir.'),
                    TextInput::make('instrument_no')
                        ->label('Çek / Senet No')
                        ->visible(fn ($get) => in_array($get('method'), ['cek', 'senet'])),
                    TextInput::make('bank_name')
                        ->label('Banka')
                        ->visible(fn ($get) => $get('method') === 'cek'),
                    Select::make('client_debt_id')
                        ->label('İlişkili Borçlandırma')
                        ->options(fn () => $this->record->debts()->where('status', 'bekliyor')->pluck('title', 'id'))
                        ->searchable()
                        ->helperText('Bu ödeme belirli bir borçlandırmayı kapatıyorsa seçin.'),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(2),
                ])
                ->modalHeading('Müvekkilden Ödeme Ekle')
                ->modalSubmitActionLabel('Kaydet')
                ->action(function (array $data): void {
                    $this->record->payments()->create($data);

                    Notification::make()->title('Ödeme kaydedildi')->success()->send();
                }),

            Action::make('sendMail')
                ->label('Mail Gönder')
                ->icon(Heroicon::Envelope)
                ->color('gray')
                ->disabled(fn () => blank($this->record->email))
                ->tooltip(fn () => blank($this->record->email) ? 'Müvekkilin e-posta adresi kayıtlı değil.' : null)
                ->schema([
                    Select::make('mail_template_id')
                        ->label('Şablon Seç (opsiyonel)')
                        ->options(MailTemplate::query()->pluck('name', 'id'))
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (blank($state)) {
                                return;
                            }

                            $template = MailTemplate::query()->find($state);

                            if (! $template) {
                                return;
                            }

                            $rendered = $template->render($this->placeholderData());
                            $set('subject', $rendered['subject']);
                            $set('body', $rendered['body']);
                        }),
                    TextInput::make('subject')
                        ->label('Konu')
                        ->required(),
                    RichEditor::make('body')
                        ->label('İçerik')
                        ->required(),
                ])
                ->modalHeading('Müvekkile Mail Gönder')
                ->modalSubmitActionLabel('Gönder')
                ->action(function (array $data): void {
                    try {
                        $log = app(MailSender::class)->send(
                            $this->record,
                            $data['subject'],
                            $data['body'],
                            optional(MailTemplate::query()->find($data['mail_template_id'] ?? null))->name,
                        );

                        $log->status === 'gonderildi'
                            ? Notification::make()->title('Mail gönderildi')->success()->send()
                            : Notification::make()->title('Mail gönderilemedi')->body($log->error_message)->danger()->send();
                    } catch (Throwable $e) {
                        Notification::make()->title('Mail gönderilemedi')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('sendSms')
                ->label('SMS Gönder')
                ->icon(Heroicon::DevicePhoneMobile)
                ->color('gray')
                ->schema([
                    Select::make('sms_template_id')
                        ->label('Şablon Seç (opsiyonel)')
                        ->options(SmsTemplate::query()->pluck('name', 'id'))
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (blank($state)) {
                                return;
                            }

                            $template = SmsTemplate::query()->find($state);

                            if (! $template) {
                                return;
                            }

                            $set('message', $template->render($this->placeholderData()));
                        }),
                    Textarea::make('message')
                        ->label('Mesaj')
                        ->rows(3)
                        ->required()
                        ->maxLength(480)
                        ->helperText('Standart bir SMS 160 karakterdir; uzun mesajlar birden fazla SMS olarak ücretlendirilebilir.'),
                ])
                ->modalHeading('Müvekkile SMS Gönder')
                ->modalSubmitActionLabel('Gönder')
                ->action(function (array $data): void {
                    try {
                        $log = app(SmsSender::class)->send(
                            $this->record,
                            $data['message'],
                            optional(SmsTemplate::query()->find($data['sms_template_id'] ?? null))->name,
                        );

                        $log->status === 'gonderildi'
                            ? Notification::make()->title('SMS gönderildi')->success()->send()
                            : Notification::make()->title('SMS gönderilemedi')->body($log->error_message)->danger()->send();
                    } catch (Throwable $e) {
                        Notification::make()->title('SMS gönderilemedi')->body($e->getMessage())->danger()->send();
                    }
                }),

            EditAction::make(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function placeholderData(): array
    {
        return [
            'client_name' => $this->record->fullName(),
            'client_phone' => (string) $this->record->phone,
            'client_email' => (string) $this->record->email,
            'case_no' => (string) $this->record->case_no,
            'balance' => number_format($this->record->balance(), 2, ',', '.').' ₺',
            'office_name' => 'KDB Hukuk',
            'lawyer_name' => 'Av. Kaan Durali Bulut',
        ];
    }
}
