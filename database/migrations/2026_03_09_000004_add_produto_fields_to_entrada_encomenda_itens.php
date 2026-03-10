<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {

            if (!Schema::hasColumn('entrada_encomenda_itens', 'ncm')) {
                $table->string('ncm', 20)
                    ->nullable()
                    ->after('observacao')
                    ->comment('Nomenclatura Comum do Mercosul');
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'codigo_barras')) {
                $table->string('codigo_barras', 60)
                    ->nullable()
                    ->after('ncm')
                    ->comment('Código de barras EAN/GTIN');
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'sku')) {
                $table->string('sku', 100)
                    ->nullable()
                    ->after('codigo_barras')
                    ->comment('SKU / código interno do produto');
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'unidade_medida')) {
                $table->string('unidade_medida', 20)
                    ->nullable()
                    ->after('sku')
                    ->comment('Ex: UN, KG, M, CX');
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'peso')) {
                $table->decimal('peso', 10, 3)
                    ->nullable()
                    ->after('unidade_medida')
                    ->comment('Peso em kg');
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'categoria_id')) {
                $table->unsignedBigInteger('categoria_id')
                    ->nullable()
                    ->after('peso');

                $table->foreign('categoria_id')
                    ->references('id')
                    ->on('categorias')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('entrada_encomenda_itens', 'sub_categoria_id')) {
                $table->unsignedBigInteger('sub_categoria_id')
                    ->nullable()
                    ->after('categoria_id');

                $table->foreign('sub_categoria_id')
                    ->references('id')
                    ->on('sub_categorias')
                    ->nullOnDelete();
            }

        });
    }

    public function down(): void
    {
        Schema::table('entrada_encomenda_itens', function (Blueprint $table) {

            if (Schema::hasColumn('entrada_encomenda_itens', 'categoria_id')) {
                $table->dropForeign(['categoria_id']);
            }

            if (Schema::hasColumn('entrada_encomenda_itens', 'sub_categoria_id')) {
                $table->dropForeign(['sub_categoria_id']);
            }

            $table->dropColumn([
                'ncm',
                'codigo_barras',
                'sku',
                'unidade_medida',
                'peso',
                'categoria_id',
                'sub_categoria_id'
            ]);
        });
    }
};