<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['product_name' => 'Biogesic', 'product_description' => 'Effective pain relief for headaches and fever.'],
            ['product_name' => 'Neozep', 'product_description' => 'Cold medicine for nasal congestion and flu.'],
            ['product_name' => 'Alaxan', 'product_description' => 'Dual-action relief for body pain and fever.'],
            ['product_name' => 'Tempra', 'product_description' => 'Fast relief from fever and minor aches.'],
            ['product_name' => 'Paracetamol', 'product_description' => 'General fever reducer and pain reliever.'],
            ['product_name' => 'Amoxicillin', 'product_description' => 'Antibiotic for bacterial infections.'],
            ['product_name' => 'Decolgen', 'product_description' => 'Cold and flu medicine.'],
            ['product_name' => 'Advil', 'product_description' => 'Pain reliever for headaches, arthritis, and muscle pain.'],
            ['product_name' => 'Mefenamic Acid', 'product_description' => 'Pain reliever for menstrual cramps.'],
            ['product_name' => 'Cough Syrup', 'product_description' => 'Soothing relief for coughs.'],
            ['product_name' => 'Vitamin C', 'product_description' => 'Boosts immune system.'],
            ['product_name' => 'Multivitamins', 'product_description' => 'Daily multivitamins for overall health.'],
            ['product_name' => 'Ibuprofen', 'product_description' => 'Reduces fever and treats pain.'],
            ['product_name' => 'Cetirizine', 'product_description' => 'Antihistamine for allergy relief.'],
            ['product_name' => 'Hydrocortisone Cream', 'product_description' => 'Topical cream for skin inflammation.'],
            ['product_name' => 'Antibacterial Soap', 'product_description' => 'Cleans and disinfects skin.'],
            ['product_name' => 'Oral Rehydration Salts', 'product_description' => 'Replenishes fluids and electrolytes.'],
            ['product_name' => 'Salbutamol Inhaler', 'product_description' => 'Asthma relief inhaler.'],
            ['product_name' => 'Ranitidine', 'product_description' => 'Treats and prevents ulcers.'],
            ['product_name' => 'Loperamide', 'product_description' => 'Treats diarrhea.'],
            ['product_name' => 'Chlorpheniramine', 'product_description' => 'Antihistamine for allergy symptoms.'],
            ['product_name' => 'Folic Acid', 'product_description' => 'Supports prenatal health.'],
            ['product_name' => 'Iron Supplement', 'product_description' => 'Treats and prevents iron deficiency.'],
            ['product_name' => 'Zinc Tablet', 'product_description' => 'Boosts immune system and speeds up wound healing.'],
            ['product_name' => 'Antiseptic Solution', 'product_description' => 'Cleans and disinfects wounds.'],
            ['product_name' => 'Acetaminophen', 'product_description' => 'Pain reliever and fever reducer.'],
            ['product_name' => 'Antacid Tablet', 'product_description' => 'Neutralizes stomach acid.'],
            ['product_name' => 'Oral Antifungal', 'product_description' => 'Treats oral fungal infections.'],
            ['product_name' => 'Aspirin', 'product_description' => 'Pain reliever and blood thinner.'],
            ['product_name' => 'Eye Drops', 'product_description' => 'Relieves dryness and irritation in the eyes.'],
        ];

        $productsToInsert = [];
        $inventoriesToInsert = [];
        $batchesPerProduct = 3; // Number of batches per product

        // Prepare product data for batch insert
        foreach ($products as $data) {
            $productsToInsert[] = [
                'product_name' => $data['product_name'],
                'product_description' => $data['product_description'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert products in one query
        Product::insert($productsToInsert);

        // Fetch the recently inserted products
        $insertedProducts = Product::whereIn('product_name', array_column($products, 'product_name'))->get();

        foreach ($insertedProducts as $product) {
            for ($i = 0; $i < $batchesPerProduct; $i++) {
                // Generate a unique batch_id for each batch
                $batch_id = 'BATCH-' . strtoupper(uniqid());

                // Generate a random expiration date between today and 2 years from now
                $expirationDate = Carbon::now()->addDays(rand(30, 730));

                // Get varying prices for each batch based on the product name
                $randomQuantity = rand(10, 100); // Randomize batch quantity
                $randomPrice = $this->getPhilippinePrice($product->product_name);
                $randomCostPrice = $randomPrice * 0.7; // Set cost price as 70% of sale price

                // Generate a random stock date before the sale date (up to 5 years ago)
                $randomStockDate = Carbon::now()->subDays(rand(365, 1825));

                // Prepare inventory data for multiple batches
                $inventoriesToInsert[] = [
                    'product_id' => $product->id,
                    'stock_date' => $randomStockDate, // Use the random stock date before sales
                    'quantity' => $randomQuantity,
                    'price' => $randomPrice,
                    'expiration_date' => $expirationDate,
                    'batch_id' => $batch_id,
                    'cost_price' => $randomCostPrice,
                    'created_at' => $randomStockDate, // Set created_at to the stock date
                    'updated_at' => $randomStockDate, // Set updated_at to the stock date
                ];
            }
        }

        // Batch insert the inventory records with multiple batches per product
        Inventory::insert($inventoriesToInsert);
    }

    /**
     * Return varying prices based on the Philippine economy for specific products.
     */
    private function getPhilippinePrice($productName)
    {
        // Base prices for products
        $basePrices = [
            'Biogesic' => 10,
            'Neozep' => 8,
            // Add other products and their prices...
        ];

        // Get the base price for the product
        $basePrice = $basePrices[$productName] ?? 10; // Default price if product not found

        // Apply random variation to the base price
        $variation = rand(-2, 2); // Price variation between -2 and +2 PHP
        return max(1, $basePrice + $variation); // Ensure the price is at least 1 PHP
    }
}