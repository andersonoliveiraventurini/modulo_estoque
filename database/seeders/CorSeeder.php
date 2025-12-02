<?php

namespace Database\Seeders;

use App\Models\Cor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cor::create(['nome' => 'Vermelho', 'codigo_hex' => '#FF0000']);
        Cor::create(['nome' => 'Verde', 'codigo_hex' => '#00FF00']);
        Cor::create(['nome' => 'Azul', 'codigo_hex' => '#0000FF']);
        Cor::create(['nome' => 'Amarelo', 'codigo_hex' => '#FFFF00']);
        Cor::create(['nome' => 'Preto', 'codigo_hex' => '#000000']);
        Cor::create(['nome' => 'Branco', 'codigo_hex' => '#FFFFFF']);
        Cor::create(['nome' => 'Cinza', 'codigo_hex' => '#808080']);
        Cor::create(['nome' => 'Laranja ', 'codigo_hex' => '#FFA500']);
        Cor::create(['nome' => 'Roxo', 'codigo_hex' => '#800080']);
        Cor::create(['nome' => 'Rosa', 'codigo_hex' => '#FFC0CB']);
        Cor::create(['nome' => 'Marrom', 'codigo_hex' => '#A52A2A']);
        Cor::create(['nome' => 'Ciano', 'codigo_hex' => '#00FFFF']);
        Cor::create(['nome' => 'Prata', 'codigo_hex' => '#C0C0C0']);
        Cor::create(['nome' => 'Dourado', 'codigo_hex' => '#FFD700']);
        Cor::create(['nome' => 'Bege', 'codigo_hex' => '#F5F5DC']);
        Cor::create(['nome' => 'Fosco', 'codigo_hex' => '#708090']);
    }
}
