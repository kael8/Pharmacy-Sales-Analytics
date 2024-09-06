<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        // Ensure Faker is using the 'en_US' locale
        $this->faker = \Faker\Factory::create('en_US');

        return [
            'product_name' => $this->faker->words(2, true), // Generate a product name with two words
            'product_description' => $this->faker->sentence, // Generate an English sentence
            'price' => $this->faker->randomFloat(2, 1, 1000), // Price between 1 and 1000
            'quantity_in_stock' => $this->faker->numberBetween(0, 100), // Quantity between 0 and 100
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            Inventory::create([
                'product_id' => $product->id,
                'stock_date' => now(),
                'quantity' => $product->quantity_in_stock,
                'price' => $product->price,
            ]);
        });
    }
}
