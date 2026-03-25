<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Podcast episodes for the language learning audio library.
     */
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('audio_url');
            $table->string('cover_image')->nullable();
            $table->string('duration')->nullable()->comment('e.g. "25 min"');
            $table->string('category')->nullable()->comment('e.g. "Legal", "Medical", "Business"');
            $table->string('language')->default('English');
            $table->string('level', 10)->nullable()->comment('CEFR level');
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('plays_count')->default(0);
            $table->boolean('is_premium')->default(false)->comment('VIP-only content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
