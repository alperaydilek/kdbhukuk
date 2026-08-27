<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public static function getGroup(string $group): array
    {
        return static::query()->firstWhere('group', $group)?->payload ?? [];
    }

    public static function putGroup(string $group, array $payload): self
    {
        return tap(
            static::query()->firstOrNew(['group' => $group]),
            function (self $setting) use ($payload): void {
                $setting->payload = $payload;
                $setting->save();
            }
        );
    }
}
