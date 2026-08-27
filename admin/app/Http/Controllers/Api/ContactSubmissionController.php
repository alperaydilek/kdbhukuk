<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;

class ContactSubmissionController extends Controller
{
    public function store(StoreContactSubmissionRequest $request): JsonResponse
    {
        ContactSubmission::query()->create($request->validated());

        return response()->json([
            'message' => 'Mesajınız alındı. En kısa sürede sizinle iletişime geçilecektir.',
        ], 201);
    }
}
