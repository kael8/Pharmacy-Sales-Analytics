<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product; // Add this line to import the 'Product' class
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class InventoryController extends Controller
{
    public function restock($id = null)
    {
        if ($id !== null) {
            $stock = Product::find($id);
            return view('inventory.restock', compact('stock'));
        }
        return view('inventory.restock');
    }

    public function createProduct(Request $request)
    {

        $validation = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string|max:255',
            'product_price' => 'required|numeric',
            'product_quantity' => 'required|numeric',

        ]);
        $check = Product::where('product_name', $request->product_name)->first();
        if ($check) {
            return response()->json([
                'message' => 'Product already exists',
            ], 409);
        }
        $product = Product::create(([
            'product_name' => $request->product_name,
            'product_description' => $request->product_description,
            'price' => $request->product_price,
            'quantity_in_stock' => $request->product_quantity,
        ]));

        Inventory::create([
            'product_id' => $product->id,
            'stock_date' => now(),
            'quantity' => $product->quantity_in_stock,
            'price' => $request->input('product_price'),
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);

    }

    public function viewProducts()
    {
        $products = Product::all();
        return view('inventory.viewProducts', compact('products'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($id),
            ],
            'product_description' => 'required|string|max:255',
            'product_price' => 'required|numeric',
            'product_quantity' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Update the product with the validated data
            $product->update([
                'product_name' => $request->input('product_name'),
                'product_description' => $request->input('product_description'),
                'price' => $request->input('product_price'),
                'quantity_in_stock' => $request->input('product_quantity'),
            ]);


            // Update the inventory
            Inventory::create([
                'product_id' => $product->id,
                'stock_date' => now(),
                'quantity' => $request->input('product_quantity'),
                'price' => $request->input('product_price'),
            ]);

            DB::commit();

            // Optionally, you can return a response or redirect
            return response()->json(['message' => 'Product updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }

    public function trackInventory()
    {
        $inventory = Inventory::with('product')->orderBy('created_at', 'desc')->get();
        $product = Product::all();

        // Calculate total product value
        $totalProductValue = $product->sum(function ($product) {
            return $product->price * $product->quantity_in_stock;
        });

        // Calculate total number of unique products that are currently present
        $totalProducts = $inventory->filter(function ($item) {
            return $item->quantity > 0;
        })->groupBy('product_id')->count();

        // Get recent activities (for simplicity, let's assume recent activities are the latest inventory updates)
        $recentActivities = Inventory::with('product')->orderBy('stock_date', 'desc')->take(5)->get();
        return view('inventory.trackInventory', compact('inventory', 'totalProductValue', 'totalProducts', 'recentActivities'));
    }
}
