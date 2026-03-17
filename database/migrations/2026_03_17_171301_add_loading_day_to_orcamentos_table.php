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
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->enum('loading_day', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'express',
                'sedex',
                'carrier'
            ])->nullable()->after('prazo_entrega')->comment('Dia de carregamento para pedidos de rota e modalidades similares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('loading_day');
        });
    }
};
