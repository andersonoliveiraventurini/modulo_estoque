<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consulta_preco_fornecedores', function (Blueprint $table) {
            if (!Schema::hasColumn('consulta_preco_fornecedores', 'comprador_id')) {
                $table->unsignedBigInteger('comprador_id')
                    ->nullable()
                    ->after('selecionado')
                    ->comment('Usuário da área de compras que preencheu os preços deste fornecedor.');
                $table->foreign('comprador_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('consulta_preco_fornecedores', function (Blueprint $table) {
            if (Schema::hasColumn('consulta_preco_fornecedores', 'comprador_id')) {
                $table->dropForeign(['comprador_id']);
                $table->dropColumn('comprador_id');
            }
        });
    }
};