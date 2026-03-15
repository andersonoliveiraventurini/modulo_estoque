<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fatura>
 */
class FaturaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => \App\Models\Cliente::factory(),
            'valor_total' => $this->faker->randomFloat(2, 50, 1000),
            'valor_pago' => 0,
            'numero_parcela' => 1,
            'total_parcelas' => 1,
            'data_vencimento' => now()->addDays(30),
            'status' => 'pendente',
        ];
    }
}
