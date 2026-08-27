<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order' => $this->order,
            'title' => $this->title,
            'slug' => $this->slug,
            'cover_image' => $this->cover_image ? Storage::disk('r2')->url($this->cover_image) : null,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'covers' => $this->covers,
            'scope' => $this->scope,
            'process_steps' => $this->process_steps ?? [],
            'why_important' => $this->why_important,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'faqs' => $this->whenLoaded('faqs', fn () => $this->faqs->map(fn ($faq) => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ])),
        ];
    }
}
