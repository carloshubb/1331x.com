<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('torrent_screenshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torrent_id');
            $table->string('image_url');
            $table->string('thumbnail_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->index(['torrent_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrent_screenshots');
    }
};