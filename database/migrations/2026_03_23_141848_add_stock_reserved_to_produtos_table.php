<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('stock_reserved', 12, 3)
                ->default(0)
                ->after('estoque_atual')
                ->comment('Quantidade total reservada por orçamentos aprovados ainda não faturados.');
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('stock_reserved');
        });
    }
};
