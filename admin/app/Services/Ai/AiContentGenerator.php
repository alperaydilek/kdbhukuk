<?php

namespace App\Services\Ai;

use App\Models\AiUsageLog;
use App\Models\Setting;
use RuntimeException;

class AiContentGenerator
{
    /**
     * @return array{title: string, excerpt: string, content: string, meta_title: string, meta_description: string}
     */
    public function generateBlogPost(string $topic, ?int $blogPostId = null): array
    {
        $settings = Setting::getGroup('ai');
        $provider = $settings['active_provider'] ?? null;

        if (blank($provider)) {
            throw new RuntimeException('Aktif bir yapay zekâ sağlayıcısı seçilmemiş. Lütfen Ayarlar → Yapay Zekâ bölümünden bir sağlayıcı seçip API anahtarını girin.');
        }

        $model = $settings["{$provider}_model"] ?? null;
        $apiKey = $settings["{$provider}_api_key"] ?? null;

        if (blank($apiKey) || blank($model)) {
            throw new RuntimeException('Seçili sağlayıcı için API anahtarı veya model tanımlı değil.');
        }

        $client = match ($provider) {
            'openai' => new OpenAiProvider($apiKey),
            'gemini' => new GeminiProvider($apiKey),
            default => throw new RuntimeException("Bilinmeyen yapay zekâ sağlayıcısı: {$provider}"),
        };

        $result = $client->generateBlogPost($topic, $model);

        AiUsageLog::query()->create([
            'provider' => $provider,
            'model' => $model,
            'context' => 'blog_generation',
            'blog_post_id' => $blogPostId,
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
            'total_tokens' => $result['total_tokens'],
            'estimated_cost_usd' => AiPricing::estimateCostUsd($model, $result['prompt_tokens'], $result['completion_tokens']),
        ]);

        return [
            'title' => $result['title'],
            'excerpt' => $result['excerpt'],
            'content' => $result['content'],
            'meta_title' => $result['meta_title'],
            'meta_description' => $result['meta_description'],
        ];
    }
}
