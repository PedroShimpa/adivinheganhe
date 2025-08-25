<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('perfil_privado')->default('N');
            $table->text('bio')->nullable();
            $table->string('image')->nullable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('content')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {}
};
