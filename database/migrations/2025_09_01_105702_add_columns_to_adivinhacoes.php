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
            $table->boolean('notificar_whatsapp')->default(0);
            $table->boolean('notificar_email')->default(0);
            $table->dateTime('notificado_email_em')->nullable();
            $table->dateTime('notificado_whatsapp_em')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
