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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            // qual tipo de documento
            $table->string('tipo')->comment('Tipo de documento, como nota fiscal, recibo, contrato, etc.');
            // descrição opcional
            $table->string('descricao')->nullable()->comment('Descrição ou título do documento.');
            // caminho do arquivo
            $table->string('caminho_arquivo')->comment('Caminho do arquivo armazenado.');
            // quem criou
            $table->unsignedBigInteger('user_id')->nullable()
                    ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users');
            // qual cliente
            $table->unsignedBigInteger('cliente_id')->nullable()
                    ->comment('Referência ao cliente associado ao documento, se aplicável.');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            // qual fornecedor
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                    ->comment('Referência ao fornecedor associado ao documento, se aplicável.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
