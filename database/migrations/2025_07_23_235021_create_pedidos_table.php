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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable()
                ->comment('Referência ao cliente associado a este orçamento.');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedBigInteger('vendedor_id')->nullable()
                ->comment('Referência ao vendedor associado a este orçamento.');
            $table->foreign('vendedor_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('vendedor_externo_id')->nullable()
                ->comment('Referência ao vendedor externo associado a este pedido.');
            $table->foreign('vendedor_externo_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('endereco_id')->nullable()
                ->comment('Referência ao endereço associado a este orçamento.');
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('cascade');
            $table->string('obra')->nullable()
                ->comment('Apelido do cliente para a obra ou projeto relacionado ao orçamento.');
            $table->decimal('valor_total', 15, 2)->nullable()
                ->comment('Valor total do pedido.');
            $table->string('status', 20)->default('pendente')
                ->comment('Status do pedido, como pendente, aprovado, cancelado, etc.');
            $table->text('observacoes')->nullable()
                ->comment('Observações adicionais sobre o pedido.');
                
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
