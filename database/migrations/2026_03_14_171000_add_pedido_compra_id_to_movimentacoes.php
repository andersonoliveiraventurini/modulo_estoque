<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            // Substitui o campo pedido_id genérico por uma FK específica ao pedido de compra
            $table->foreignId('pedido_compra_id')
                ->nullable()
                ->after('pedido_id')
                ->constrained('pedido_compras')
                ->nullOnDelete()
                ->comment('Vínculo ao Pedido de Compra (opcional na entrada)');
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['pedido_compra_id']);
            $table->dropColumn('pedido_compra_id');
        });
    }
};
