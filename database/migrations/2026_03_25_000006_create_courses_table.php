<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Courses offered by instructors (e.g. "Everyday English A2", "Business English B1").
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('level', 10)->comment('CEFR level: A1, A2, B1, etc.');
            $table->string('language')->default('English');
            $table->string('language_flag')->nullable()->comment('e.g. 🇬🇧');
            $table->unsignedInteger('total_lessons')->default(0);
            $table->string('price')->nullable()->comment('e.g. "$120" or "Free"');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->enum('category', ['Medical', 'Legal', 'Business', 'Literary', 'Technical', 'Interpretation', 'General'])->default('General');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('enrolled_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
