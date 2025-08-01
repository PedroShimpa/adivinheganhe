<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suporte_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        DB::table('suporte_categorias')->insert([
            [
                'descricao' => 'Fale conosco',
                'created_at' => now(),
            ],
            [
                'descricao' => 'Suporte',
                'created_at' => now(),
            ],
        ]);

        Schema::create('suporte', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('categoria_id');
            $table->text('descricao');
            $table->string('status', 2)->default('A')->comment('A - Aguardando, EA - em atendimento,  F - Finalizado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suporte');
    }
};
