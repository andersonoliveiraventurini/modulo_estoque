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
        // User::factory(10)->create();

        /*User::create([
            'name' => 'Anderson',
            'email' => 'anderson@gmail.com',
            'password' => Hash::make('12345'),
            'email_verified_at' => Carbon::now()
        ]);*/

         $users = [
        // Usuários existentes (mantidos)
        ['name' => 'Anderson', 'email' => 'anderson@gmail.com'],
        ['name' => 'Cezar', 'email' => 'cezar@acav.com'],
        ['name' => 'Thiago', 'email' => 'thiago@acav.com'],

        // Financeiro
        ['name' => 'Alline', 'email' => 'alline@acav.com'],
        ['name' => 'Jaqueline', 'email' => 'jaqueline@acav.com'],
        ['name' => 'Ana Claudia', 'email' => 'ana.claudia@acav.com'],
        ['name' => 'Juliane', 'email' => 'juliane@acav.com'],
        ['name' => 'Emily', 'email' => 'emily@acav.com'],

        // Compras
        ['name' => 'Fabio', 'email' => 'fabio@acav.com'],

        // Estoque
        ['name' => 'Sandro', 'email' => 'sandro@acav.com'],
        ['name' => 'Adenilson', 'email' => 'adenilson@acav.com'],

        // Conferência / Expedição / Separação
        ['name' => 'Lucas', 'email' => 'lucas@acav.com'],
        ['name' => 'Matheus', 'email' => 'matheus@acav.com'],
        ['name' => 'Victor', 'email' => 'victor@acav.com'],
        ['name' => 'Pedro', 'email' => 'pedro@acav.com'],
        ['name' => 'Carlos Eduardo', 'email' => 'carlos.eduardo@acav.com'],

        // Vendas / Logística
        ['name' => 'Aylam', 'email' => 'aylam@acav.com'],

        // Vendas
        ['name' => 'Pamela', 'email' => 'pamela@acav.com'],
        ['name' => 'Gustavo', 'email' => 'gustavo@acav.com'],
        ['name' => 'Aline Maia', 'email' => 'aline.maia@acav.com'],
        ['name' => 'Leticia Loretti', 'email' => 'leticia.loretti@acav.com'],
        ['name' => 'Miria', 'email' => 'miria@acav.com'],
        ['name' => 'Eduardo', 'email' => 'eduardo@acav.com'],
        ['name' => 'Silvane', 'email' => 'silvane@acav.com'],
        ['name' => 'Leticia Dusso', 'email' => 'leticia.dusso@acav.com'],
        ['name' => 'Alessandro', 'email' => 'alessandro@acav.com'],
    ];

    foreach ($users as $user) {
        User::firstOrCreate(
            ['email' => $user['email']],
            [
                'name' => $user['name'],
                'password' => Hash::make('12345'),
                'email_verified_at' => Carbon::now(),
            ]
        );
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
