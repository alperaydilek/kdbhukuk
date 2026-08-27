<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order')->default(0);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('covers')->nullable();
            $table->longText('scope')->nullable();
            $table->json('process_steps')->nullable();
            $table->longText('why_important')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
