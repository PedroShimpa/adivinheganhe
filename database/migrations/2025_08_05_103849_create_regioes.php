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
        Schema::create('regioes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug_url');
            $table->text('descricao')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('adivinhacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('regiao_id')->nullable()->index()->after('resolvida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regioes');
    }
};
