<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conferencias', 'pedido_compra_id')) {
            Schema::table('conferencias', function (Blueprint $row) {
                $row->foreignId('pedido_compra_id')->nullable()->after('picking_batch_id')->constrained('pedido_compras');
            });
        }

        if (!Schema::hasColumn('conferencia_items', 'pedido_compra_item_id')) {
            Schema::table('conferencia_items', function (Blueprint $row) {
                $row->foreignId('pedido_compra_item_id')->nullable()->after('picking_item_id')->constrained('pedido_compra_itens');
            });
        }
    }

    public function down(): void
    {
        Schema::table('conferencias', function (Blueprint $row) {
            if (Schema::hasColumn('conferencias', 'pedido_compra_id')) {
                $row->dropForeign(['pedido_compra_id']);
                $row->dropColumn('pedido_compra_id');
            }
        });

        Schema::table('conferencia_items', function (Blueprint $row) {
            if (Schema::hasColumn('conferencia_items', 'pedido_compra_item_id')) {
                $row->dropForeign(['pedido_compra_item_id']);
                $row->dropColumn('pedido_compra_item_id');
            }
        });
    }
};
