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
        if (!Schema::hasColumn('movimentacao_produtos', 'data_vencimento')) {
            Schema::table('movimentacao_produtos', function (Blueprint $table) {
                $table->date('data_vencimento')->nullable()->after('quantidade')
                    ->comment('Data de vencimento do lote do produto nesta movimentação.');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('movimentacao_produtos', 'data_vencimento')) {
            Schema::table('movimentacao_produtos', function (Blueprint $table) {
                $table->dropColumn('data_vencimento');
            });
        }
    }
};
