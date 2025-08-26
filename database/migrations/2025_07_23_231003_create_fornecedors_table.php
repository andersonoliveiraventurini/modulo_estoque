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
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->integer('linha_brcom')->nullable();
            $table->string('nome_fantasia');
            $table->text('descricao')->nullable();
            $table->text('observacao')->nullable();
            $table->string('email')->nullable();           
            $table->string('telefone')->nullable();
            $table->string('CNPJ')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};
