<?php

namespace Database\Seeders;

use App\Models\SubCategoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubCategoria::create([
            'nome' => 'Nylon',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Suprema',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Gold',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Linha 20',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Linha 25',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Linha 30',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Linha 42',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Contra Marco',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Macho Cunha',
            'categoria_id' => 4
        ]);
        SubCategoria::create([
            'nome' => 'Portão',
            'categoria_id' => 4
        ]);
    }
}
