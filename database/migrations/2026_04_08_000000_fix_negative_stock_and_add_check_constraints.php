<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Corrige registros negativos antes de adicionar as constraints
        DB::statement('UPDATE produtos SET estoque_atual = 0 WHERE estoque_atual < 0');
        DB::statement('UPDATE produtos SET estoque_web   = 0 WHERE estoque_web   < 0');

        // 2. Adiciona CHECK constraints
        DB::statement('
            ALTER TABLE produtos
                ADD CONSTRAINT chk_estoque_atual_nao_negativo
                    CHECK (estoque_atual IS NULL OR estoque_atual >= 0),
                ADD CONSTRAINT chk_estoque_web_nao_negativo
                    CHECK (estoque_web IS NULL OR estoque_web >= 0)
        ');
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE produtos
                DROP CHECK chk_estoque_atual_nao_negativo,
                DROP CHECK chk_estoque_web_nao_negativo
        ');
    }
};