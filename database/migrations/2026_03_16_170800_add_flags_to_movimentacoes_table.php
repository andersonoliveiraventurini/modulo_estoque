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
            $table->boolean('is_reposicao')->default(false)->after('tipo')
                ->comment('Identifica se é uma movimentação de reposição de estoque.');
            $table->boolean('is_devolucao')->default(false)->after('is_reposicao')
                ->comment('Identifica se é uma movimentação de devolução.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropColumn(['is_reposicao', 'is_devolucao']);
        });
    }
};
