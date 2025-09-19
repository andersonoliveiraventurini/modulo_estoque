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
            $table->integer('numero_brcom')->nullable();
            $table->string('cpf')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('nome')->nullable();          
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('tratamento')->nullable();    
            $table->integer('status')->default(1); // 1 - Ativo, 0 - Inativo
            $table->date('data_nascimento')->nullable();

             // Dados da empresa
            $table->string('inscricao_estadual')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->date('data_abertura')->nullable();
            $table->string('cnae')->nullable();
            $table->string('regime_tributario')->nullable();

            // Responsável legal
            $table->string('suframa')->nullable();

            // Classificação
            $table->string('classificacao')->nullable();
            $table->string('canal_origem')->nullable();

            // Informações de crédito
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->unsignedBigInteger('vendedor_externo_id')->nullable();
            $table->decimal('desconto', 5, 2)->nullable();
            $table->boolean('bloqueado')->default(false);
            
            $table->boolean('negociar_titulos')->default(false);
            $table->integer('inativar_apos')->nullable();

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
