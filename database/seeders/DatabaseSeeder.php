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

        User::factory()->create([
            'name' => 'Anderson',
            'email' => 'anderson@gmail.com',
            'password' => Hash::make('12345'),
            'email_verified_at' => Carbon::now()
        ]);

        $this->call(NcmSeeder::class);
        //$this->call(FornecedorSeeder::class);
        $this->call(VendedorSeeder::class);
        $this->call(ClienteSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(ProdutoSeeder::class);
        $this->call(ArmazemSeeder::class);
        $this->call(SubCategoriaSeeder::class);
        $this->call(CorSeeder::class);
    }
}
