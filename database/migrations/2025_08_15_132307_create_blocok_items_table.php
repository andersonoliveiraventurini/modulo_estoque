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
        Schema::create('bloco_k_items', function (Blueprint $table) {
            $table->id();
            // produto que será produzido
            $table->unsignedBigInteger('produto_id')->nullable()
                  ->comment('Referência ao produto relacionado a este item do Bloco K.');
            $table->foreign('produto_id')->references('id')->on('produtos');

            // quantidade do produto
            $table->decimal('quantidade', 15, 2)->nullable()
                    ->comment('Quantidade do produto produzido.');
            // unidade de medida do produto
            $table->string('unidade_medida', 10)->nullable()
                    ->comment('Unidade de medida do produto produzido, como kg, g, L, etc.');

           


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloco_k_items');
    }
};
