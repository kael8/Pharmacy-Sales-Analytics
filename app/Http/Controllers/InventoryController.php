<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product; // Add this line to import the 'Product' class
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this line to import the 'Log' facade


class InventoryController extends Controller
{
    public function addProduct($id = null)
    {
        if ($id !== null) {
            $stock = Product::find($id);
            return view('inventory.restock', compact('stock'));
        }
        $action = 'addProduct';
        return view('inventory.restock', compact('action'));
    }

    public function createProduct(Request $request)
    {
        // Validate the request data, including expiration_date for inventory
        $validation = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string|max:255',

        ]);

        // Check if the product already exists
        $check = Product::where('product_name', $request->product_name)->first();
        if ($check) {
            return response()->json([
                'message' => 'Product already exists',
            ], 409);
        }

        // Create the product
        $product = Product::create([
            'product_name' => $request->product_name,
            'product_description' => $request->product_description,


        ]);



        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }



    public function viewProducts()
    {

        return view('inventory.viewProducts');
    }

    public function products(Request $request)
    {
        // Get the search query from the request
        $searchQuery = $request->input('search');

        // If a search query is provided, filter products based on the query
        $products = Product::when($searchQuery, function ($query, $searchQuery) {
            return $query->where('product_name', 'like', '%' . $searchQuery . '%')
                ->orWhere('product_description', 'like', '%' . $searchQuery . '%');
        })
            ->leftJoin('inventories', function ($join) {
                $join->on('products.id', '=', 'inventories.product_id')
                    ->whereIn('inventories.id', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('inventories')
                            ->groupBy('batch_id');
                    });
            })
            ->select('products.*', DB::raw('SUM(inventories.quantity) as total_quantity'), DB::raw('SUM(inventories.price * inventories.quantity) as total_price'))
            ->groupBy('products.id')
            ->paginate(15);

        // If a search query is provided, filter inventory batches based on the query
        $query = Inventory::with('product');
        $totalProductValue = $query->get()->sum(function ($batch) {
            return $batch->price * $batch->quantity; // Using the price and quantity from the inventory
        });

        $totalProducts = $query->count(); // Count the total number of batches

        // Return the paginated product data and additional information
        return response()->json([
            'totalProductValue' => $totalProductValue,
            'totalProducts' => $totalProducts,
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'next_page_url' => $products->nextPageUrl(),
            'prev_page_url' => $products->previousPageUrl(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
            'total' => $products->total(),
        ]);
    }


    public function viewInventoryBatches()
    {
        return view('inventory.viewBatches');
    }
    public function batches(Request $request)
    {
        // Get the search query from the request
        $searchQuery = $request->input('search');

        // Start the query, joining with the related product table
        $query = Inventory::with('product')  // Load the related 'product' data
            ->select('inventories.*')  // Select all columns from the 'inventories' table
            ->join(
                DB::raw('(SELECT MAX(created_at) as max_created_at, batch_id FROM inventories GROUP BY batch_id) as latest_batches'),
                function ($join) {
                    $join->on('inventories.batch_id', '=', 'latest_batches.batch_id')
                        ->on('inventories.created_at', '=', 'latest_batches.max_created_at');
                }
            )
            ->orderBy('created_at', 'desc'); // Joining the latest batch for each batch_id

        // Apply the search filter if provided
        if ($searchQuery) {
            $query->whereHas('product', function ($subQuery) use ($searchQuery) {
                $subQuery->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('inventories.batch_id', 'like', '%' . $searchQuery . '%'); // Explicitly use 'inventories.batch_id'
                });
            });
        }

        // Calculate total product value and total products from filtered inventory records
        $totalProductValue = $query->get()->sum(function ($batch) {
            return $batch->price * $batch->quantity;  // Calculate product value
        });

        $totalProducts = $query->count();  // Count the total number of unique batches

        // Paginate the results
        $batches = $query->paginate(15);

        // Return the paginated batch data and additional information
        return response()->json([
            'totalProductValue' => $totalProductValue,  // Total value of products
            'totalProducts' => $totalProducts,  // Total number of product batches
            'data' => $batches->items(),  // The actual batch data for this page
            'current_page' => $batches->currentPage(),  // Current page number
            'last_page' => $batches->lastPage(),  // Last page number
            'next_page_url' => $batches->nextPageUrl(),  // URL for the next page
            'prev_page_url' => $batches->previousPageUrl(),  // URL for the previous page
            'from' => $batches->firstItem(),  // First item on the current page
            'to' => $batches->lastItem(),  // Last item on the current page
            'total' => $batches->total(),  // Total number of records across all pages
        ]);
    }








    public function editBatch($id = null)
    {
        if ($id !== null) {

            $batch = Inventory::with('product')->whereHas('product', function ($query) use ($id) {
                $query->where('inventories.id', $id);
            })->firstOrFail();

            // Add product details to the batch object
            $batch->product_name = $batch->product->product_name;
            $batch->product_description = $batch->product->product_description;


            $action = 'edit';
            return view('inventory.restock', compact('batch', 'action'));
        }
        return view('inventory.restock');
    }

    public function updateBatch(Request $request)
    {
        $request->validate([
            'product_quantity' => 'required|numeric',
            'product_price' => 'required|numeric',
            'cost_price' => 'required|numeric', // Added validation for cost_price
            'expiration_date' => 'required|date|after:today',
            'batch_id' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Get the latest batch record by batch_id
            $latestBatch = Inventory::where('batch_id', $request->input('batch_id'))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestBatch) {
                // Create a new inventory record with the same batch_id
                Inventory::create([
                    'product_id' => $latestBatch->product_id,
                    'batch_id' => $latestBatch->batch_id, // Keep the same batch_id
                    'quantity' => $request->input('product_quantity'),
                    'action_type' => 'adjustment',
                    'price' => $request->input('product_price'),
                    'cost_price' => $request->input('cost_price'),
                    'stock_date' => now(),
                    'expiration_date' => $request->input('expiration_date'),
                ]);

                DB::commit();

                return response()->json(['message' => 'New batch record created successfully']);
            } else {
                // Handle case if no existing batch is found
                return response()->json(['message' => 'Batch not found'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create new batch record', 'error' => $e->getMessage()], 500);
        }
    }



    public function updateProduct(Request $request)
    {

        $product = Product::findOrFail($request->product_id);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->product_id),
            ],
            'product_description' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Update the product with the validated data
            $product->update($validatedData);

            DB::commit();

            // Log the successful update (optional)
            Log::info('Product updated', ['product_id' => $product->product_id]);

            return response()->json(['message' => 'Product updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error (optional)
            Log::error('Failed to update product', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }


    public function addBatch()
    {
        $products = Product::all();
        $action = 'add';
        return view('inventory.restock', compact('products', 'action'));
    }

    public function trackInventory()
    {
        // Fetch all products for filter dropdowns
        $products = Product::all();

        // Fetch unique batch IDs from the Inventory table and group them by batch_id
        $batches = Inventory::select('batch_id')
            ->groupBy('batch_id')
            ->orderBy('batch_id', 'desc')
            ->get();

        // Render the view with products and batches for filter dropdowns
        return view('inventory.trackInventory', compact('products', 'batches'));
    }



    public function track(Request $request)
    {
        $product_id = $request->input('product_id');
        $batch_id = $request->input('batch_id');

        $query = Inventory::with('product')->orderBy('created_at', 'desc');

        // Apply filters based on selected product and batch
        if ($product_id) {
            $query->where('product_id', $product_id);
        }
        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        }

        $inventory = $query->paginate(10); // Paginate by 10 items per page

        $product = Product::all();

        // Calculate total product value
        $totalProductValue = $product->sum(function ($product) {
            return $product->price * $product->quantity_in_stock;
        });

        // Calculate total number of unique products
        $totalProducts = $inventory->filter(function ($item) {
            return $item->quantity > 0;
        })->groupBy('product_id')->count();

        // Get recent activities (latest inventory updates)
        $recentActivities = Inventory::with('product')->orderBy('stock_date', 'desc')->take(5)->get();

        // Extract unique batches from inventory
        $uniqueBatches = Inventory::where('product_id', $product_id)
            ->select('batch_id', 'price', 'quantity', 'created_at')
            ->whereIn('id', function ($query) use ($product_id) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('inventories')
                    ->where('product_id', $product_id)
                    ->groupBy('batch_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'inventory' => $inventory->items(),
            'totalProductValue' => $totalProductValue,
            'totalProducts' => $totalProducts,
            'recentActivities' => $recentActivities,
            'current_page' => $inventory->currentPage(),
            'last_page' => $inventory->lastPage(),
            'per_page' => $inventory->perPage(),
            'total' => $inventory->total(),
            'batches' => $uniqueBatches, // New field for unique batches
        ]);
    }





    public function createBatch(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_price' => 'required|numeric',
            'product_quantity' => 'required|numeric',
            'cost_price' => 'required|numeric', // Added validation for cost_price
            'expiration_date' => 'required|date|after:today', // Add validation for expiration date
        ]);

        // Generate a unique batch_id
        $batch_id = 'BATCH-' . strtoupper(uniqid());

        // Create the inventory record with expiration date and batch_id
        Inventory::create([
            'product_id' => $request->product_id,
            'stock_date' => now(),
            'quantity' => $request->product_quantity,
            'price' => $request->product_price,
            'expiration_date' => $request->expiration_date, // Save expiration date
            'batch_id' => $batch_id, // Save batch_id
            'cost_price' => $request->cost_price, // Save the cost price
        ]);

        return response()->json([
            'message' => 'Batch created successfully',
        ], 201);
    }


    public function editProduct(Request $request)
    {
        $product = Product::findOrFail($request)->first();

        $action = 'editProduct';
        return view('inventory.restock', compact('product', 'action'));
    }
}
