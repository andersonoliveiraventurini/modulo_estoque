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
        Schema::create('non_conformities', function (Blueprint $table) {
            $table->id();
            $table->string('nr')->unique();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->string('produto_nome')->nullable();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->string('fornecedor_nome')->nullable();
            $table->date('data_ocorrencia');
            $table->string('nota_fiscal')->nullable();
            $table->string('romaneio_recebimento')->nullable();
            $table->text('acoes_tomadas')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_conformities');
    }
};
