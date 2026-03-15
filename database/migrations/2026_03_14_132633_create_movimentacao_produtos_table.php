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
        if (!Schema::hasTable('movimentacao_produtos')) {
            Schema::create('movimentacao_produtos', function (Blueprint $table) {
                $table->id();
                
                $table->unsignedBigInteger('movimentacao_id');
                $table->foreign('movimentacao_id')->references('id')->on('movimentacoes')->onDelete('cascade');
                
                $table->unsignedBigInteger('produto_id');
                $table->foreign('produto_id')->references('id')->on('produtos');

                $table->unsignedBigInteger('fornecedor_id')->nullable();
                $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
                
                $table->integer('quantidade');
                $table->double('valor_unitario')->nullable();
                $table->double('valor_total')->nullable();
                
                $table->string('endereco')->nullable();
                $table->string('corredor')->nullable();
                $table->string('posicao')->nullable();
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacao_produtos');
    }
};
