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
        Schema::table('inconsistencia_recebimentos', function (Blueprint $table) {
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'pedido_compra_id')) {
                $table->foreignId('pedido_compra_id')->after('id')->constrained('pedido_compras')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'produto_id')) {
                $table->foreignId('produto_id')->after('pedido_compra_id')->constrained('produtos')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'quantidade_esperada')) {
                $table->decimal('quantidade_esperada', 15, 2)->after('produto_id');
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'quantidade_recebida')) {
                $table->decimal('quantidade_recebida', 15, 2)->after('quantidade_esperada');
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'usuario_id')) {
                $table->foreignId('usuario_id')->after('quantidade_recebida')->constrained('users')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'movimentacao_id')) {
                $table->foreignId('movimentacao_id')->after('usuario_id')->constrained('movimentacoes')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('inconsistencia_recebimentos', 'observacao')) {
                $table->text('observacao')->nullable()->after('movimentacao_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inconsistencia_recebimentos', function (Blueprint $table) {
            $table->dropForeign(['pedido_compra_id']);
            $table->dropForeign(['produto_id']);
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['movimentacao_id']);
            $table->dropColumn(['pedido_compra_id', 'produto_id', 'quantidade_esperada', 'quantidade_recebida', 'usuario_id', 'movimentacao_id', 'observacao']);
        });
    }
};
