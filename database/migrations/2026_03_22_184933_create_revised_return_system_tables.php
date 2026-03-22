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
        // Limpa a tabela anterior simplificada
        Schema::dropIfExists('product_returns');

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('nr')->unique(); // DEV-YYYY-NNNN
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('vendedor_id')->nullable()->constrained('users');
            $table->foreignId('usuario_id')->constrained('users'); // Quem iniciou
            
            $table->enum('status', [
                'pendente_supervisor', 
                'pendente_estoque', 
                'finalizado', 
                'negado', 
                'em_troca'
            ])->default('pendente_supervisor');
            
            $table->boolean('troca_produto')->default(false);
            $table->date('data_ocorrencia');
            $table->string('nota_fiscal')->nullable();
            $table->string('romaneio_recebimento')->nullable();
            $table->text('observacoes')->nullable();
            $table->text('observacoes_estoque')->nullable();
            $table->decimal('valor_total_credito', 15, 2)->default(0);
            
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->onDelete('cascade');
            $table->foreignId('orcamento_item_id')->constrained('orcamento_itens');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        Schema::create('return_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('role'); // supervisor, estoque
            $table->enum('status', ['aprovado', 'negado']);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('client_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('return_id')->nullable()->constrained('returns');
            $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->decimal('valor', 15, 2);
            $table->string('descricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_credits');
        Schema::dropIfExists('return_authorizations');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
    }
};
