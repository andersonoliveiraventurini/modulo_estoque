<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacao_produtos', 'observacao')) {
                $table->string('observacao')->nullable()->after('posicao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimentacao_produtos', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacao_produtos', 'observacao')) {
                $table->dropColumn('observacao');
            }
        });
    }
};
