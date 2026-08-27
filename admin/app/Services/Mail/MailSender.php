<?php

namespace App\Services\Mail;

use App\Mail\GenericHtmlMail;
use App\Models\Client;
use App\Models\CommunicationLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class MailSender
{
    /**
     * Ayarlar tablosundaki Google Mail (SMTP) bilgilerini çalışma zamanında
     * Laravel mail konfigürasyonuna uygular.
     */
    public function applyRuntimeConfig(): void
    {
        $settings = Setting::getGroup('mail');

        if (blank($settings['host'] ?? null)) {
            throw new RuntimeException('Mail ayarları tanımlı değil. Lütfen Ayarlar → E-posta bölümünü doldurun.');
        }

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings['host'],
            'port' => $settings['port'] ?? 587,
            'encryption' => $settings['encryption'] ?? 'tls',
            'username' => $settings['username'] ?? null,
            'password' => $settings['app_password'] ?? null,
            'timeout' => null,
        ]);

        Config::set('mail.default', 'smtp');
        Config::set('mail.from', [
            'address' => $settings['from_address'] ?? $settings['username'],
            'name' => $settings['from_name'] ?? 'KDB Hukuk',
        ]);
    }

    public function send(Client $client, string $subject, string $htmlBody, ?string $templateName = null): CommunicationLog
    {
        if (blank($client->email)) {
            throw new RuntimeException('Müvekkilin e-posta adresi kayıtlı değil.');
        }

        try {
            $this->applyRuntimeConfig();

            Mail::to($client->email)->send(new GenericHtmlMail($subject, $htmlBody));

            return CommunicationLog::query()->create([
                'client_id' => $client->id,
                'channel' => 'mail',
                'template_name' => $templateName,
                'to' => $client->email,
                'subject' => $subject,
                'body' => $htmlBody,
                'status' => 'gonderildi',
                'sent_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            return CommunicationLog::query()->create([
                'client_id' => $client->id,
                'channel' => 'mail',
                'template_name' => $templateName,
                'to' => $client->email,
                'subject' => $subject,
                'body' => $htmlBody,
                'status' => 'basarisiz',
                'error_message' => $e->getMessage(),
                'sent_by' => Auth::id(),
            ]);
        }
    }
}
