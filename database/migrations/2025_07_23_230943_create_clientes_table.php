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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('cpf')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('nome')->nullable();          
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('tratamento')->nullable();    
            $table->integer('status')->default(1); // 1 - Ativo, 0 - Inativo
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
