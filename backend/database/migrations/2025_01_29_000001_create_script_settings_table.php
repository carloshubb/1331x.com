<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('external_script')->comment('Identifier, e.g. external_script');
            $table->boolean('is_enabled')->default(true);
            $table->string('script_url', 512)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_settings');
    }
};
