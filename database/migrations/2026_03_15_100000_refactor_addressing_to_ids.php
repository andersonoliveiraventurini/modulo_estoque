<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacao_produtos', 'armazem_id')) {
                $table->foreignId('armazem_id')->nullable()->after('produto_id')->constrained('armazens')->nullOnDelete();
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'corredor_id')) {
                $table->foreignId('corredor_id')->nullable()->after('armazem_id')->constrained('corredors')->nullOnDelete();
            }
            if (!Schema::hasColumn('movimentacao_produtos', 'posicao_id')) {
                $table->foreignId('posicao_id')->nullable()->after('corredor_id')->constrained('posicaos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('posicao_id');
            $table->dropConstrainedForeignId('corredor_id');
            $table->dropConstrainedForeignId('armazem_id');
        });
    }
};
