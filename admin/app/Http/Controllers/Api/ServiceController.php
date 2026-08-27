<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceListResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        return ServiceListResource::collection($services);
    }

    public function show(string $slug): JsonResponse|ServiceResource
    {
        $service = Service::query()
            ->where('is_published', true)
            ->with('faqs')
            ->firstWhere('slug', $slug);

        if (! $service) {
            return response()->json(['message' => 'Hizmet bulunamadı.'], 404);
        }

        return new ServiceResource($service);
    }
}
