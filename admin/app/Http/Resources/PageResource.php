<?php

namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 *
 * Not: alan kasıtlı olarak "fields" adlandırıldı — "data" adı, Laravel'in
 * JsonResource sarmalama anahtarıyla ("data") çakışıp otomatik sarmalamayı
 * sessizce devre dışı bırakıyordu.
 */
class PageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'fields' => $this->data ?? [],
        ];
    }
}
