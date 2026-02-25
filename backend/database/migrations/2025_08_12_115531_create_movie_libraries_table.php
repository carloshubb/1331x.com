<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieLibrariesTable extends Migration
{
    public function up()
    {
        Schema::create('movie_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('img_url');
            $table->float('rate',3,2);         // If you want float, change to $table->float('rate', 3, 1);
            $table->string('info_url');
            $table->string('info_title');
            $table->string('category');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movie_libraries');
    }
}
