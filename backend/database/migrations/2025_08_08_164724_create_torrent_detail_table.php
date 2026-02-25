<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torrent_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torrent_id')->constrained('torrents')->onDelete('cascade');

            $table->longText('full_description')->nullable();
            $table->text('language')->nullable();
            $table->date('release_date')->nullable();
            $table->text('genre')->nullable();
            $table->text('quality')->nullable();
            $table->text('format')->nullable();
            $table->text('audio_language')->nullable();
            $table->text('subtitle_language')->nullable();
            $table->text('encoder')->nullable();
            $table->text('source')->nullable();
            $table->decimal('imdb_rating', 3, 1)->nullable();
            $table->text('runtime')->nullable();
            $table->text('director')->nullable();
            $table->text('cast')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('total_files')->nullable();
            $table->longText('nfo_content')->nullable();
            $table->boolean('has_sample')->default(false);
            $table->string('uploader_status', 100)->nullable();           
            $table->string('type', 100)->nullable();                     
            $table->string('lastchecked', 100)->nullable();                    
            $table->string('dateuploaded', 100)->nullable();                    
            $table->string('infohash')->nullable();           
            $table->longText('files')->nullable();
            $table->longText('comments')->nullable();
            $table->longText('trackerlist')->nullable();
            $table->timestamp('detail_scraped_at')->nullable();

            $table->json('screenshots')->nullable();
            $table->String('media_info',255)->nullable();

            // Additional general torrent detail fields
            $table->string('title')->nullable();
            $table->string('size')->nullable();
            $table->integer('seeders')->default(0);
            $table->integer('leechers')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->string('uploader', 255)->nullable();
            $table->text('magnet_link')->nullable();
            $table->string('category', 255)->nullable();
            $table->longText('description')->nullable();
            $table->integer('file_count')->nullable();
            $table->integer('download_count')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torrent_detail');
    }
};
