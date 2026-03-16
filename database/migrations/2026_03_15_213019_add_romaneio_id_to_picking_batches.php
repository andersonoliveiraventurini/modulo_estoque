<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('picking_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('picking_batches', 'romaneio_id')) {
                $table->foreignId('romaneio_id')->nullable()->constrained('romaneios')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('picking_batches', function (Blueprint $table) {
            $table->dropForeign(['romaneio_id']);
            $table->dropColumn('romaneio_id');
        });
    }
};
