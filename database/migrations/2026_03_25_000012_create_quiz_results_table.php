<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores quiz attempt results for progress tracking.
     */
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quiz_title');
            $table->string('course_name')->nullable();
            $table->unsignedTinyInteger('score')->comment('Score percentage 0-100');
            $table->unsignedSmallInteger('total_questions');
            $table->boolean('passed')->default(false);
            $table->enum('type', ['lesson_quiz', 'final_assessment', 'onboarding'])->default('lesson_quiz');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
