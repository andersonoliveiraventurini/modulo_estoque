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
        Schema::create('bloco_k', function (Blueprint $table) {
            $table->id();
            $table->text('k001')->nullable()
                  ->comment('Abertura do Bloco K - Marca o início do Bloco K.');

            $table->text('k010')->nullable()
                  ->comment('Leiaute adotado (simplificado ou completo) - Indica se a escrituração está sendo feita no formato simplificado ou completo. Fonte: seventreinamentos.com.br');

            $table->text('k100')->nullable()
                  ->comment('Período de apuração do ICMS/IPI - Define o intervalo de datas do período que está sendo escriturado.');

            $table->text('k200')->nullable()
                  ->comment('Estoque Escriturado - Informa o estoque final ao fim do período de apuração, por tipo de estoque e por participante. Fontes: legislacao.fazenda.sp.gov.br, Siga o Fisco.');

            $table->text('k220')->nullable()
                  ->comment('Outras movimentações internas - Movimentações internas como transferência entre estoques, ajustes, perdas etc. (presente em escrituração completa).');

            $table->text('k230')->nullable()
                  ->comment('Industrialização por terceiros – entrada - Recebimento de produtos para industrialização em terceiros.');

            $table->text('k235')->nullable()
                  ->comment('Insumos consumidos na industrialização por terceiros – entrada - Detalha os insumos utilizados nessa industrialização.');

            $table->text('k250')->nullable()
                  ->comment('Industrialização para terceiros – saída - Produtos enviados após industrialização.');

            $table->text('k255')->nullable()
                  ->comment('Insumos consumidos na industrialização para terceiros – saída - Detalha os insumos consumidos nesse processo.');

            $table->text('k260')->nullable()
                  ->comment('Reprocessamento ou reparo de produto - Registra reprocessamentos ou reparos realizados em produtos.');

            $table->text('k265')->nullable()
                  ->comment('Insumos consumidos no reprocessamento/reparo - Especifica os insumos utilizados nessas atividades.');

            $table->text('k270')->nullable()
                  ->comment('Correção de apontamento de produção - Ajustes em apurações de produção previamente informadas. Fonte: SAP Community.');

            $table->text('k275')->nullable()
                  ->comment('Insumos consumidos na correção de produção - Especifica insumos corrigidos nesse apontamento.');

            $table->text('k280')->nullable()
                  ->comment('Correção de apontamento – estoque escriturado - Ajuste no estoque escriturado anteriormente (registro K200). Fontes: autoatendimento.contmatic.com.br, legislacao.fazenda.sp.gov.br');

            $table->text('k990')->nullable()
                  ->comment('Encerramento do Bloco K - Finaliza o bloco, com totalização e fechamento da sequência.');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloco_k');
    }
};
