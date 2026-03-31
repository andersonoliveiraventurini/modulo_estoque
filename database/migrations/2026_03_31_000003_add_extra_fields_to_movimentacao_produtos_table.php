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
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacao_produtos', 'wt_code')) {
                $table->string('wt_code')->nullable()->after('produto_id');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'cor')) {
                $table->string('cor')->nullable()->after('wt_code');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'codigo_fornecedor')) {
                $table->string('codigo_fornecedor')->nullable()->after('cor');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'quantidade_vendida')) {
                $table->decimal('quantidade_vendida', 12, 3)->default(0)->after('quantidade');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'is_encomenda')) {
                $table->boolean('is_encomenda')->default(false)->after('quantidade_vendida');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'numero_pedido')) {
                $table->string('numero_pedido')->nullable()->after('is_encomenda');
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'vendedor_id')) {
                $table->unsignedBigInteger('vendedor_id')->nullable()->after('numero_pedido');
                $table->foreign('vendedor_id')->references('id')->on('vendedores');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            $table->dropForeign(['vendedor_id']);
            $table->dropColumn([
                'wt_code',
                'cor',
                'codigo_fornecedor',
                'quantidade_vendida',
                'is_encomenda',
                'numero_pedido',
                'vendedor_id',
            ]);
        });
    }
};
