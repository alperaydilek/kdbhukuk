<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_debt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method')->default('nakit');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->boolean('is_postdated')->default(false);
            $table->string('status')->default('tahsil_edildi');
            $table->string('instrument_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('cashbox_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payments');
    }
};
