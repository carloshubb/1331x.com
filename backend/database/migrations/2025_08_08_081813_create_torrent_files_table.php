<?php
// 4. Torrent Files Migration (database/migrations/2024_01_01_000004_create_torrent_files_table.php)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('torrent_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torrent_id');
            $table->string('filename', 500);
            $table->string('path', 1000)->nullable();
            $table->bigInteger('size_bytes');
            $table->string('size_formatted', 20);
            $table->timestamps();

            $table->index('torrent_id');
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrent_files');
    }
};
