<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks languages each user speaks or is learning, with CEFR level.
     */
    public function up(): void
    {
        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language');          // e.g. "English", "Spanish"
            $table->string('flag')->nullable();  // e.g. "🇬🇧", "🇪🇸"
            $table->string('level', 10);         // CEFR level or "Native"
            $table->boolean('is_native')->default(false);
            $table->timestamps();


            $table->unique(['user_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_languages');
    }
};
