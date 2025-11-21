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
            $table->integer('versao')->default(1);

            $table->date('validade')
                ->nullable()
                ->comment('Data de validade do orçamento, geralmente 2 dias após a emissão.');
            $table->enum('status', ['Pendente', 'Aguardando fornecedor', 'Disponível'])->default('Pendente')
                ->comment('Status da cotação de preço.');

            $table->string('descricao')->nullable()
                ->comment('Descrição da classificação do fornecedor.');
            $table->unsignedBigInteger('cor_id')->nullable()
                ->comment('Cor associada à classificação do fornecedor.');
            $table->foreign('cor_id')->references('id')->on('cores');

            $table->string('quantidade')->nullable()
                ->comment('Quantidade');

            // cliente
            $table->unsignedBigInteger('cliente_id')->nullable()
                ->comment('Cliente que solicitou a cotação.');
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuário que solicitou a cotação.');
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->double('preco_compra')->nullable()
                ->comment('Preço cotado pelo fornecedor.');
            $table->double('preco_venda')->nullable()
                ->comment('Preço de venda.');
            $table->string('observacao')->nullable()
                ->comment('Observações adicionais sobre o preço cotado.');

            // fornecedor
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                ->comment('Referência ao fornecedor - para recebimento.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');

            // pessoa da área de compras
            $table->unsignedBigInteger('comprador_id')->nullable()
                ->comment('Usuário que fez o cadastro do preço.');
            $table->foreign('comprador_id')->references('id')->on('users');


            $table->string('prazo_entrega', 100)->nullable()
                ->comment('Prazo de entrega do orçamento.');
            $table->string('pdf_path')->nullable();
            $table->uuid('token_acesso')->unique()->nullable();
            $table->timestamp('token_expira_em')->nullable();

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
