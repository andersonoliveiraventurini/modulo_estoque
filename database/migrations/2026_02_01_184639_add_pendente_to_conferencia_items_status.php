<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: altera o ENUM para incluir 'pendente'
        // Se usar PostgreSQL, adapte para ALTER TYPE
        DB::statement("ALTER TABLE conferencia_items MODIFY COLUMN status ENUM('ok', 'divergente', 'pendente') NOT NULL DEFAULT 'pendente'");

        // Atualiza registros existentes que estão com status 'ok' mas nunca foram conferidos
        DB::statement("
            UPDATE conferencia_items
            SET status = 'pendente'
            WHERE conferido_por_id IS NULL
              AND status = 'ok'
        ");
    }

    public function down(): void
    {
        // Reverte itens pendentes que nunca foram conferidos para 'ok'
        DB::statement("UPDATE conferencia_items SET status = 'ok' WHERE status = 'pendente'");

        DB::statement("ALTER TABLE conferencia_items MODIFY COLUMN status ENUM('ok', 'divergente') NOT NULL DEFAULT 'ok'");
    }
};