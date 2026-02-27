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
        Schema::table('consulta_precos', function (Blueprint $table) {
            // Vincula o item ao grupo de cotação
            $table->unsignedBigInteger('grupo_id')->nullable()->after('id')
                ->comment('Grupo de cotação ao qual este item pertence.');
            $table->foreign('grupo_id')->references('id')->on('consulta_preco_grupos');

            // orcamento_id já existe mas aceita null — garante o foreign se ainda não existir
            // $table->foreign('orcamento_id')->references('id')->on('orcamentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consulta_precos', function (Blueprint $table) {
            //
        });
    }
};
