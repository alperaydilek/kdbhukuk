<?php

namespace App\Services\Ai\Contracts;

interface AiProvider
{
    /**
     * @return array{title: string, excerpt: string, content: string, meta_title: string, meta_description: string, prompt_tokens: int, completion_tokens: int, total_tokens: int}
     */
    public function generateBlogPost(string $topic, string $model): array;
}
