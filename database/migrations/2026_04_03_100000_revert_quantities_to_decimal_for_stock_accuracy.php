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
            $table->decimal('estoque_atual', 15, 3)->change();
            $table->decimal('estoque_minimo', 15, 3)->change();
            $table->decimal('stock_reserved', 15, 3)->default(0)->change();
        });

        Schema::table('conferencia_items', function (Blueprint $table) {
            $table->decimal('qty_separada', 15, 3)->change();
            $table->decimal('qty_conferida', 15, 3)->change();
            $table->decimal('divergencia', 15, 3)->change();
        });

        Schema::table('picking_items', function (Blueprint $table) {
            $table->decimal('qty_solicitada', 15, 3)->change();
            $table->decimal('qty_separada', 15, 3)->change();
        });

        Schema::table('orcamento_itens', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->integer('estoque_atual')->change();
            $table->integer('estoque_minimo')->change();
            $table->integer('stock_reserved')->default(0)->change();
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

        Schema::table('orcamento_itens', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });
    }
};
