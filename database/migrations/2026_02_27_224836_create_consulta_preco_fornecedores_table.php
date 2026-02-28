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
        Schema::create('consulta_preco_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consulta_preco_id');
            $table->foreign('consulta_preco_id')->references('id')->on('consulta_precos')->onDelete('cascade');
            $table->unsignedBigInteger('fornecedor_id');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
            $table->double('preco_compra')->nullable()->comment('Preço cotado por este fornecedor.');
            $table->double('preco_venda')->nullable()->comment('Preço de venda sugerido para este fornecedor.');
            $table->string('prazo_entrega', 100)->nullable();
            $table->boolean('selecionado')->default(false)
                ->comment('Fornecedor escolhido pelo compras para este item.');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_preco_fornecedores');
    }
};
