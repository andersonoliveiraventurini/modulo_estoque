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
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->enum('workflow_status', [
                'aguardando_separacao',
                'em_separacao',
                'aguardando_conferencia',
                'em_conferencia',
                'conferido',
                'finalizado',
                'cancelado'
            ])->nullable()->after('status')->comment('Fluxo operacional do pedido/orçamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('workflow_status');
        });
    }
};
