<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('download_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torrent_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('downloaded_at');
            $table->timestamps();

            $table->index(['torrent_id', 'downloaded_at']);
            $table->index(['user_id', 'downloaded_at']);
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('download_history');
    }
};