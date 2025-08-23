<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('commentable'); // Pode ser post, foto, vídeo etc.
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('likeable'); // Pode ser post, comentário, foto, etc.
            $table->timestamps();

            $table->unique(['user_id', 'likeable_id', 'likeable_type']); // evitar duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_and_likes_tables');
    }
};
