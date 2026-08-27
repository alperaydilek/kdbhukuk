<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'provider',
        'model',
        'context',
        'blog_post_id',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost_usd',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost_usd' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<BlogPost, $this>
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
