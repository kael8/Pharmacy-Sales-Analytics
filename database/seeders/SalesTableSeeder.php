<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Inventory;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class SalesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $productIds = Product::pluck('id')->toArray(); // Get all existing product IDs

        DB::beginTransaction();

        try {
            foreach (range(1, 20) as $index) { // Create 20 sales records, adjust as necessary
                $productId = $faker->randomElement($productIds); // Pick a random product
                $product = Product::find($productId);

                if ($product) {
                    $quantitySold = $faker->numberBetween(1, 10); // Random quantity sold

                    if ($product->quantity_in_stock >= $quantitySold) {
                        // Generate random dates
                        $randomDate = $faker->dateTimeBetween('-2 months', 'now');

                        // Update product stock
                        $product->quantity_in_stock -= $quantitySold;
                        $product->save();

                        // Insert into inventory
                        Inventory::create([
                            'product_id' => $product->id,
                            'quantity' => $product->quantity_in_stock,
                            'price' => $product->price,
                            'stock_date' => $randomDate, // Use the random date here
                        ]);

                        // Insert into sales
                        Sale::create([
                            'product_id' => $product->id,
                            'user_id' => 2, // Use an existing user ID or change accordingly
                            'quantity_sold' => $quantitySold,
                            'total_amount' => $quantitySold * $product->price,
                            'sale_date' => $randomDate, // Use the random date here
                        ]);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
