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
        Schema::table('adivinhacoes_premiacoes', function (Blueprint $table) {
            $table->date('previsao_envio_premio')->nullable();
            $table->string('vencedor_notificado', 2)->default('N');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
