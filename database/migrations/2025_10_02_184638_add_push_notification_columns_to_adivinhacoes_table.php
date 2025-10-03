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
            $table->boolean('notificar_push')->default(false);
            $table->timestamp('notificado_push_em')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes', function (Blueprint $table) {
            $table->dropColumn(['notificar_push', 'notificado_push_em']);
        });
    }
};
