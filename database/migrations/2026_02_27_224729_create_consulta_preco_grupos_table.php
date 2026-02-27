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
        Schema::create('consulta_preco_grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')
                ->comment('Cliente para quem a cotação está sendo feita.');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->unsignedBigInteger('usuario_id')
                ->comment('Vendedor que criou o grupo de cotação.');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->unsignedBigInteger('orcamento_id')->nullable()
                ->comment('Orçamento gerado a partir deste grupo, quando aprovado.');
            $table->foreign('orcamento_id')->references('id')->on('orcamentos');
            $table->enum('status', [
                'Pendente',           // aguardando compras responder
                'Aguardando fornecedor', // compras enviou para fornecedor
                'Disponível',         // compras preencheu os preços
                'Aprovado',           // vendedor aprovou / cliente aceitou
                'Expirado',           // passou do prazo de validade
                'Cancelado',
            ])->default('Pendente');
            $table->timestamp('validade')->nullable()
                ->comment('Prazo de 48h após o status mudar para Disponível.');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_preco_grupos');
    }
};
