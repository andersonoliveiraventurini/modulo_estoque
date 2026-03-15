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
        Schema::table('pedido_compras', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido_compras', 'requisicao_compra_id')) {
                $table->foreignId('requisicao_compra_id')->nullable()->after('usuario_id')->constrained('requisicao_compras')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_compras', function (Blueprint $table) {
            $table->dropForeign(['requisicao_compra_id']);
            $table->dropColumn('requisicao_compra_id');
        });
    }
};
