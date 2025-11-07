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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orcamento_id')->nullable()
                ->comment('Referência ao orçamento associado ao pagamento.');
            $table->foreign('orcamento_id')->references('id')->on('orcamentos');

            $table->unsignedBigInteger('pedido_id')->nullable()
                ->comment('Referência ao pedido associado ao pagamento.');
            $table->foreign('pedido_id')->references('id')->on('pedidos');

            $table->unsignedBigInteger('condicao_pagamento_id')
                ->comment('Referência à condição de pagamento utilizada.');
            $table->foreign('condicao_pagamento_id')->references('id')->on('condicoes_pagamento');

            $table->decimal('desconto_balcao', 10, 2)->nullable();
            $table->decimal('desconto_aplicado', 10, 2)->nullable();
            $table->decimal('valor_final', 10, 2)->nullable();
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->decimal('troco', 10, 2)->nullable();

            $table->datetime('data_pagamento')
                ->comment('Data e hora em que o pagamento foi realizado.');

            $table->enum('tipo_documento', ['cupom_fiscal', 'nota_fiscal'])
                ->default('cupom_fiscal')
                ->comment('Tipo de documento fiscal gerado.');

            $table->string('numero_documento')->nullable()
                ->comment('Número do documento fiscal gerado.');

            $table->string('cnpj_cpf_nota')->nullable()
                ->comment('CNPJ/CPF diferente do cliente para emissão da nota fiscal.');

            $table->text('observacoes')->nullable()
                ->comment('Observações sobre o pagamento.');

            $table->unsignedBigInteger('user_id')
                ->comment('Referência ao usuário que registrou o pagamento.');
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
        Schema::dropIfExists('pagamentos');
    }
};
