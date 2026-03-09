<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            $table->string('ncm', 20)->nullable()->after('observacao')
                ->comment('Nomenclatura Comum do Mercosul');
            $table->string('codigo_barras', 60)->nullable()->after('ncm')
                ->comment('Código de barras EAN/GTIN');
            $table->string('sku', 100)->nullable()->after('codigo_barras')
                ->comment('SKU / código interno do produto');
            $table->string('unidade_medida', 20)->nullable()->after('sku')
                ->comment('Ex: UN, KG, M, CX');
            $table->decimal('peso', 10, 3)->nullable()->after('unidade_medida')
                ->comment('Peso em kg');
            $table->unsignedBigInteger('categoria_id')->nullable()->after('peso');
            $table->foreign('categoria_id')->references('id')->on('categorias')->nullOnDelete();
            $table->unsignedBigInteger('sub_categoria_id')->nullable()->after('categoria_id');
            $table->foreign('sub_categoria_id')->references('id')->on('sub_categorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['sub_categoria_id']);
            $table->dropColumn([
                'ncm', 'codigo_barras', 'sku',
                'unidade_medida', 'peso',
                'categoria_id', 'sub_categoria_id',
            ]);
        });
    }
};