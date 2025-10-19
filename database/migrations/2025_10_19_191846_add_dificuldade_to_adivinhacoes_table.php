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
            $table->enum('dificuldade', ['muito facil', 'facil', 'média', 'dificil', 'muito dificil'])->default('média');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes', function (Blueprint $table) {
            $table->dropColumn('dificuldade');
        });
    }
};
