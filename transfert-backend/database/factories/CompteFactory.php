<?php
namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::inRandomOrder()->first();
        $fournisseur = $this->faker->randomElement(['Wave', 'OrangeMoney', 'Wari', 'CB']);
        $code = '';
        switch ($fournisseur) {
            case 'Wave':
                $code = 'WV';
                break;
            case 'OrangeMoney':
                $code = 'OM';
                break;
            case 'Wari':
                $code = 'WR';
                break;
            case 'CB':
                $code = 'CB';
                break;
        }
        $numero_compte = $code . '-' . $client->numero_telephone;
        
        return [
            'numero_compte' => $numero_compte,
            'solde' => $this->faker->randomFloat(2, 0, 1000000),
            'client_id' => $client->id,
            'fournisseur' => $fournisseur,
        ];
    }
}
