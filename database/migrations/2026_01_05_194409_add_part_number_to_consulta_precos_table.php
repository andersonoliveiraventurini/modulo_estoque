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
        Schema::table('consulta_precos', function (Blueprint $table) {            
            $table->string('part_number')->nullable()->comment('Part Number - Identificador único do produto fornecido pelo fabricante.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consulta_precos', function (Blueprint $table) {
            //
        });
    }
};
