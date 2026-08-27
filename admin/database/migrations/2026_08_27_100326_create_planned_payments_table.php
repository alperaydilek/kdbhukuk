<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planned_payments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('category')->nullable();
            $table->string('status')->default('bekliyor');
            $table->text('description')->nullable();
            $table->foreignId('cashbox_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planned_payments');
    }
};
