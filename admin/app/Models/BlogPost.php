<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected $fillable = [
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'cover_image',
        'content',
        'author_name',
        'reading_minutes',
        'status',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'ai_generated',
        'ai_topic_prompt',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'ai_generated' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $post): void {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title, language: 'tr');
            }

            if (blank($post->reading_minutes) && filled($post->content)) {
                $words = str_word_count(strip_tags($post->content));
                $post->reading_minutes = max(1, (int) ceil($words / 180));
            }
        });
    }

    /**
     * @return BelongsTo<BlogCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }
}
