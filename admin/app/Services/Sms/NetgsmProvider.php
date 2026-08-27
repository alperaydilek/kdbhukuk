<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;

/**
 * Netgsm REST SMS API entegrasyonu.
 *
 * Netgsm hesap kullanıcı adı/şifre ve onaylı bir mesaj başlığı (msgheader)
 * gerektirir. Gerçek kullanımdan önce Netgsm panelinizdeki API erişim
 * bilgilerini ve başlık onayını doğrulayın.
 */
class NetgsmProvider implements SmsProvider
{
    public function __construct(
        protected string $username,
        protected string $password,
        protected string $header,
    ) {}

    public function send(string $toPhone, string $message): array
    {
        $response = Http::timeout(30)->post('https://api.netgsm.com.tr/sms/rest/v2/send', [
            'usercode' => $this->username,
            'password' => $this->password,
            'msgheader' => $this->header,
            'encoding' => 'TR',
            'messages' => [
                ['msg' => $message, 'no' => $this->normalizePhone($toPhone)],
            ],
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
