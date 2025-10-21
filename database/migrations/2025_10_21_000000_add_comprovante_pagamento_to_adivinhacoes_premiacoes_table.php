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
            $table->string('comprovante_pagamento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes_premiacoes', function (Blueprint $table) {
            $table->dropColumn('comprovante_pagamento');
        });
    }
};
