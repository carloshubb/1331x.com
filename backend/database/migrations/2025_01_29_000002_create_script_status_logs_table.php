<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 16)->comment('loaded|failed');
            $table->string('script_url', 255)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_status_logs');
    }
};
