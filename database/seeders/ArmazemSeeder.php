<?php

namespace Database\Seeders;

use App\Models\Armazem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArmazemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Armazem::updateOrCreate(
            ['nome' => 'HUB'],
            [
                'tipo' => 'hub',
                'is_active' => true,
                'localizacao' => 'Central',
                'descricao' => 'Armazém Principal de Recebimento'
            ]
        );
    }
}
