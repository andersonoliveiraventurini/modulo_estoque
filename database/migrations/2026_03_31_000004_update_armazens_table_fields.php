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
        Schema::table('armazens', function (Blueprint $table) {
            if (!Schema::hasColumn('armazens', 'tipo')) {
                $table->enum('tipo', ['hub', 'secondary'])->default('secondary')->after('nome');
            }
            if (!Schema::hasColumn('armazens', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('tipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('armazens', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'is_active']);
        });
    }
};
