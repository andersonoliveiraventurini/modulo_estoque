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
        Schema::table('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('orcamento_id')->nullable()->after('id');
            $table->unsignedBigInteger('cliente_id')->nullable()->after('orcamento_id');
            $table->unsignedBigInteger('vendedor_id')->nullable()->after('cliente_id');
            $table->decimal('valor_total', 15, 2)->default(0)->after('vendedor_id');
            $table->string('status')->default('concluida')->after('valor_total');
            $table->dateTime('data_venda')->nullable()->after('status');
            
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->onDelete('set null');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->foreign('vendedor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign(['orcamento_id']);
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['vendedor_id']);
            
            $table->dropColumn([
                'orcamento_id',
                'cliente_id',
                'vendedor_id',
                'valor_total',
                'status',
                'data_venda'
            ]);
        });
    }
};
