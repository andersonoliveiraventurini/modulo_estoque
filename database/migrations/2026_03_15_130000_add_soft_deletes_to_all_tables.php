<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing();
        
        $exclude = [
            'migrations',
            'password_reset_tokens',
            'sessions',
            'jobs',
            'failed_jobs',
            'cache',
            'cache_locks',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'personal_access_tokens',
        ];

        foreach ($tables as $table) {
            if (in_array($table, $exclude)) continue;

            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        // Operação de reversão complexa para uma migration dinâmica, 
        // mas para fins deste script, vamos deixar vazio ou tentar o inverso.
    }
};
