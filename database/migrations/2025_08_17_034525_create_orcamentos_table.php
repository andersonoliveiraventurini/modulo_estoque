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
            Schema::create('orcamentos', function (Blueprint $table) {
                  $table->id();
                  $table->integer('versao')->default(1);
                  $table->enum('complemento', ['Não', 'Sim'])->default('Não')
                        ->comment('Complemento para outro orçamento relacionado.');
                  $table->date('validade')
                        ->nullable()
                        ->comment('Data de validade do orçamento, geralmente 2 dias após a emissão.');

                  $table->unsignedBigInteger('condicao_id')->nullable()
                        ->comment('Referência à condição de pagamento associada a este orçamento.');
                  $table->foreign('condicao_id')->references('id')->on('condicoes_pagamento');

                  $table->text('outros_meios_pagamento')->nullable()
                        ->comment('Descrição de outros meios de pagamento, para esse orçamento, se aplicável.');

                  $table->unsignedBigInteger('cliente_id')->nullable()
                        ->comment('Referência ao cliente associado a este orçamento.');
                  $table->foreign('cliente_id')->references('id')->on('clientes');

                  $table->unsignedBigInteger('vendedor_id')->nullable()
                        ->comment('Referência ao vendedor associado a este orçamento.');
                  $table->foreign('vendedor_id')->references('id')->on('users');

                  $table->unsignedBigInteger('endereco_id')->nullable()
                        ->comment('Referência ao endereço associado a este orçamento.');
                  $table->foreign('endereco_id')->references('id')->on('enderecos');
                  $table->string('obra')->nullable()
                        ->comment('Apelido do cliente para a obra ou projeto relacionado ao orçamento.');

                  $table->string('frete')->nullable()
                        ->comment('Frete será pago pela empresa ou cliente.');

                  $table->decimal('guia_recolhimento', 15, 2)->nullable()
                        ->comment('Valor da guia de recolhimento associada ao orçamento.');

                  $table->decimal('valor_total_itens', 15, 2)->nullable()
                        ->comment('Valor total dos itens do orçamento.');

                  $table->decimal('desconto_total', 10, 2)->default(0)
                        ->comment('Total de descontos aprovados aplicados ao orçamento.');

                  $table->decimal('valor_com_desconto', 10, 2)->default(0)
                        ->comment('Valor final do orçamento após aplicação dos descontos aprovados.');

                  $table->enum('status', ['Aprovar desconto','Aprovar pagamento', 'Pendente', 'Aprovado',
                   'Finalizado', 'Cancelado', 'Rejeitado', 'Expirado', 'Pago', 'Estornado', 'Sem estoque'])
                        ->default('Pendente')
                        ->comment('Status do orçamento, como pendente, aprovado, cancelado, etc.');

                  $table->string('prazo_entrega', 100)->nullable()
                        ->comment('Prazo de entrega do orçamento.');
                  $table->enum('tipo_documento', ['Nota fiscal', 'Cupom Fiscal', 'Homologação'])->default('Nota fiscal')
                        ->comment('Tipo de documento associado ao orçamento.');
$table->integer('homologacao')->nullable();                      
                  $table->boolean('venda_triangular')->default(false)
                        ->comment('Indica se a venda é triangular.');
                  $table->string('cnpj_triangular')->nullable()
                        ->comment('CNPJ da empresa triangular, se aplicável.');
                  $table->text('observacoes')->nullable()
                        ->comment('Observações adicionais sobre o orçamento.');
                  $table->string('pdf_path')->nullable();

                  $table->unsignedBigInteger('usuario_logado_id')->nullable()
                        ->comment('Referência ao usuário que está logado e criando o orçamento.');
                  $table->foreign('usuario_logado_id')->references('id')->on('users');
                  $table->timestamps();
                  $table->softDeletes();
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('orcamentos');
      }
};
