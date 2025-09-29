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
        Schema::create('orcamento_vidros', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('orcamento_id')->nullable()
                  ->comment('Referência ao orçamento relacionado a este item.');
            $table->foreign('orcamento_id')->references('id')->on('orcamentos');
            
            $table->string('descricao')->nullable()
                    ->comment('Descrição detalhada do vidro orçado.');
            
            // quantidade do produto
            $table->decimal('quantidade', 15, 2)->nullable()
                    ->comment('Quantidade do produto orçado.');

            // altura do produto
            $table->decimal('altura', 15, 2)->nullable()
                    ->comment('Altura do produto orçado.');

            // largura do produto
            $table->decimal('largura', 15, 2)->nullable()
                    ->comment('Largura do produto orçado.');

            // valor por unidade
            $table->decimal('preco_metro_quadrado', 15, 2)->nullable()
                    ->comment('Preço do metro quadrado.');
            // recebeu desconto
            $table->decimal('desconto', 15, 2)->nullable()
                    ->comment('Desconto aplicado ao produto orçado, se houver.');
            // valor com desconto
            $table->decimal('valor_com_desconto', 15, 2)->nullable()
                    ->comment('Valor do produto orçado com desconto aplicado, se houver.');

            // quem deu o desconto
            $table->unsignedBigInteger('user_id')->nullable()
                    ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users');

            // unidade de medida do produto
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamento_vidros');
    }
};
