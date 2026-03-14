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
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacoes', 'nota_fiscal_fornecedor')) {
                $table->string('nota_fiscal_fornecedor')->nullable()->after('pedido_id');
            }
            if (!Schema::hasColumn('movimentacoes', 'romaneiro')) {
                $table->string('romaneiro')->nullable()->after('nota_fiscal_fornecedor');
            }
            if (!Schema::hasColumn('movimentacoes', 'observacao')) {
                $table->text('observacao')->nullable()->after('romaneiro');
            }

            if (!Schema::hasColumn('movimentacoes', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->foreign('usuario_id')->references('id')->on('users');
            }

            if (!Schema::hasColumn('movimentacoes', 'usuario_editou_id')) {
                $table->unsignedBigInteger('usuario_editou_id')->nullable();
                $table->foreign('usuario_editou_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'usuario_editou_id')) {
                $table->dropForeign(['usuario_editou_id']);
                $table->dropColumn('usuario_editou_id');
            }

            // Apenas para limpeza se o 134900 for revertido de forma solitária
            $colunasExtras = ['nota_fiscal_fornecedor', 'romaneiro', 'observacao'];
            foreach($colunasExtras as $col) {
                if (Schema::hasColumn('movimentacoes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
