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
            $table->longText('descricao')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes', function (Blueprint $table) {
            // Caso antes fosse string normal (varchar 255)
            $table->string('descricao', 255)->nullable(false)->change();
        });
    }
};
