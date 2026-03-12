<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('vendedor_assistente_id')->nullable()->after('vendedor_externo_id');
            $table->foreign('vendedor_assistente_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['vendedor_assistente_id']);
            $table->dropColumn('vendedor_assistente_id');
        });
    }
};