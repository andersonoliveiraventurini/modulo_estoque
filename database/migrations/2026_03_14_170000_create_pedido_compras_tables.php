<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pedido_compras')) {
            Schema::create('pedido_compras', function (Blueprint $table) {
                $table->id();

                $table->foreignId('fornecedor_id')->constrained('fornecedores');
                $table->foreignId('usuario_id')->constrained('users');

                $table->date('data_pedido');
                $table->date('previsao_entrega')->nullable();

                $table->enum('status', ['aguardando', 'parcialmente_recebido', 'recebido', 'cancelado'])
                    ->default('aguardando');

                $table->string('numero_pedido')->nullable()->comment('Número interno ou externo do pedido');
                $table->string('arquivo_pedido')->nullable()->comment('Upload do documento do pedido');

                // Pagamento
                $table->foreignId('condicao_pagamento_id')->nullable()->constrained('condicoes_pagamento');
                $table->text('forma_pagamento_descricao')->nullable()->comment('Descrição da forma / condição de pagamento');
                $table->double('valor_total')->nullable();

                $table->text('observacao')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('pedido_compra_itens')) {
            Schema::create('pedido_compra_itens', function (Blueprint $table) {
                $table->id();

                $table->foreignId('pedido_compra_id')->constrained('pedido_compras')->cascadeOnDelete();
                $table->foreignId('produto_id')->nullable()->constrained('produtos');

                $table->string('descricao_livre')->nullable()->comment('Texto livre caso o produto ainda não exista no cadastro');
                $table->double('quantidade');
                $table->double('valor_unitario')->nullable();
                $table->double('valor_total')->nullable();
                $table->text('observacao')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_compra_itens');
        Schema::dropIfExists('pedido_compras');
    }
};
