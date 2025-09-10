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
        Armazem::factory()->create([
            'nome' => 'HUB',
        ]);
    }
}
