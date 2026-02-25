<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torrent_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('rating'); // 1-5 stars
            $table->timestamps();

            $table->unique(['torrent_id', 'user_id']);
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};