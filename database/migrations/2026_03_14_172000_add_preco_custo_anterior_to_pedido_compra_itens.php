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
        Schema::table('pedido_compra_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido_compra_itens', 'preco_custo_anterior')) {
                $table->decimal('preco_custo_anterior', 15, 2)->nullable()->after('valor_unitario');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_compra_itens', function (Blueprint $table) {
            $table->dropColumn('preco_custo_anterior');
        });
    }
};
