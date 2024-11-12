<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Inventory;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $productIds = Product::pluck('id')->toArray(); // Get all existing product IDs
        $userIds = DB::table('users')->pluck('id')->toArray(); // Get all existing user IDs

        DB::beginTransaction();

        try {
            foreach (range(1, 500) as $index) { // Create 500 sales records, adjust as necessary
                $productId = $faker->randomElement($productIds); // Pick a random product
                $userId = $faker->randomElement($userIds); // Pick a random user
                $product = Product::find($productId);

                if ($product) {
                    $quantitySold = $faker->numberBetween(1, 10); // Random quantity sold

                    // Fetch the latest inventory record for this product
                    $inventory = Inventory::where('product_id', $productId)
                        ->latest('created_at')
                        ->first();

                    // Check if there is enough stock and the batch hasn't expired
                    if ($inventory && $inventory->quantity >= $quantitySold && Carbon::now()->lessThan(Carbon::parse($inventory->expiration_date))) {
                        // Generate a sale date that is before the inventory expiration date
                        $randomDate = $faker->dateTimeBetween('-4 years', Carbon::parse($inventory->expiration_date)->subDay());

                        // Insert into sales
                        Sale::create([
                            'product_id' => $product->id,
                            'user_id' => $userId, // Add user_id
                            'quantity_sold' => $quantitySold,
                            'cost' => $quantitySold * $inventory->cost_price, // Use the cost price from inventory
                            'profit' => ($quantitySold * $inventory->price) - ($quantitySold * $inventory->cost_price),
                            'total_amount' => $quantitySold * $inventory->price, // Use the price from inventory
                            'sale_date' => $randomDate, // Ensure sale date is valid
                            'batch_id' => $inventory->batch_id // Include batch_id from the inventory
                        ]);

                        // Update the existing inventory record to reflect the remaining stock
                        $inventory->quantity -= $quantitySold;
                        $inventory->save();

                        // Create a new inventory record to reflect the reduced stock
                        Inventory::create([
                            'product_id' => $product->id,
                            'batch_id' => $inventory->batch_id,
                            'quantity' => $quantitySold, // Quantity sold
                            'price' => $inventory->price, // Use the price from the inventory
                            'cost_price' => $inventory->cost_price, // Use the cost price from the inventory
                            'action_type' => 'reduced',
                            'expiration_date' => $inventory->expiration_date, // Maintain the expiration date
                            'stock_date' => $inventory->stock_date, // Use the stock date from the existing inventory
                            'created_at' => $randomDate, // Set created_at to sale_date
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