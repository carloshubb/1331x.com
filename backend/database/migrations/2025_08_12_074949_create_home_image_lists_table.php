<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_image_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Image title
            $table->string('image_url'); // Full image URL
            $table->string('link')->nullable(); // Optional link when clicked
            $table->string('quality')->nullable(); // quality
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true); // Show/hide
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_image_lists');
    }
};
