<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores instructor availability slots for the booking calendar.
     */
    public function up(): void
    {
        Schema::create('instructor_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->boolean('is_booked')->default(false);
            $table->timestamps();

            $table->unique(['instructor_id', 'date', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_slots');
    }
};
