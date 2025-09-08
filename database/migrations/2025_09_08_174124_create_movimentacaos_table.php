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
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['entrada', 'saida'])
                ->comment('Tipo de movimentação: entrada ou saída.');
            $table->integer('quantidade');
            $table->double('valor_unitario')->nullable()
                ->comment('Valor unitário do produto na movimentação.');
            $table->double('valor_total')->nullable()
                ->comment('Valor total da movimentação (quantidade * valor_unitario).');

            $table->integer('nota_fiscal_fornecedor')->nullable()
                ->comment('Número da nota fiscal associada à movimentação, se aplicável.');

            $table->string('romaneiro')->nullable()
                ->comment('Número do romaneio, se aplicável.');

            // produto a ser movimentado
            $table->unsignedBigInteger('produto_id')->nullable()
                ->comment('Produto movimentado.');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            // fornecedor
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                ->comment('Referência ao fornecedor - para recebimento.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');

            // pedido
            $table->unsignedBigInteger('pedido_id')->nullable()
                ->comment('Referência de pedido - para recebimento.');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');

            // cliente
            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuário que fez a movimentação.');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
