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
        Schema::create('competitivo_perguntas', function (Blueprint $table) {
            $table->id();
            $table->text('pergunta');
            $table->string('resposta');
            $table->string('arquivo')->nullable();
            $table->tinyInteger('dificuldade')->comment('1 a 10');
            $table->timestamps();
        });

        Schema::create('competitivo_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained('competitivo_perguntas');
            $table->string('resposta');
            $table->boolean('correta');
            $table->timestamps();
        });

        Schema::create('competitivo_partidas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->tinyInteger('status')->default(0)->comment('0=aguardando, 1=em andamento, 2=finalizada');
            $table->integer('round_atual')->default(1);
            $table->integer('tempo_atual')->default(100);
            $table->tinyInteger('dificuldade_atual')->default(1);
            $table->timestamps();
        });

        Schema::create('competitivo_fila', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('elo')->comment('ELO do jogador no momento que entrou na fila');
            $table->tinyInteger('status')->default(0)->comment('0=esperando, 1=match encontrado, 2=cancelado');
            $table->timestamps();
        });

        Schema::create('competitivo_partidas_jogadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('competitivo_partidas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('vencedor')->nullable();
            $table->integer('round_eliminado')->nullable();
            $table->timestamps();
        });

        Schema::create('competitivo_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('elo')->default(0);
            $table->integer('vitorias')->default(0);
            $table->integer('derrotas')->default(0);
            $table->integer('maior_streak')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
