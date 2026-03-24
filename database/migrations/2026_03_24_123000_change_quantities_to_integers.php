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
        Schema::table('produtos', function (Blueprint $table) {
            $table->integer('estoque_atual')->change();
            $table->integer('estoque_minimo')->change();
            $table->integer('estoque_web')->nullable()->change();
            $table->integer('stock_reserved')->default(0)->change();
        });

        Schema::table('requisicao_compra_itens', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });

        Schema::table('falta_itens', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });

        Schema::table('order_return_items', function (Blueprint $table) {
            $table->integer('quantity_requested')->change();
            $table->integer('quantity_approved')->nullable()->change();
        });

        Schema::table('estoque_reservas', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });

        Schema::table('conferencia_items', function (Blueprint $table) {
            $table->integer('qty_separada')->change();
            $table->integer('qty_conferida')->change();
            $table->integer('divergencia')->change();
        });

        Schema::table('picking_items', function (Blueprint $table) {
            $table->integer('qty_solicitada')->change();
            $table->integer('qty_separada')->change();
        });

        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            $table->integer('quantidade_solicitada')->change();
            $table->integer('quantidade_recebida')->change();
        });

        Schema::table('pedido_compra_itens', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });

        Schema::table('bloco_k_items', function (Blueprint $table) {
            $table->integer('quantidade')->nullable()->change();
        });

        Schema::table('bloco_k_insumos', function (Blueprint $table) {
            $table->integer('quantidade')->nullable()->change();
        });

        Schema::table('bloco_k_descartes', function (Blueprint $table) {
            $table->integer('quantidade_descarte')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting to exact original decimal scales might be complex without manual values.
        // Assuming original was decimal(15, 3) or similar for most.
        
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('estoque_atual', 15, 2)->change();
            $table->decimal('estoque_minimo', 15, 2)->change();
            $table->decimal('estoque_web', 15, 2)->nullable()->change();
            $table->decimal('stock_reserved', 12, 3)->default(0)->change();
        });

        Schema::table('requisicao_compra_itens', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 3)->change();
        });

        Schema::table('falta_itens', function (Blueprint $table) {
            $table->decimal('quantidade', 10, 3)->change();
        });

        Schema::table('order_return_items', function (Blueprint $table) {
            $table->decimal('quantity_requested', 15, 3)->change();
            $table->decimal('quantity_approved', 15, 3)->nullable()->change();
        });

        Schema::table('estoque_reservas', function (Blueprint $table) {
            $table->decimal('quantidade', 12, 3)->change();
        });

        Schema::table('conferencia_items', function (Blueprint $table) {
            $table->decimal('qty_separada', 12, 3)->change();
            $table->decimal('qty_conferida', 12, 3)->change();
            $table->decimal('divergencia', 12, 3)->change();
        });

        Schema::table('picking_items', function (Blueprint $table) {
            $table->decimal('qty_solicitada', 12, 3)->change();
            $table->decimal('qty_separada', 12, 3)->change();
        });

        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            $table->decimal('quantidade_solicitada', 10, 2)->change();
            $table->decimal('quantidade_recebida', 10, 2)->change();
        });

        Schema::table('pedido_compra_itens', function (Blueprint $table) {
            $table->double('quantidade')->change();
        });

        Schema::table('bloco_k_items', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 2)->nullable()->change();
        });

        Schema::table('bloco_k_insumos', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 2)->nullable()->change();
        });

        Schema::table('bloco_k_descartes', function (Blueprint $table) {
            $table->decimal('quantidade_descarte', 15, 2)->nullable()->change();
        });
    }
};
