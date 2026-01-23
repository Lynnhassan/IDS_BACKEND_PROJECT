<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courseId')->constrained('courses')->onDelete('cascade');
            $table->foreignId('userId')->constrained('users')->onDelete('cascade');

            $table->dateTime('generatedDate')->useCurrent();

            // CRITICAL: Make verification code unique!
            $table->string('verificationCode')->unique();

            $table->timestamps();

            // IMPORTANT: Prevent duplicate certificates for same user+course
            $table->unique(['userId', 'courseId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
