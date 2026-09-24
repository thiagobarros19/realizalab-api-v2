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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->foreignUlid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained();
            $table->foreignUlid('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('file_disk')->default('s3');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64);

            $table->timestamp('released_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
