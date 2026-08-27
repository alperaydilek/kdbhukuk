<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LegalPageResource;
use App\Models\LegalPage;
use Illuminate\Http\JsonResponse;

class LegalPageController extends Controller
{
    public function show(string $slug): JsonResponse|LegalPageResource
    {
        $page = LegalPage::query()->firstWhere('slug', $slug);

        if (! $page) {
            return response()->json(['message' => 'Sayfa bulunamadı.'], 404);
        }

        return new LegalPageResource($page);
    }
}
