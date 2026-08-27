<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsBlogPrompt;
use App\Services\Ai\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProvider
{
    use BuildsBlogPrompt;

    public function __construct(protected string $apiKey) {}

    public function generateBlogPost(string $topic, string $model): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(90)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $this->userPrompt($topic)]]],
                ],
                'systemInstruction' => [
                    'parts' => [['text' => $this->systemPrompt()]],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini isteği başarısız oldu: '.$response->body());
        }

        $json = $response->json();
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $parsed = $this->parseJsonResponse($text);

        $usage = $json['usageMetadata'] ?? [];

        return [
            ...$parsed,
            'prompt_tokens' => (int) ($usage['promptTokenCount'] ?? 0),
            'completion_tokens' => (int) ($usage['candidatesTokenCount'] ?? 0),
            'total_tokens' => (int) ($usage['totalTokenCount'] ?? 0),
        ];
    }
}
