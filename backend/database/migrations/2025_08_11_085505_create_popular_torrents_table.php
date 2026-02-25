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
        Schema::create('popular_torrents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategory_id')->nullable(); // Foreign key to torrents table
            $table->String('torrent_link')->nullable(); // Number of views
            $table->String('category_title')->nullable(); // Number of views
            $table->String('name')->nullable(); // Number of views
            $table->integer('comments_count')->nullable(); // Number of comments_count
            $table->integer('seeders')->default(0); // Number of seeds
            $table->integer('leechers')->default(0); // Number of leeches
            $table->String('size_formatted')->nullable(); // size
            $table->timestamp('approved_at')->nullable();
            $table->String('uploader')->nullable(); // uploader
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_torrents');
    }
};
