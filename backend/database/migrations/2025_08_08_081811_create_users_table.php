<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->enum('role', ['admin', 'moderator', 'vip', 'user'])->default('user');
            $table->enum('status', ['active', 'banned', 'suspended'])->default('active');
            $table->timestamp('last_seen_at')->nullable();
            $table->json('preferences')->nullable(); // Theme, language, etc.
            $table->bigInteger('upload_count')->default(0);
            $table->bigInteger('download_count')->default(0);
            $table->decimal('ratio', 10, 2)->default(0.00);
            $table->bigInteger('total_uploaded')->default(0); // in bytes
            $table->bigInteger('total_downloaded')->default(0); // in bytes
            $table->timestamp('banned_until')->nullable();
            $table->text('ban_reason')->nullable();
            $table->string('rank')->nullable();
            $table->string('privacy')->nullable();
            $table->string('gender')->nullable();
            $table->string('country')->nullable();
            $table->timestamp('birthday')->nullable();
            $table->timestamp('joindate')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['status', 'role']);
            $table->index('last_seen_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};