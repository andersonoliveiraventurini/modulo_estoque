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
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_encomenda_itens', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('quantidade_recebida')
                    ->comment('Data de vencimento informada na entrada da encomenda.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_encomenda_itens', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }
};
