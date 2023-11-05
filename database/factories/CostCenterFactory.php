<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostCenter>
 */
class CostCenterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
   
    {
        $random_number = $this->faker->unique()->numberBetween(1,1000);
        $name='UNRWA invoice number '.$random_number ;
        $account_no=10000+$random_number ;
        return [
            'name' => $name,
            'id' => $account_no,
            'father_costcenter' => 1,
            'has_sons_costcenters'=>  0 ,
            //
        ];
    }
}
