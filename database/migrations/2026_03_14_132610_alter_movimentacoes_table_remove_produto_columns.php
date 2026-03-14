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
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'produto_id')) {
                $table->dropForeign(['produto_id']);
                $table->dropColumn(['produto_id', 'quantidade', 'valor_unitario', 'valor_total']);
            }
            if (Schema::hasColumn('movimentacoes', 'fornecedor_id')) {
                $table->dropForeign(['fornecedor_id']);
                $table->dropColumn('fornecedor_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacoes', 'quantidade')) {
                $table->integer('quantidade')->default(0);
                $table->double('valor_unitario')->nullable()->comment('Valor unitário do produto na movimentação.');
                $table->double('valor_total')->nullable()->comment('Valor total da movimentação (quantidade * valor_unitario).');
            }
            
            if (!Schema::hasColumn('movimentacoes', 'produto_id')) {
                $table->unsignedBigInteger('produto_id')->nullable()->comment('Produto movimentado.');
                $table->foreign('produto_id')->references('id')->on('produtos');
            }

            if (!Schema::hasColumn('movimentacoes', 'fornecedor_id')) {
                $table->unsignedBigInteger('fornecedor_id')->nullable()->comment('Referência ao fornecedor - para recebimento.');
                $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
            }
        });
    }
};
