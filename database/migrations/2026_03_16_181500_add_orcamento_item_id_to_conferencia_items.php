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
        Schema::table('conferencia_items', function (Blueprint $table) {
            $table->unsignedBigInteger('orcamento_item_id')->nullable()->after('picking_item_id');
            
            // Opcional: Adicionar chave estrangeira se a tabela orcamento_itens existir e for compatível
            // $table->foreign('orcamento_item_id')->references('id')->on('orcamento_itens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferencia_items', function (Blueprint $table) {
            $table->dropColumn('orcamento_item_id');
        });
    }
};
