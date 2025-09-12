<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::create([
            'nome' => 'Guarnições/ Escovas'
        ]);
        Categoria::create([
            'nome' => 'Roldanas'
        ]);
        
        Categoria::create([
            'nome' => 'Esquadrias de Alumínio'
        ]);
        
        Categoria::create([
            'nome' => 'Ferragens VT'
        ]);
        
        Categoria::create([
            'nome' => 'Box'
        ]);
        
        Categoria::create([
            'nome' => 'Guarda Corpo'
        ]);
        
        Categoria::create([
            'nome' => 'Fechaduras'
        ]);
        
        Categoria::create([
            'nome' => 'Integrada'
        ]);
        
        Categoria::create([
            'nome' => 'ferramenta'
        ]);

    }
}
