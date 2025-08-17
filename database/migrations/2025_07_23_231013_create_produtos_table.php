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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo_produto_sped')->nullable()->comment('Tipo do produto conforme a classificação do SPED - Indica a categoria do produto segundo as normas do Sistema Público de Escrituração Digital (SPED).');
            $table->string('ncm')->nullable()->comment('Nomenclatura Comum do Mercosul - Código utilizado para identificar a natureza de um produto no comércio internacional.');
            $table->string('descricao')->nullable();
            $table->string('tamanho')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
