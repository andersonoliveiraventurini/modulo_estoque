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
        Schema::table('orcamentos', function (Blueprint $row) {
            $row->timestamp('retirado_em')->nullable();
            $row->foreignId('retirado_por_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $row) {
            $row->dropForeign(['retirado_por_id']);
            $row->dropColumn(['retirado_em', 'retirado_por_id']);
        });
    }
};
