<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('picking_items', function (Blueprint $table) {
            $table->index(['status']);
            $table->index(['produto_id']);
            $table->index(['picking_batch_id']);
        });
        Schema::table('picking_batches', function (Blueprint $table) {
            $table->index(['status']);
            $table->index(['orcamento_id']);
            $table->index(['armazem_id']);
            $table->index(['started_at']);
        });
        Schema::table('estoque_reservas', function (Blueprint $table) {
            $table->index(['produto_id', 'status']);
        });
    }
    public function down(): void {
        // Opcional remover índices
    }
};