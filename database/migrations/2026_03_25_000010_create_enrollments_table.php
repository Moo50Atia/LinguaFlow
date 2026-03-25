<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks which user is enrolled in which course, with progress.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_lesson')->default(1)->comment('Current lesson order position');
            $table->unsignedInteger('completed_lessons')->default(0);
            $table->decimal('progress', 5, 2)->default(0.00)->comment('Percentage 0-100');
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
