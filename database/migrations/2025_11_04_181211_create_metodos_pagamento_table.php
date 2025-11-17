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
            $table->enum('tipo', ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'boleto', 'transferencia', 'credito_cliente', 'outro'])
                ->comment('Tipo do método de pagamento');
            $table->boolean('permite_parcelamento')->default(false)
                ->comment('Se permite parcelamento');
            $table->integer('max_parcelas')->nullable()
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
                'ativo' => true,
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'PIX',
                'codigo' => 'pix',
                'tipo' => 'pix',
                'permite_parcelamento' => false,
                'ativo' => true,
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cartão de Débito',
                'codigo' => 'cartao_debito',
                'tipo' => 'cartao_debito',
                'permite_parcelamento' => false,
                'ativo' => true,
                'ordem' => 3,
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Crédito do Cliente',
                'codigo' => 'credito_cliente',
                'tipo' => 'credito_cliente',
                'permite_parcelamento' => false,
                'ativo' => true,
                'ordem' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
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
