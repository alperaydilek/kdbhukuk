<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function show(string $slug): JsonResponse|PageResource
    {
        $page = Page::query()->firstWhere('slug', $slug);

        if (! $page) {
            return response()->json(['message' => 'Sayfa bulunamadı.'], 404);
        }

        return new PageResource($page);
    }
}
