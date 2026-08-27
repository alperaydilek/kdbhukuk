<?php

namespace App\Services\Ai;

/**
 * Kabaca güncel liste fiyatları üzerinden maliyet tahmini (USD / 1M token).
 * Gerçek faturalandırma sağlayıcı panelinden takip edilmelidir; burada
 * kullanıcıya "ortalama ne kadar harcayacağı" konusunda fikir vermek
 * amaçlanmıştır.
 */
class AiPricing
{
    /**
     * @var array<string, array{input: float, output: float}>
     */
    protected static array $table = [
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4.1-mini' => ['input' => 0.40, 'output' => 1.60],
        'gpt-4.1' => ['input' => 2.00, 'output' => 8.00],
        'gemini-1.5-flash' => ['input' => 0.075, 'output' => 0.30],
        'gemini-1.5-pro' => ['input' => 1.25, 'output' => 5.00],
        'gemini-2.0-flash' => ['input' => 0.10, 'output' => 0.40],
    ];

    public static function estimateCostUsd(string $model, int $promptTokens, int $completionTokens): float
    {
        $rates = static::$table[$model] ?? ['input' => 0.50, 'output' => 1.50];

        $inputCost = ($promptTokens / 1_000_000) * $rates['input'];
        $outputCost = ($completionTokens / 1_000_000) * $rates['output'];

        return round($inputCost + $outputCost, 4);
    }

    /**
     * @return array<int, string>
     */
    public static function modelsFor(string $provider): array
    {
        return match ($provider) {
            'openai' => ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1'],
            'gemini' => ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            default => [],
        };
    }
}
