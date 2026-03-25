<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Social feed posts ("Moments") with community corrections.
     */
    public function up(): void
    {
        Schema::create('moments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->enum('category', [
                'General', 'Grammar', 'Vocabulary', 'Culture',
                'Pronunciation', 'Questions', 'Advice', 'Daily Life'
            ])->default('General');
            $table->json('images')->nullable()->comment('Array of image URLs');
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();
        });

        Schema::create('moment_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('original_text');
            $table->text('corrected_text');
            $table->timestamps();
        });

        Schema::create('moment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['moment_id', 'user_id']);
        });

        Schema::create('moment_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moment_comments');
        Schema::dropIfExists('moment_likes');
        Schema::dropIfExists('moment_corrections');
        Schema::dropIfExists('moments');
    }
};
