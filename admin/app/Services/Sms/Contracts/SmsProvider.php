<?php

namespace App\Services\Sms\Contracts;

interface SmsProvider
{
    /**
     * @return array{success: bool, response: string}
     */
    public function send(string $toPhone, string $message): array;
}
