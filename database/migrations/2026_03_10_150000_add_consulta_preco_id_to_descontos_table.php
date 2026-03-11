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
        Schema::table('descontos', function (Blueprint $table) {
            if (!Schema::hasColumn('descontos', 'consulta_preco_id')) {
                $table->unsignedBigInteger('consulta_preco_id')->nullable()->after('produto_id')
                    ->comment('Referência ao item da encomenda (consulta_precos) quando o desconto é específico do item.');
                $table->foreign('consulta_preco_id')->references('id')->on('consulta_precos')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descontos', function (Blueprint $table) {
            if (Schema::hasColumn('descontos', 'consulta_preco_id')) {
                $table->dropForeign(['consulta_preco_id']);
                $table->dropColumn('consulta_preco_id');
            }
        });
    }
};
