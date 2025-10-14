<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;
use App\Models\Contato;
use GuzzleHttp\Client;

class ClienteSeederAtualizar extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        $clientes = Cliente::all();
        foreach ($clientes as $cliente) {
            
        }
    }
}
