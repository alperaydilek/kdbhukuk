<?php

namespace App\Models;

use Database\Factories\MailTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    /** @use HasFactory<MailTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'description',
    ];

    /**
     * Şablon içindeki {{placeholder}} alanlarını verilen verilerle doldurur.
     *
     * @param  array<string, string>  $data
     * @return array{subject: string, body: string}
     */
    public function render(array $data): array
    {
        $replace = function (string $text) use ($data): string {
            foreach ($data as $key => $value) {
                $text = str_replace('{{'.$key.'}}', $value, $text);
            }

            return $text;
        };

        return [
            'subject' => $replace($this->subject),
            'body' => $replace($this->body),
        ];
    }
}
