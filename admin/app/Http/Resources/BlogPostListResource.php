<?php

namespace App\Http\Resources;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin BlogPost */
class BlogPostListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'cover_image' => $this->cover_image ? Storage::disk('r2')->url($this->cover_image) : null,
            'category' => $this->whenLoaded('category', fn () => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'reading_minutes' => $this->reading_minutes,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
