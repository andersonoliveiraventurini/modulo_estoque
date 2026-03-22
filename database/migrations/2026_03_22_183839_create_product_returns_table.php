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
        Schema::create('product_returns', function (Blueprint $table) {
            $table->id();
            $table->string('nr')->unique();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->string('produto_nome')->nullable();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->string('fornecedor_nome')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('cliente_nome')->nullable();
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vendedor_nome')->nullable();
            $table->date('data_ocorrencia');
            $table->string('nota_fiscal')->nullable();
            $table->string('romaneio_recebimento')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('gerar_credito')->default(false);
            $table->boolean('troca_produto')->default(false);
            $table->boolean('retorno_estoque')->nullable();
            $table->string('status')->default('pendente'); // pendente, finalizado, aguardando_autorizacao, em_troca
            $table->foreignId('usuario_id')->constrained('users'); // criador
            $table->foreignId('responsavel_estoque_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('finalizado_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_returns');
    }
};
