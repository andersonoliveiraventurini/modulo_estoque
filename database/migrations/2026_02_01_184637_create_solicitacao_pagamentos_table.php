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
        Schema::create('solicitacao_pagamentos', function (Blueprint $table) {
            $table->id();

            // Referência ao orçamento
            $table->unsignedBigInteger('orcamento_id')
                ->comment('Referência ao orçamento associado a esta solicitação.');
            $table->foreign('orcamento_id')->references('id')->on('orcamentos');
            
            // Descrição do meio de pagamento solicitado
            $table->text('descricao_pagamento')
                ->comment('Descrição detalhada do meio de pagamento solicitado.');
            
            // Justificativa da solicitação
            $table->text('justificativa_solicitacao')
                ->comment('Justificativa do vendedor para solicitar este meio de pagamento.');
            
            // Condições propostas
            $table->integer('numero_parcelas')->nullable()
                ->comment('Número de parcelas propostas, se aplicável.');
            
            $table->decimal('valor_entrada', 15, 2)->nullable()
                ->comment('Valor de entrada proposto, se aplicável.');
            
            $table->date('data_primeiro_vencimento')->nullable()
                ->comment('Data do primeiro vencimento, se aplicável.');
            
            $table->integer('intervalo_dias')->nullable()
                ->comment('Intervalo em dias entre parcelas, se aplicável.');
            
            // Quem solicitou
            $table->unsignedBigInteger('solicitado_por')
                ->comment('ID do usuário que solicitou a aprovação.');
            $table->foreign('solicitado_por')->references('id')->on('users');
            
            // Campos de aprovação
            $table->timestamp('aprovado_em')->nullable()
                ->comment('Data e hora em que foi aprovado.');
            
            $table->unsignedBigInteger('aprovado_por')->nullable()
                ->comment('ID do usuário que aprovou.');
            $table->foreign('aprovado_por')->references('id')->on('users');
            
            $table->text('justificativa_aprovacao')->nullable()
                ->comment('Justificativa da aprovação.');
            
            // Campos de rejeição
            $table->timestamp('rejeitado_em')->nullable()
                ->comment('Data e hora em que foi rejeitado.');
            
            $table->unsignedBigInteger('rejeitado_por')->nullable()
                ->comment('ID do usuário que rejeitou.');
            $table->foreign('rejeitado_por')->references('id')->on('users');
            
            $table->text('justificativa_rejeicao')->nullable()
                ->comment('Justificativa da rejeição.');
            
            // Status
            $table->enum('status', ['Pendente', 'Aprovado', 'Rejeitado'])
                ->default('Pendente')
                ->comment('Status da solicitação.');
            
            $table->text('observacoes')->nullable()
                ->comment('Observações adicionais.');
                
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacao_pagamentos');
    }
};
