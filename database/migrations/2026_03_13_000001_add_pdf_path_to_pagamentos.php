<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()
                ->after('observacoes')
                ->comment('Caminho do comprovante PDF no disco public: pagamentos/comprovante_{id}.pdf');
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};