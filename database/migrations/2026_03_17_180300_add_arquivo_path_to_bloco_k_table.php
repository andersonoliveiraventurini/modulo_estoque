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
        Schema::table('bloco_k', function (Blueprint $table) {
            $table->string('arquivo_path')->nullable()->after('k990');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloco_k', function (Blueprint $table) {
            $table->dropColumn('arquivo_path');
        });
    }
};
