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
        Schema::table('adivinhacoes', function (Blueprint $table) {
            $table->string('dica')->after('resolvida')->nullable();
            $table->string('dica_paga')->after('dica')->nullable('N');
            $table->decimal('dica_valor')->after('dica_paga')->nullable();
        });

        Schema::create('dicas_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('adivinhacao_id')->index();
            $table->unsignedBigInteger('pagamento_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes', function (Blueprint $table) {
            //
        });
    }
};
