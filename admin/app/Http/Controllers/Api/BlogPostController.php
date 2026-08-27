<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostListResource;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogPostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = BlogPost::query()
            ->where('status', 'yayinda')
            ->with('category')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 12));

        return BlogPostListResource::collection($posts);
    }

    public function show(string $slug): JsonResponse|BlogPostResource
    {
        $post = BlogPost::query()
            ->where('status', 'yayinda')
            ->with('category')
            ->firstWhere('slug', $slug);

        if (! $post) {
            return response()->json(['message' => 'Yazı bulunamadı.'], 404);
        }

        return new BlogPostResource($post);
    }
}
