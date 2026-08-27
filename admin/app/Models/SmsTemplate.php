<?php

namespace App\Models;

use Database\Factories\SmsTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    /** @use HasFactory<SmsTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'body',
        'description',
    ];

    /**
     * @param  array<string, string>  $data
     */
    public function render(array $data): string
    {
        $text = $this->body;

        foreach ($data as $key => $value) {
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        return $text;
    }
}
