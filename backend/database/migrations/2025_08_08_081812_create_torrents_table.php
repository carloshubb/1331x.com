<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('torrents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500);
            $table->string('slug', 500)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('uploader_id')->nullable();
            $table->string('uploader', 255)->nullable();// SHA1 hash
            $table->string('info_hash', 40)->nullable();// SHA1 hash
            $table->text('magnet_link')->nullable();
            $table->string('torrent_file_path')->nullable();
            $table->bigInteger('size_bytes')->default(0);
            $table->string('size_formatted', 20)->nullable(); // e.g., "1.5 GB"
            $table->integer('files_count')->default(1);
            $table->integer('seeders')->default(0);
            $table->integer('leechers')->default(0);
            $table->integer('completed_downloads')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00); // 0.00 to 5.00
            $table->integer('ratings_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_trusted')->default(false);
            $table->json('tags')->nullable(); // Additional tags
            $table->string('imdb_id', 20)->nullable(); // For movies
            $table->string('tmdb_id', 20)->nullable(); // For movies/TV
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['category_id', 'status', 'approved_at']);
            $table->index(['seeders', 'leechers']);
            $table->index(['is_featured', 'approved_at']);
            $table->index(['status', 'created_at']);
            $table->index('info_hash');
            $table->fullText(['name', 'description']);

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrents');
    }
};
