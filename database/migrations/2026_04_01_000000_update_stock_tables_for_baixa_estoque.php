<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update estoque_reservas
        if (Schema::hasTable('estoque_reservas')) {
            Schema::table('estoque_reservas', function (Blueprint $table) {
                if (!Schema::hasColumn('estoque_reservas', 'armazem_id')) {
                    $table->foreignId('armazem_id')->nullable()->after('produto_id')->constrained('armazens');
                }
            });
        }

        // Update stock_movement_logs
        if (Schema::hasTable('stock_movement_logs')) {
            Schema::table('stock_movement_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movement_logs', 'orcamento_id')) {
                    $table->foreignId('orcamento_id')->nullable()->after('colaborador_id')->constrained('orcamentos')->nullOnDelete();
                }
                if (!Schema::hasColumn('stock_movement_logs', 'origem')) {
                    $table->string('origem')->nullable()->after('orcamento_id');
                }
                if (!Schema::hasColumn('stock_movement_logs', 'destino')) {
                    $table->string('destino')->nullable()->after('origem');
                }
                if (!Schema::hasColumn('stock_movement_logs', 'motivo')) {
                    $table->string('motivo')->nullable()->after('destino');
                }
            });
        }

        // Update ordens_reposicao
        if (Schema::hasTable('ordens_reposicao')) {
            Schema::table('ordens_reposicao', function (Blueprint $table) {
                if (!Schema::hasColumn('ordens_reposicao', 'orcamento_id')) {
                    $table->foreignId('orcamento_id')->nullable()->after('produto_id')->constrained('orcamentos')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('estoque_reservas')) {
            Schema::table('estoque_reservas', function (Blueprint $table) {
                $table->dropForeign(['armazem_id']);
                $table->dropColumn('armazem_id');
            });
        }

        if (Schema::hasTable('stock_movement_logs')) {
            Schema::table('stock_movement_logs', function (Blueprint $table) {
                $table->dropForeign(['orcamento_id']);
                $table->dropColumn(['orcamento_id', 'origem', 'destino', 'motivo']);
            });
        }

        if (Schema::hasTable('ordens_reposicao')) {
            Schema::table('ordens_reposicao', function (Blueprint $table) {
                $table->dropForeign(['orcamento_id']);
                $table->dropColumn('orcamento_id');
            });
        }
    }
};
