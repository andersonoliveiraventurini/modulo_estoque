<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use App\Models\Venda;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Papéis e Permissões Primeiro
        $this->call(RolesAndPermissionsSeeder::class);

        /*User::create([
            'name' => 'Anderson',
            'email' => 'anderson@gmail.com',
            'password' => Hash::make('12345'),
            'email_verified_at' => Carbon::now()
        ]);*/

         $users = [
            // Administradores
            ['name' => 'Anderson', 'email' => 'anderson@gmail.com', 'role' => 'admin'],
            ['name' => 'Cezar', 'email' => 'cezar@acav.com', 'role' => 'admin'],
            ['name' => 'Thiago', 'email' => 'thiago@acav.com', 'role' => 'admin'],

            // Financeiro (Supervisor)
            ['name' => 'Alline', 'email' => 'alline@acav.com', 'role' => 'supervisor'],
            ['name' => 'Jaqueline', 'email' => 'jaqueline@acav.com', 'role' => 'supervisor'],
            ['name' => 'Ana Claudia', 'email' => 'ana.claudia@acav.com', 'role' => 'supervisor'],
            ['name' => 'Juliane', 'email' => 'juliane@acav.com', 'role' => 'supervisor'],
            ['name' => 'Emily', 'email' => 'emily@acav.com', 'role' => 'supervisor'],

            // Compras
            ['name' => 'Fabio', 'email' => 'fabio@acav.com', 'role' => 'compras'],

            // Estoque / Conferência / Expedição
            ['name' => 'Sandro', 'email' => 'sandro@acav.com', 'role' => 'estoquista'],
            ['name' => 'Adenilson', 'email' => 'adenilson@acav.com', 'role' => 'estoquista'],
            ['name' => 'Lucas', 'email' => 'lucas@acav.com', 'role' => 'estoquista'],
            ['name' => 'Matheus', 'email' => 'matheus@acav.com', 'role' => 'estoquista'],
            ['name' => 'Victor', 'email' => 'victor@acav.com', 'role' => 'estoquista'],
            ['name' => 'Pedro', 'email' => 'pedro@acav.com', 'role' => 'estoquista'],
            ['name' => 'Carlos Eduardo', 'email' => 'carlos.eduardo@acav.com', 'role' => 'estoquista'],
            ['name' => 'Aylam', 'email' => 'aylam@acav.com', 'role' => 'estoquista'],

            // Vendas
            ['name' => 'Pamela', 'email' => 'pamela@acav.com', 'role' => 'vendedor'],
            ['name' => 'Gustavo', 'email' => 'gustavo@acav.com', 'role' => 'vendedor'],
            ['name' => 'Aline Maia', 'email' => 'aline.maia@acav.com', 'role' => 'vendedor'],
            ['name' => 'Leticia Loretti', 'email' => 'leticia.loretti@acav.com', 'role' => 'vendedor'],
            ['name' => 'Miria', 'email' => 'miria@acav.com', 'role' => 'vendedor'],
            ['name' => 'Eduardo', 'email' => 'eduardo@acav.com', 'role' => 'vendedor'],
            ['name' => 'Silvane', 'email' => 'silvane@acav.com', 'role' => 'vendedor'],
            ['name' => 'Leticia Dusso', 'email' => 'leticia.dusso@acav.com', 'role' => 'vendedor'],
            ['name' => 'Alessandro', 'email' => 'alessandro@acav.com', 'role' => 'vendedor'],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('12345'),
                    'email_verified_at' => Carbon::now(),
                ]
            );
            
            // Atribui o cargo se o usuário não tiver nenhum
            if ($user->roles->isEmpty()) {
                $user->assignRole($userData['role']);
            }
        }
        //$this->call(FornecedorSeeder::class);

        $this->call(NcmSeeder::class);
        $this->call(VendedorSeeder::class);
        $this->call(ClienteSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(ProdutoSeeder::class);
        $this->call(ArmazemSeeder::class);
        $this->call(SubCategoriaSeeder::class);
        $this->call(CorSeeder::class);
        $this->call(TipoTransporteSeeder::class);
        $this->call(CondicoesPagamentoSeeder::class);
    }
}
