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
            $table->timestamp('vip_release_at')->nullable()->after('liberado_at');
            $table->boolean('only_members')->default(false)->after('vip_release_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adivinhacoes', function (Blueprint $table) {
            $table->dropColumn(['vip_release_at', 'only_members']);
        });
    }
};
