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
        Schema::create('adicionais_indicacao_adivinhe_o_milhao', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid')->index();
            $table->integer('value')->comment('Este valor sobe e desce conforme são ultilizados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adicionais_indicacao_adivinhe_o_milhao');
    }
};
