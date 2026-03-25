<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores instructor-specific profile data (extends user).
     */
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['Medical', 'Legal', 'Business', 'Literary', 'Technical', 'Interpretation'])->default('Business');
            $table->enum('type', ['Paid', 'Free'])->default('Paid');
            $table->decimal('price_per_hour', 8, 2)->nullable()->comment('NULL for free instructors');
            $table->text('bio')->nullable();
            $table->json('specialties')->nullable()->comment('Array of specialty strings');
            $table->string('schedule')->nullable()->comment('e.g. "Mon, Wed, Fri (Morning)"');
            $table->unsignedInteger('years_experience')->default(0);
            $table->unsignedInteger('total_students')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
