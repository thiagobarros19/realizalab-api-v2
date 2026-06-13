<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financials', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->decimal('amount', 10, 2);
            $table->date('paid_at')->default(now());
            $table->enum('type', ['in', 'out']);
            $table->enum('category', ['exam', 'food', 'transport', 'supply', 'other'])->default('other');
            $table->enum('payment_method', ['cash', 'pix', 'credit_card', 'debit_card', 'transfer', 'other'])->default('other');
            $table->text('description')->nullable();

            $table->nullableMorphs('financialable');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financials');
    }
};
