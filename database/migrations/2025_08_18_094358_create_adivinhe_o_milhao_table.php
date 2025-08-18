<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adivinhe_o_milhao_perguntas', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->text('arquivo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('resposta');
            $table->timestamps();
        });

        Schema::create('adivinhe_o_milhao_inicio_jogo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('respostas_corretas')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('adivinhe_o_milhao_respostas', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('resposta');
            $table->unsignedBigInteger('pergunta_id')->index();
            $table->boolean('correta')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('adivinhe_o_milhao');
    }
};
