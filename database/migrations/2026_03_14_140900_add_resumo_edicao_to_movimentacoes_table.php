<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacoes', 'resumo_edicao')) {
                $table->string('resumo_edicao')->nullable()->after('observacao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'resumo_edicao')) {
                $table->dropColumn('resumo_edicao');
            }
        });
    }
};
