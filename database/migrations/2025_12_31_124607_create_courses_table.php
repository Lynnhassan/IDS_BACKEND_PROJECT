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

        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Id int PK
            $table->string('title', 100);
            $table->string('shortDescription', 150);
            $table->string('longDescription', 100);
            $table->string('category');
            $table->enum('difficulty', ['Easy', 'Medium', 'Hard']);
            $table->string('thumbnail', 100)->nullable();

            $table->foreignId('instructorId')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->boolean('isPublished')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
