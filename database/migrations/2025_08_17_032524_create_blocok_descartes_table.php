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
        Schema::create('blocok_descartes', function (Blueprint $table) {
            $table->id();

            // produto que será criado 
            $table->unsignedBigInteger('produto_id')->nullable()
                  ->comment('Referência ao produto relacionado a este descarte do Bloco K.');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            // produto que será descartado
            $table->unsignedBigInteger('produto_descartado_id')->nullable()
                  ->comment('Referência ao produto que foi descartado.');
            $table->foreign('produto_descartado_id')->references('id')->on('produtos')->onDelete('cascade');

            // quantidade descartada
            $table->decimal('quantidade_descarte', 15, 2)->nullable()
                    ->comment('Quantidade de produto descartada durante a operação, se houver descarte.');
            // unidade de medida do descarte
            $table->string('unidade_medida_descarte', 10)->nullable()
                    ->comment('Unidade de medida do descarte, como kg, g, L, etc.');    
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocok_descartes');
    }
};
