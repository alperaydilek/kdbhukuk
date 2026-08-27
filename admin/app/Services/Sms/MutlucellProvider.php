<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;

/**
 * Mutlucell HTTP SMS API entegrasyonu.
 *
 * Mutlucell, kullanıcı adı/şifre ve gönderici başlığı (originator) ile
 * çalışan bir HTTP tabanlı API sağlar. Gerçek üretim kullanımından önce
 * Mutlucell panelinizdeki güncel endpoint ve parametre adlarını
 * (bayi hesabınıza özel olabilir) doğrulayın.
 */
class MutlucellProvider implements SmsProvider
{
    public function __construct(
        protected string $username,
        protected string $password,
        protected string $originator,
    ) {}

    public function send(string $toPhone, string $message): array
    {
        $response = Http::asForm()->timeout(30)->post('https://smsgw.mutlucell.com/smsgw-ws/sndblksms', [
            'username' => $this->username,
            'password' => $this->password,
            'originator' => $this->originator,
            'message' => $message,
            'gsm' => $this->normalizePhone($toPhone),
        ]);

        return [
            'success' => $response->successful(),
            'response' => $response->body(),
        ];
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? $phone;

        return ltrim((string) $digits, '0');
    }
}
