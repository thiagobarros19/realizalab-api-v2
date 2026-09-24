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
        Schema::create('exam_result_access_logs', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->foreignUlid('exam_result_id')->constrained('exam_results')->cascadeOnDelete();
            $table->string('actor_type');
            $table->ulid('actor_id');
            $table->string('action');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Append-only audit trail: no updated_at, no soft deletes.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_result_access_logs');
    }
};
