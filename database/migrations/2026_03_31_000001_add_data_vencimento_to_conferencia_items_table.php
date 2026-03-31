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
        Schema::table('conferencia_items', function (Blueprint $table) {
            if (!Schema::hasColumn('conferencia_items', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('qty_conferida')
                    ->comment('Data de vencimento informada na conferência técnica.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferencia_items', function (Blueprint $table) {
            if (Schema::hasColumn('conferencia_items', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }
};
