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
        if (Schema::hasTable('non_conformities')) {
            Schema::table('non_conformities', function (Blueprint $table) {
                if (!Schema::hasColumn('non_conformities', 'quantidade')) {
                    $table->decimal('quantidade', 12, 3)->default(0)->after('produto_nome');
                }
                if (!Schema::hasColumn('non_conformities', 'baixar_estoque')) {
                    $table->boolean('baixar_estoque')->default(false)->after('quantidade');
                }
                if (!Schema::hasColumn('non_conformities', 'armazem_id')) {
                    $table->foreignId('armazem_id')->nullable()->after('baixar_estoque')->constrained('armazens');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('non_conformities')) {
            Schema::table('non_conformities', function (Blueprint $table) {
                $table->dropForeign(['armazem_id']);
                $table->dropColumn(['quantidade', 'baixar_estoque', 'armazem_id']);
            });
        }
    }
};
