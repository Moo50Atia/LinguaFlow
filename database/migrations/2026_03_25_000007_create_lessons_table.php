<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Individual lessons within a course, ordered sequentially.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->comment('Sequential position in the course');
            $table->string('title');
            $table->string('duration')->nullable()->comment('e.g. "45 min"');
            $table->text('description')->nullable();
            $table->text('notes')->nullable()->comment('Instructor notes and key phrases');
            $table->string('image')->nullable();
            $table->boolean('has_quiz')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
