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
        Schema::table('produtos', function (Blueprint $table) {
            $table->unique('sku');
            $table->unique('part_number');
            $table->unique(['nome', 'fornecedor_id', 'cor_id'], 'produtos_nome_fornecedor_cor_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropUnique('produtos_sku_unique');
            $table->dropUnique('produtos_part_number_unique');
            $table->dropUnique('produtos_nome_fornecedor_cor_unique');
        });
    }
};
