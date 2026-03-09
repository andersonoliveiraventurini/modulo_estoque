<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Registra o comprador (usuário) que preencheu os preços do fornecedor
        Schema::table('consulta_preco_fornecedores', function (Blueprint $table) {
            $table->foreignId('comprador_id')
                ->nullable()
                ->after('selecionado')
                ->constrained('users')
                ->nullOnDelete();
        });

        // 2. Tabela de entradas de encomendas (cabeçalho)
        Schema::create('entrada_encomendas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grupo_id')           // cotação de origem
                ->constrained('consulta_preco_grupos')
                ->cascadeOnDelete();

            $table->foreignId('recebido_por')       // usuário que deu entrada
                ->constrained('users');

            $table->foreignId('entregue_para')      // vendedor/usuário que recebeu
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();

            $table->date('data_recebimento');
            $table->date('data_entrega')->nullable();

            $table->enum('status', [
                'Recebido parcialmente',
                'Recebido completo',
                'Entregue',
            ])->default('Recebido parcialmente');

            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        // 3. Itens de cada entrada
        Schema::create('entrada_encomenda_itens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('entrada_id')
                ->constrained('entrada_encomendas')
                ->cascadeOnDelete();

            $table->foreignId('consulta_preco_id')  // item de cotação de origem
                ->constrained('consulta_precos')
                ->cascadeOnDelete();

            $table->decimal('quantidade_solicitada', 10, 2);
            $table->decimal('quantidade_recebida',   10, 2)->default(0);

            $table->boolean('recebido_completo')->default(false);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_encomenda_itens');
        Schema::dropIfExists('entrada_encomendas');
        Schema::table('consulta_preco_fornecedores', function (Blueprint $table) {
            $table->dropForeign(['comprador_id']);
            $table->dropColumn('comprador_id');
        });
    }
};