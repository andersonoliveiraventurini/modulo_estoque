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
        Schema::table('picking_items', function (Blueprint $table) {
            // ✅ Permite null para itens de encomenda
            $table->foreignId('produto_id')->nullable()->change();

            // ✅ Referência ao item de cotação
            $table->foreignId('consulta_preco_id')
                ->nullable()
                ->after('orcamento_item_id')
                ->constrained('consulta_precos')
                ->nullOnDelete();

            // ✅ Flag para identificar tipo do item
            $table->boolean('is_encomenda')->default(false)->after('consulta_preco_id');

            // ✅ Descrição livre para itens sem produto cadastrado
            $table->string('descricao_encomenda')->nullable()->after('is_encomenda');
        });
    }

    public function down(): void
    {
        Schema::table('picking_items', function (Blueprint $table) {
            $table->dropForeign(['consulta_preco_id']);
            $table->dropColumn(['consulta_preco_id', 'is_encomenda', 'descricao_encomenda']);
            $table->foreignId('produto_id')->nullable(false)->change();
        });
    }
};
