<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bookings for instructor sessions (complete course or specific session).
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('booking_type', ['complete-course', 'specific-session'])->default('specific-session');
            $table->enum('course_style', ['private', 'group'])->nullable()->comment('Only for complete-course type');
            $table->date('date');
            $table->time('time');
            $table->decimal('price', 8, 2)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
