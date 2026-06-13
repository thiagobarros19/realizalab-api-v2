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
        Schema::create('order_exams', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->foreignUlid('order_id')->constrained();
            $table->foreignUlid('exam_id')->constrained();
            $table->string('exam_name');
            $table->decimal('exam_price', 10,2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_exams');
    }
};
