<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['product_name' => 'Biogesic', 'product_description' => 'Effective pain relief for headaches and fever.', 'price' => 12.00, 'quantity_in_stock' => 88],
            ['product_name' => 'Neozep', 'product_description' => 'Cold medicine for nasal congestion and flu.', 'price' => 10.00, 'quantity_in_stock' => 95],
            ['product_name' => 'Alaxan', 'product_description' => 'Dual-action relief for body pain and fever.', 'price' => 20.00, 'quantity_in_stock' => 50],
            ['product_name' => 'Tempra', 'product_description' => 'Fast relief from fever and minor aches.', 'price' => 15.00, 'quantity_in_stock' => 75],
            ['product_name' => 'Paracetamol', 'product_description' => 'General fever reducer and pain reliever.', 'price' => 5.00, 'quantity_in_stock' => 150],
            ['product_name' => 'Amoxicillin', 'product_description' => 'Antibiotic for bacterial infections.', 'price' => 50.00, 'quantity_in_stock' => 30],
            ['product_name' => 'Decolgen', 'product_description' => 'Cold and flu medicine.', 'price' => 8.00, 'quantity_in_stock' => 80],
            ['product_name' => 'Advil', 'product_description' => 'Pain reliever for headaches, arthritis, and muscle pain.', 'price' => 25.00, 'quantity_in_stock' => 45],
            ['product_name' => 'Mefenamic Acid', 'product_description' => 'Pain reliever for menstrual cramps.', 'price' => 18.00, 'quantity_in_stock' => 60],
            ['product_name' => 'Cough Syrup', 'product_description' => 'Soothing relief for coughs.', 'price' => 22.00, 'quantity_in_stock' => 35],
            ['product_name' => 'Vitamin C', 'product_description' => 'Boosts immune system.', 'price' => 7.00, 'quantity_in_stock' => 100],
            ['product_name' => 'Multivitamins', 'product_description' => 'Daily multivitamins for overall health.', 'price' => 15.00, 'quantity_in_stock' => 120],
            ['product_name' => 'Ibuprofen', 'product_description' => 'Reduces fever and treats pain.', 'price' => 13.00, 'quantity_in_stock' => 110],
            ['product_name' => 'Cetirizine', 'product_description' => 'Antihistamine for allergy relief.', 'price' => 6.00, 'quantity_in_stock' => 65],
            ['product_name' => 'Hydrocortisone Cream', 'product_description' => 'Topical cream for skin inflammation.', 'price' => 35.00, 'quantity_in_stock' => 40],
            ['product_name' => 'Antibacterial Soap', 'product_description' => 'Cleans and disinfects skin.', 'price' => 3.00, 'quantity_in_stock' => 200],
            ['product_name' => 'Oral Rehydration Salts', 'product_description' => 'Replenishes fluids and electrolytes.', 'price' => 12.00, 'quantity_in_stock' => 70],
            ['product_name' => 'Salbutamol Inhaler', 'product_description' => 'Asthma relief inhaler.', 'price' => 40.00, 'quantity_in_stock' => 20],
            ['product_name' => 'Ranitidine', 'product_description' => 'Treats and prevents ulcers.', 'price' => 22.00, 'quantity_in_stock' => 55],
            ['product_name' => 'Loperamide', 'product_description' => 'Treats diarrhea.', 'price' => 5.00, 'quantity_in_stock' => 130],
            ['product_name' => 'Chlorpheniramine', 'product_description' => 'Antihistamine for allergy symptoms.', 'price' => 10.00, 'quantity_in_stock' => 85],
            ['product_name' => 'Folic Acid', 'product_description' => 'Supports prenatal health.', 'price' => 8.00, 'quantity_in_stock' => 95],
            ['product_name' => 'Iron Supplement', 'product_description' => 'Treats and prevents iron deficiency.', 'price' => 17.00, 'quantity_in_stock' => 65],
            ['product_name' => 'Zinc Tablet', 'product_description' => 'Boosts immune system and speeds up wound healing.', 'price' => 9.00, 'quantity_in_stock' => 90],
            ['product_name' => 'Antiseptic Solution', 'product_description' => 'Cleans and disinfects wounds.', 'price' => 11.00, 'quantity_in_stock' => 85],
            ['product_name' => 'Acetaminophen', 'product_description' => 'Pain reliever and fever reducer.', 'price' => 14.00, 'quantity_in_stock' => 100],
            ['product_name' => 'Antacid Tablet', 'product_description' => 'Neutralizes stomach acid.', 'price' => 7.00, 'quantity_in_stock' => 105],
            ['product_name' => 'Oral Antifungal', 'product_description' => 'Treats oral fungal infections.', 'price' => 28.00, 'quantity_in_stock' => 50],
            ['product_name' => 'Aspirin', 'product_description' => 'Pain reliever and blood thinner.', 'price' => 12.00, 'quantity_in_stock' => 70],
            ['product_name' => 'Eye Drops', 'product_description' => 'Relieves dryness and irritation in the eyes.', 'price' => 18.00, 'quantity_in_stock' => 60],
        ];

        foreach ($products as $data) {
            $product = Product::create($data);

            // Optionally, create an inventory record associated with each product
            Inventory::create([
                'product_id' => $product->id,
                'stock_date' => now(),
                'quantity' => $product->quantity_in_stock,
                'price' => $product->price,
            ]);
        }
    }
}