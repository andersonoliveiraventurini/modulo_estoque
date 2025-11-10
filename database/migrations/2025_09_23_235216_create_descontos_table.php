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

            // Campos de aprovação
            $table->timestamp('aprovado_em')->nullable()
                ->comment('Data e hora em que o desconto foi aprovado.');

            $table->unsignedBigInteger('aprovado_por')->nullable()
                ->comment('ID do usuário que aprovou o desconto.');
            $table->foreign('aprovado_por')->references('id')->on('users');

            $table->text('justificativa_aprovacao')->nullable()
                ->comment('Justificativa para a aprovação do desconto.');

            // Campos de rejeição
            $table->timestamp('rejeitado_em')->nullable()
                ->comment('Data e hora em que o desconto foi rejeitado.');

            $table->unsignedBigInteger('rejeitado_por')->nullable()
                ->comment('ID do usuário que rejeitou o desconto.');
            $table->foreign('rejeitado_por')->references('id')->on('users');

            $table->text('justificativa_rejeicao')->nullable()
                ->comment('Justificativa para a rejeição do desconto.');

            // Campo adicional para observações gerais
            $table->text('observacao')->nullable()
                ->comment('Observações adicionais sobre o desconto.');

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
