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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('courseId');
            $table->integer('rating')->default(5); // 1-5 stars
            $table->text('comment')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('courseId')->references('id')->on('courses')->onDelete('cascade');

            // Prevent duplicate reviews - one review per user per course
            $table->unique(['userId', 'courseId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
