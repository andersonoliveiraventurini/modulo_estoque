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
        Schema::create('metodos_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->comment('Nome do método de pagamento');
            $table->string('codigo')->unique()->comment('Código único do método');
           $table->enum('tipo', [
                            'dinheiro',
                            'cartao_credito',
                            'cartao_debito',
                            'pix',
                            'boleto1',
                            'boleto2',
                            'boleto3',
                            'boleto4',
                            'boleto5',
                            'boleto6',
                            'boleto7',
                            'cheque1',
                            'cheque2',
                            'cheque3',
                            'cheque4',
                            'cheque5',
                            'cheque6',
                            'cheque7',
                            'credito_cliente',
                            'outros'
                        ])->comment('Tipo do método de pagamento');

            $table->boolean('permite_parcelamento')->default(false)
                ->comment('Se permite parcelamento');
            $table->integer('max_parcelas')->nullable()->default(null)
                ->comment('Número máximo de parcelas permitidas');
            $table->boolean('ativo')->default(true)->comment('Se o método está ativo');
            $table->integer('ordem')->default(0)->comment('Ordem de exibição');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insere métodos padrão
        DB::table('metodos_pagamento')->insert([
            [
                'nome' => 'Dinheiro',
                'codigo' => 'dinheiro',
                'tipo' => 'dinheiro',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 1,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'PIX',
                'codigo' => 'pix',
                'tipo' => 'pix',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 2,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
             

            [
                'nome' => 'Cartão de Débito',
                'codigo' => 'cartao_debito',
                'tipo' => 'cartao_debito',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 3,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            [
                'nome' => 'Cartão de Crédito',
                'codigo' => 'cartao_credito',
                'tipo' => 'cartao_credito',
                'permite_parcelamento' => true,
                'max_parcelas' => 12,
                'ativo' => true,
                'ordem' => 4,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Crédito do Cliente',
                'codigo' => 'credito_cliente',
                'tipo' => 'credito_cliente',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 5,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Boleto 07 dias',
                'codigo' => 'boleto1',
                'tipo' => 'boleto1',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 3,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Boleto 14 dias',
                'codigo' => 'boleto2',
                'tipo' => 'boleto2',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 4,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'nome' => 'Boleto 21 dias',
                'codigo' => 'boleto3',
                'tipo' => 'boleto3',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 5,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'nome' => 'Boleto 28 dias',
                'codigo' => 'boleto4',
                'tipo' => 'boleto4',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 6,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'nome' => 'Boleto 28/56 dias',
                'codigo' => 'boleto5',
                'tipo' => 'boleto5',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 6,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Boleto 28/42/56 dias',
                'codigo' => 'boleto6',
                'tipo' => 'boleto6',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 7,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cheque 07 dias',
                'codigo' => 'cheque1',
                'tipo' => 'cheque1',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 8,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cheque 14 dias',
                'codigo' => 'cheque2',
                'tipo' => 'cheque2',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 9,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'nome' => 'Cheque 21 dias',
                'codigo' => 'cheque3',
                'tipo' => 'cheque3',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 10,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'nome' => 'Cheque 28 dias',
                'codigo' => 'cheque4',
                'tipo' => 'cheque4',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 11,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'nome' => 'Cheque 28/56 dias',
                'codigo' => 'cheque5',
                'tipo' => 'cheque5',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 12,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   
                'nome' => 'Cheque 28/42/56 dias',
                'codigo' => 'cheque6',
                'tipo' => 'cheque6',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 13,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cheque 28/56/84 dias',
                'codigo' => 'cheque7',
                'tipo' => 'cheque7',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 14,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            [
                'nome' => 'Outros',
                'codigo' => 'outros',
                'tipo' => 'outros',
                'permite_parcelamento' => false,
                'max_parcelas' => null,
                'ativo' => true,
                'ordem' => 99,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_pagamento');
    }
};
