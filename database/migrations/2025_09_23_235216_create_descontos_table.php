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
        Schema::create('descontos', function (Blueprint $table) {
            $table->id();
            $table->string('motivo')
                ->comment('Motivo do desconto, como "Desconto de Verão" ou "Promoção Especial".');
            $table->decimal('valor', 10, 2)
                ->comment('Valor do desconto, que pode ser um valor fixo ou uma porcentagem.');
            $table->decimal('porcentagem', 5, 2)->nullable()
                ->comment('Porcentagem do desconto, se aplicável. Exemplo: 15.00 para 15%.');
            $table->enum('tipo', ['fixo', 'percentual'])
                ->comment('Tipo de desconto: "fixo" para um valor fixo ou "percentual" para uma porcentagem.');

            // qual cliente
            $table->unsignedBigInteger('cliente_id')->nullable()
                ->comment('Referência ao cliente associado ao documento, se aplicável.');
            $table->foreign('cliente_id')->references('id')->on('clientes');

            // orçamento
            $table->unsignedBigInteger('orcamento_id')->nullable()
                ->comment('Referência ao orçamento associado ao desconto, se aplicável.');
            $table->foreign('orcamento_id')->references('id')->on('orcamentos');

            // pedido
            $table->unsignedBigInteger('pedido_id')->nullable()
                ->comment('Referência ao pedido associado ao desconto, se aplicável.');
            $table->foreign('pedido_id')->references('id')->on('pedidos');

            // quem criou
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descontos');
    }
};
