<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orcamentos 
            MODIFY COLUMN workflow_status ENUM(
                'aguardando_pagamento',
                'aguardando_separacao',
                'em_separacao',
                'aguardando_conferencia',
                'em_conferencia',
                'conferido',
                'finalizado',
                'cancelado'
            ) NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orcamentos 
            MODIFY COLUMN workflow_status ENUM(
                'aguardando_separacao',
                'em_separacao',
                'aguardando_conferencia',
                'em_conferencia',
                'conferido',
                'finalizado',
                'cancelado'
            ) NULL DEFAULT NULL
        ");
    }
};