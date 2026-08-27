<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'order',
        'title',
        'slug',
        'cover_image',
        'short_description',
        'description',
        'covers',
        'scope',
        'process_steps',
        'why_important',
        'meta_title',
        'meta_description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'process_steps' => 'array',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $service): void {
            if (blank($service->slug)) {
                $service->slug = Str::slug($service->title, language: 'tr');
            }
        });
    }

    /**
     * @return HasMany<ServiceFaq, $this>
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('order');
    }
}
