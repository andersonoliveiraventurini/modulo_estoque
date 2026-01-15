<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona campos de embalagem na tabela picking_batches
        Schema::table('picking_batches', function (Blueprint $table) {
            $table->integer('qtd_caixas')->nullable()->after('observacoes');
            $table->integer('qtd_sacos')->nullable()->after('qtd_caixas');
            $table->integer('qtd_sacolas')->nullable()->after('qtd_sacos');
            $table->string('outros_embalagem')->nullable()->after('qtd_sacolas');
        });

        // Adiciona campos de embalagem na tabela conferencias
        Schema::table('conferencias', function (Blueprint $table) {
            $table->integer('qtd_caixas')->nullable()->after('observacoes');
            $table->integer('qtd_sacos')->nullable()->after('qtd_caixas');
            $table->integer('qtd_sacolas')->nullable()->after('qtd_sacos');
            $table->string('outros_embalagem')->nullable()->after('qtd_sacolas');
        });
    }

    public function down(): void
    {
        Schema::table('picking_batches', function (Blueprint $table) {
            $table->dropColumn(['qtd_caixas', 'qtd_sacos', 'qtd_sacolas', 'outros_embalagem']);
        });

        Schema::table('conferencias', function (Blueprint $table) {
            $table->dropColumn(['qtd_caixas', 'qtd_sacos', 'qtd_sacolas', 'outros_embalagem']);
        });
    }
};