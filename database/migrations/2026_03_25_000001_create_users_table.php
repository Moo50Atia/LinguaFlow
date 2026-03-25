<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Craeteing Laravel users table with LinguaFlow-specific fields.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('location')->nullable();
            $table->string('native_language')->nullable();
            $table->string('cefr_level', 10)->default('A1.1')->comment('A1.1, A1.2, A2, B1.1, B1.2, B2, C1, C2');
            $table->enum('role', ['student', 'instructor', 'admin'])->default('student');
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_online')->default(false);
            $table->string('google_id')->nullable()->unique();
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'bio',
                'gender',
                'date_of_birth',
                'location',
                'native_language',
                'cefr_level',
                'role',
                'is_vip',
                'is_online',
                'google_id',
                'last_seen_at',
            ]);
        });
    }
};
