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
            $table->string('sku')->unique()->comment('Stock Keeping Unit - Código único para identificar o produto em estoque.');   
            $table->string('nome');
            $table->string('tipo_produto_sped')->nullable()->comment('Tipo do produto conforme a classificação do SPED - Indica a categoria do produto segundo as normas do Sistema Público de Escrituração Digital (SPED).');
            $table->string('ncm')->nullable()->comment('Nomenclatura Comum do Mercosul - Código utilizado para identificar a natureza de um produto no comércio internacional.');
            $table->string('codigo_barras')->nullable()->comment('Código de barras - Código utilizado para identificar o produto de forma única.');
            $table->unsignedBigInteger('fornecedor_id')->nullable()
                  ->comment('Referência ao fornecedor deste produto.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');
            $table->decimal('preco_custo', 15, 2)->nullable()->comment('Preço de custo do produto - Valor gasto para adquirir ou produzir o produto.');
            $table->decimal('preco_venda', 15, 2)->nullable()->comment('Preço de venda do produto - Valor pelo qual o produto é comercializado.');
            $table->decimal('estoque_minimo', 15, 2)->nullable()->comment('Estoque mínimo - Quantidade mínima que deve ser mantida em estoque para evitar falta.');
            $table->decimal('estoque_atual', 15, 2)->nullable()->comment('Estoque atual - Quantidade atual disponível em estoque.');
            $table->string('unidade_medida', 10)->nullable()->comment('Unidade de medida - Unidade utilizada para quantificar o produto, como kg, g, L, etc.');
            $table->string('codigo_barras')->nullable()->comment('Código de barras - Código utilizado para identificar o produto de forma única.');
            $table->string('marca')->nullable()->comment('Marca do produto - Nome da marca ou fabricante do produto.');
            $table->string('modelo')->nullable()->comment('Modelo do produto - Identificação específica do modelo do produto.');
            $table->string('cor')->nullable()->comment('Cor do produto - Descrição da cor do produto.');
            $table->string('peso')->nullable()->comment('Peso do produto - Peso do produto, geralmente em kg ou g.');   
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('imagem_principal')->nullable()->comment('Caminho para a imagem do produto - Armazena o caminho do arquivo de imagem do produto.');
            $table->boolean('ativo')->default(true)->comment('Indica se o produto está ativo ou inativo no sistema.');
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
