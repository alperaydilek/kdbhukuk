<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
