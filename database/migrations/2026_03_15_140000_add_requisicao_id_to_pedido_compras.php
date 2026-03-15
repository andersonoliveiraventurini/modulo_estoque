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
            $table->unsignedBigInteger('requisicao_compra_id')->nullable()->after('usuario_id');
            $table->foreign('requisicao_compra_id')->references('id')->on('requisicao_compras')->onDelete('set null');
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
