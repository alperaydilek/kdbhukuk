<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BlogCategoryResource::collection(BlogCategory::query()->orderBy('name')->get());
    }
}
