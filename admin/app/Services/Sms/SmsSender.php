<?php

namespace App\Services\Sms;

use App\Models\Client;
use App\Models\CommunicationLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SmsSender
{
    public function send(Client $client, string $message, ?string $templateName = null): CommunicationLog
    {
        $settings = Setting::getGroup('sms');
        $provider = $settings['active_provider'] ?? null;

        if (blank($client->phone)) {
            throw new RuntimeException('Müvekkilin telefon numarası kayıtlı değil.');
        }

        if (blank($provider)) {
            throw new RuntimeException('Aktif bir SMS sağlayıcısı seçilmemiş. Lütfen Ayarlar → SMS bölümünden Mutlucell veya Netgsm seçip bilgileri girin.');
        }

        $client_provider = match ($provider) {
            'mutlucell' => new MutlucellProvider(
                $settings['mutlucell_username'] ?? '',
                $settings['mutlucell_password'] ?? '',
                $settings['mutlucell_originator'] ?? '',
            ),
            'netgsm' => new NetgsmProvider(
                $settings['netgsm_username'] ?? '',
                $settings['netgsm_password'] ?? '',
                $settings['netgsm_header'] ?? '',
            ),
            default => throw new RuntimeException("Bilinmeyen SMS sağlayıcısı: {$provider}"),
        };

        try {
            $result = $client_provider->send($client->phone, $message);

            return CommunicationLog::query()->create([
                'client_id' => $client->id,
                'channel' => 'sms',
                'template_name' => $templateName,
                'to' => $client->phone,
                'body' => $message,
                'status' => $result['success'] ? 'gonderildi' : 'basarisiz',
                'error_message' => $result['success'] ? null : $result['response'],
                'sent_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            return CommunicationLog::query()->create([
                'client_id' => $client->id,
                'channel' => 'sms',
                'template_name' => $templateName,
                'to' => $client->phone,
                'body' => $message,
                'status' => 'basarisiz',
                'error_message' => $e->getMessage(),
                'sent_by' => Auth::id(),
            ]);
        }
    }
}
