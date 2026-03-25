<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Quiz questions for lessons and final course assessments.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete()->comment('For final assessments');
            $table->enum('type', ['lesson_quiz', 'final_assessment', 'onboarding'])->default('lesson_quiz');
            $table->text('question');
            $table->json('options')->comment('Array of option strings');
            $table->unsignedTinyInteger('correct_answer')->comment('0-based index of correct option');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
