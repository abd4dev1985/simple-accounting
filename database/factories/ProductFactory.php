<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    

    public function definition(): array
    {
        $goods=['tv','pc screen','mobile','fan', 'microwave ','washing machine','refrigerator','laptop','dish waher'];
        $brands=['lg','samsung','الحافظ','هاي لايف','philps','toshipa','panasonic','dell'];

        return [
            'name' =>  $this->faker->randomElement( $goods)." ".$this->faker->company()   ,
            'serial'=> $this->faker->ean13(),
        ];
    }




}
