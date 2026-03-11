<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Garante que a tabela consulta_preco_grupos tenha orcamento_id (necessário para show do orçamento).
     */
    public function up(): void
    {
        if (!Schema::hasTable('consulta_preco_grupos')) {
            return;
        }

        Schema::table('consulta_preco_grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('consulta_preco_grupos', 'orcamento_id')) {
                $table->unsignedBigInteger('orcamento_id')->nullable()->after('usuario_id')
                    ->comment('Orçamento gerado a partir deste grupo, quando aprovado.');
                $table->foreign('orcamento_id')->references('id')->on('orcamentos')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('consulta_preco_grupos')) {
            return;
        }

        Schema::table('consulta_preco_grupos', function (Blueprint $table) {
            if (Schema::hasColumn('consulta_preco_grupos', 'orcamento_id')) {
                $table->dropForeign(['orcamento_id']);
                $table->dropColumn('orcamento_id');
            }
        });
    }
};
