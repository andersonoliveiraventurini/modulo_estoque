<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migration é segura para rodar mesmo se as tabelas já existirem.
// Ela só adiciona colunas que possam estar faltando.

return new class extends Migration
{
    public function up(): void
    {
        // ── entrada_encomendas ───────────────────────────────────────────────
        // Garante que todas as colunas esperadas existem
        Schema::table('entrada_encomendas', function (Blueprint $table) {
            // Adiciona cliente_id se não existir
            if (!Schema::hasColumn('entrada_encomendas', 'cliente_id')) {
                $table->unsignedBigInteger('cliente_id')
                    ->nullable()
                    ->after('entregue_para')
                    ->comment('Cliente da cotação de origem.');
                $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            }

            // Adiciona data_entrega se não existir
            if (!Schema::hasColumn('entrada_encomendas', 'data_entrega')) {
                $table->date('data_entrega')->nullable()->after('data_recebimento');
            }
        });

        // ── entrada_encomenda_itens ──────────────────────────────────────────
        // A tabela já existe — apenas garante softDeletes não estão quebrando nada
        // (não usamos softDeletes nessa tabela, então não há nada a corrigir)
    }

    public function down(): void
    {
        Schema::table('entrada_encomendas', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_encomendas', 'cliente_id')) {
                $table->dropForeign(['cliente_id']);
                $table->dropColumn('cliente_id');
            }
            if (Schema::hasColumn('entrada_encomendas', 'data_entrega')) {
                $table->dropColumn('data_entrega');
            }
        });
    }
};