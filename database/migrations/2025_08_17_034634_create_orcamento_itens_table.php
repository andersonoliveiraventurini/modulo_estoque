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
        Schema::create('orcamento_itens', function (Blueprint $table) {
            $table->id();
            // produto que será orçado
            $table->unsignedBigInteger('produto_id')->nullable()
                  ->comment('Referência ao produto relacionado a este item do orçamento.');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            // quantidade do produto
            $table->decimal('quantidade', 15, 2)->nullable()
                    ->comment('Quantidade do produto orçado.');
            // valor por unidade
            $table->decimal('valor_unitario', 15, 2)->nullable()
                    ->comment('Valor unitário do produto orçado.');
            // recebeu desconto
            $table->decimal('desconto', 15, 2)->nullable()
                    ->comment('Desconto aplicado ao produto orçado, se houver.');
            // valor com desconto
            $table->decimal('valor_com_desconto', 15, 2)->nullable()
                    ->comment('Valor do produto orçado com desconto aplicado, se houver.');

            // quem deu o desconto
            $table->unsignedBigInteger('user_id')->nullable()
                    ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('orcamento_itens');
    }
};
