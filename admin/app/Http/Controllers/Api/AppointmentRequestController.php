<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequestRequest;
use App\Models\AppointmentRequest;
use Illuminate\Http\JsonResponse;

class AppointmentRequestController extends Controller
{
    public function store(StoreAppointmentRequestRequest $request): JsonResponse
    {
        AppointmentRequest::query()->create($request->validated());

        return response()->json([
            'message' => 'Randevu talebiniz alındı. Sizi en kısa sürede arayacağız.',
        ], 201);
    }
}
