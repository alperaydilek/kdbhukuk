<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Service */
class ServiceListResource extends JsonResource
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
            'short_description' => $this->short_description,
            'cover_image' => $this->cover_image ? Storage::disk('r2')->url($this->cover_image) : null,
        ];
    }
}
