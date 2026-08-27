<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbox_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->date('date');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('manuel');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbox_transactions');
    }
};
