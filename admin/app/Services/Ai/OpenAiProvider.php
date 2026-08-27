<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsBlogPrompt;
use App\Services\Ai\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProvider
{
    use BuildsBlogPrompt;

    public function __construct(protected string $apiKey) {}

    public function generateBlogPost(string $topic, string $model): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($topic)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI isteği başarısız oldu: '.$response->body());
        }

        $json = $response->json();
        $content = $json['choices'][0]['message']['content'] ?? '';
        $parsed = $this->parseJsonResponse($content);

        return [
            ...$parsed,
            'prompt_tokens' => (int) ($json['usage']['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($json['usage']['completion_tokens'] ?? 0),
            'total_tokens' => (int) ($json['usage']['total_tokens'] ?? 0),
        ];
    }
}
