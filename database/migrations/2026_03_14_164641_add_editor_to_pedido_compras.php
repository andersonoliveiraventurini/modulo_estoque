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
        Schema::table('pedido_compras', function (Blueprint $table) {
            $table->foreignId('editor_usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('editado_em')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_compras', function (Blueprint $table) {
            $table->dropForeign(['editor_usuario_id']);
            $table->dropColumn(['editor_usuario_id', 'editado_em']);
        });
    }
};
