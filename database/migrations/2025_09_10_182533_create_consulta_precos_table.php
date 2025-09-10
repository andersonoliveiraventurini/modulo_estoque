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
        Schema::create('consulta_precos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao')->nullable()
                ->comment('Descrição da classificação do fornecedor.');
            $table->string('cor')->nullable()
                ->comment('Cor associada à classificação do fornecedor.');

            // cliente
            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuário que solicitou a cotação.');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('orcamento_id')->nullable()
                ->comment('orçamento a que está vinculado.');

            $table->double('preco')->nullable()
                ->comment('Preço cotado pelo fornecedor.');
            $table->double('preco_venda')->nullable()
                ->comment('Preço de venda.');
            $table->string('observacao')->nullable()
                ->comment('Observações adicionais sobre o preço cotado.');

            // fornecedor
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                ->comment('Referência ao fornecedor - para recebimento.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');

            // cliente
            $table->unsignedBigInteger('responsavel_id')->nullable()
                ->comment('Usuário que fez o cadastro do preço.');
            $table->foreign('responsavel_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_precos');
    }
};
