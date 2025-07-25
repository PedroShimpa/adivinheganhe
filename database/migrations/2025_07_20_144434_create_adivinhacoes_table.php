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
        Schema::create('adivinhacoes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('titulo');
            $table->text('imagem');
            $table->text('descricao');
            $table->longText('premio');
            $table->string('resposta');
            $table->string('resolvida')->default('N')->index();
            $table->timestamps();
        });

        Schema::create('adivinhacoes_respostas', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('adivinhacao_id')->index();
            $table->string('resposta');
            $table->timestamps();
        });

        Schema::create('adivinhacoes_premiacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('adivinhacao_id')->index();
            $table->string('premio_enviado')->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adivinhacoes');
    }
};
