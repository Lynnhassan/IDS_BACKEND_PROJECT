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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            // Add this field to match your diagram:
            $table->enum('contentType', ['Video', 'Article', 'Quiz']);
            $table->string('videoUrl', 100);
            $table->double('estimatedDuration');
            $table->integer('order');
            $table->foreignId('courseId')
                ->constrained('courses')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
