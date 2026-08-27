<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'kvkk_consent' => ['required', 'accepted'],
        ];
    }
}
