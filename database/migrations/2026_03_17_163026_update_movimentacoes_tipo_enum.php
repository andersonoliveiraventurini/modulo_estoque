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
        // Necessário no MySQL para alterar ENUM mantendo compatibilidade de dados e adicionando novos valores.
        DB::statement("ALTER TABLE movimentacoes MODIFY COLUMN tipo ENUM('entrada', 'saida', 'saida_para_hub', 'entrada_hub', 'saida_hub', 'entrada_estoque', 'saida_estoque', 'reposicao', 'devolucao_hub', 'transferencia') NOT NULL COMMENT 'Tipo de movimentação: entrada ou saída.'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE movimentacoes MODIFY COLUMN tipo ENUM('entrada', 'saida', 'saida_para_hub') NOT NULL COMMENT 'Tipo de movimentação: entrada ou saída.'");
    }
};
