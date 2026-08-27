<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SiteSettingsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Setting::getGroup('site'),
        ]);
    }
}
