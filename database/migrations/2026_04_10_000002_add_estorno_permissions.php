<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Adiciona as permissões do módulo de estornos.
     *
     * ATENÇÃO: as mesmas permissões devem ser adicionadas ao
     * RolesAndPermissionsSeeder para manter a consistência entre
     * ambientes que rodam apenas o seeder.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'estorno_solicitar',    // Abrir solicitação de estorno
            'estorno_aprovar',      // Aprovar ou rejeitar solicitações
            'estorno_concluir',     // Marcar como executado (concluido)
            'estorno_visualizar',   // Visualizar lista e detalhes
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }
};
