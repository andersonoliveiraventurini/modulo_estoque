<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            // Data real da movimentação (retroativos)
            $table->date('data_movimentacao')->nullable()->after('tipo')
                ->comment('Data real da movimentação (permite retroativos).');
            // Upload do arquivo de nota fiscal (PDF/Imagem)
            $table->string('arquivo_nota_fiscal')->nullable()->after('nota_fiscal_fornecedor')
                ->comment('Caminho do arquivo da NF do fornecedor.');
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropColumn(['data_movimentacao', 'arquivo_nota_fiscal']);
        });
    }
};
