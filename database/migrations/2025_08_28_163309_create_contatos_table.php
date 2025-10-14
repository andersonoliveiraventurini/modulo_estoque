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
        Schema::create('contatos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();

            // cliente
            $table->unsignedBigInteger('cliente_id')->nullable()
                    ->comment('Referência ao cliente, se houver.');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            // fornecedor
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                    ->comment('Referência ao fornecedor, se houver.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contatos');
    }
};
