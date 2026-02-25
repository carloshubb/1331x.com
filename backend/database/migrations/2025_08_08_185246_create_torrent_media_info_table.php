<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('torrent_media_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torrent_id');
            $table->string('info_type', 50); // video, audio, subtitle
            $table->string('codec', 50)->nullable();
            $table->string('resolution', 20)->nullable(); // 1920x1080
            $table->integer('bitrate')->nullable();
            $table->string('frame_rate', 10)->nullable();
            $table->string('aspect_ratio', 10)->nullable();
            $table->string('color_space', 20)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('duration', 20)->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();

            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->index(['torrent_id', 'info_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrent_media_info');
    }
};