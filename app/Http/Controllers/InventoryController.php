<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\RefundAmount;
use Illuminate\Http\Request;
use App\Models\Product; // Add this line to import the 'Product' class
use Carbon\Carbon; // Add this line to import the 'Carbon' class
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this line to import the 'Log' facade
use App\Models\Sale; // Add this line to import the 'Sale' class
use App\Models\Notification; // Add this line to import the 'Notification' class


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
        // Get the search query and sorting parameters from the request
        $searchQuery = $request->input('search');
        $sortColumn = $request->input('sort', 'product_name'); // Default sort column
        $sortDirection = $request->input('direction', 'asc'); // Default sort direction

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
            ->join('products', 'products.id', '=', 'inventories.product_id'); // Join with products table

        // Apply the search filter if provided
        if ($searchQuery) {
            $query->whereHas('product', function ($subQuery) use ($searchQuery) {
                $subQuery->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('inventories.batch_id', 'like', '%' . $searchQuery . '%'); // Explicitly use 'inventories.batch_id'
                });
            });
        }

        // Apply sorting
        if ($sortColumn == 'product_name') {
            $query->orderBy('products.product_name', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Paginate the results
        $batches = $query->paginate(15);

        // Return the paginated batch data and additional information
        return response()->json([
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


    public function viewInventoryBatches()
    {
        return view('inventory.viewBatches');
    }
    public function batches(Request $request)
    {
        // Get the search query and sorting parameters from the request
        $searchQuery = $request->input('search');
        $sortColumn = $request->input('sort', 'product_name'); // Default sort column to 'product_name'
        $sortDirection = $request->input('direction', 'asc'); // Default sort direction

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
            ->join('products', 'products.id', '=', 'inventories.product_id'); // Join with products table

        // Apply the search filter if provided
        if ($searchQuery) {
            $query->whereHas('product', function ($subQuery) use ($searchQuery) {
                $subQuery->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('inventories.batch_id', 'like', '%' . $searchQuery . '%'); // Explicitly use 'inventories.batch_id'
                });
            });
        }

        // Apply sorting
        if ($sortColumn == 'product_name') {
            $query->orderBy('products.product_name', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
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
                ->join('products', 'products.id', '=', 'inventories.product_id'); // Join with products table

            $searchBatches = $query->get();
            return view('inventory.restock', compact('batch', 'action', 'searchBatches'));
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
                Rule::unique('products')->ignore($product->id, 'id'), // Ignore the unique rule if the product_name is the same as the record in product_id
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
        $products = Product::orderBy('product_name', 'asc')->get();

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
        $sale_count = [];

        $query = Inventory::with('product')
            ->where('action_type', '!=', 'cancelled') // Exclude cancelled items
            ->orderBy('created_at', 'desc');

        // Apply filters based on selected product and batch
        if ($product_id) {
            $query->where('product_id', $product_id);
        }
        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        }

        $inventory = $query->paginate(10); // Paginate by 10 items per page

        foreach ($inventory->items() as $item) {
            // Initialize sale count for each batch if not already set
            if (!isset($sale_count[$item->batch_id])) {
                $sale_count[$item->batch_id] = 0;
            }
            if ($item->action_type == 'reduced') {
                $sale_count[$item->batch_id]++;
            }

            $item->quantity_sold = Sale::where('batch_id', $item->batch_id)
                ->where('sale_date', $item->created_at)
                ->pluck('quantity_sold')
                ->first();
            // Assign the sale count from the array to the item
            $item->sale_count = $sale_count[$item->batch_id];

            // Check if the item is the latest for its batch
            $latestInventory = Inventory::where('batch_id', $item->batch_id)
                ->orderBy('created_at', 'desc')
                ->first();
            $item->is_latest = $latestInventory->id == $item->id;

            // If action_type is refunded, get the refund amount from the RefundAmount table
            if ($item->action_type == 'refunded') {
                $refundAmount = RefundAmount::where('inventory_id', $item->id)->first();
                if ($refundAmount) {
                    $item->refunded_amount = (int) $refundAmount->amount; // Cast to integer
                } else {
                    $item->refunded_amount = 0; // No refund amount found
                }
            }
        }

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
            ->where('quantity', '>', 0) // Exclude batches with a quantity of 0
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

    // Helper method to get the sale count for a specific batch
    private function getSaleCount($batch_id)
    {
        return Sale::where('batch_id', $batch_id)->count();
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

        // Generate a base batch ID with the date and initial suffix '01'
        $baseBatchId = Carbon::now()->format('ymd') . '01';
        $newBatchCode = $baseBatchId;

        // Loop to ensure the batch ID is unique
        while (Inventory::where('batch_id', $newBatchCode)->exists()) {
            // Increment the last digit of the batch code
            $lastDigit = (int) substr($newBatchCode, -1);
            $newBatchCode = substr($newBatchCode, 0, -1) . ($lastDigit + 1);
        }

        // Create the inventory record with expiration date and batch_id
        Inventory::create([
            'product_id' => $request->product_id,
            'stock_date' => now(),
            'quantity' => $request->product_quantity,
            'price' => $request->product_price,
            'expiration_date' => $request->expiration_date, // Save expiration date
            'batch_id' => $newBatchCode, // Save batch_id
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

    public function refundView($id)
    {

        // Fetch the inventory and sales record to be refunded
        $sales = Sale::with([
            'inventory_date' => function ($query) {
                $query->where('action_type', 'reduced');
            },
            'product'
        ])
            ->whereHas('inventory_date', function ($query) {
                $query->where('action_type', 'reduced');
            })
            ->where('batch_id', $id)
            ->orderBy('sale_date')
            ->get();

        return view('inventory.refund', compact('sales'));
    }

    public function refund(Request $request)
    {


        $request->validate([
            'id' => 'required|exists:inventories,id',
        ]);

        DB::beginTransaction();

        try {
            // Fetch the inventory record to be refunded
            $inventory = Inventory::with('salesWithBatchId')->findOrFail($request->id);

            // Sum the quantity sold from the related sales
            $quantity_refund = $inventory->sales->sum('quantity_sold');

            $inventory->action_type = 'cancelled';
            $inventory->save();

            // Get latest inventory record for the product
            $latestInventory = Inventory::where('batch_id', $inventory->batch_id)
                ->where('action_type', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->first();

            $refund = Inventory::create([
                'product_id' => $inventory->product_id,
                'stock_date' => $latestInventory->stock_date,
                'quantity' => $quantity_refund + $latestInventory->quantity,
                'price' => $latestInventory->price,
                'expiration_date' => $latestInventory->expiration_date,
                'batch_id' => $inventory->batch_id,
                'cost_price' => $latestInventory->cost_price,
                'action_type' => 'refunded',
            ]);

            $amount = RefundAmount::create([
                'inventory_id' => $refund->id,
                'amount' => $quantity_refund,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Refund successful',
                'id' => $inventory->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Refund failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteProductView()
    {
        $products = Product::all();

        return view('inventory.deleteProduct', compact('products'));
    }

    public function deleteProduct(Request $request)
    {

        $product = Product::findOrFail($request->product_id);

        DB::beginTransaction();

        try {
            // Fetch related inventories
            $inventories = Inventory::where('product_id', $product->id)->get();

            // Delete related notifications, sales, and refunds
            foreach ($inventories as $inventory) {
                Notification::where('inventory_id', $inventory->id)->delete();
                RefundAmount::where('inventory_id', $inventory->id)->delete();
            }

            Sale::where('product_id', $product->id)->delete();
            Inventory::where('product_id', $product->id)->delete();

            // Delete the product
            $product->delete();

            DB::commit();

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBatch($batchId)
    {
        DB::beginTransaction();

        try {
            // Find the inventory records with the given batch ID
            $batches = Inventory::where('batch_id', $batchId)->get();

            // Delete related notifications, sales, and refunds
            foreach ($batches as $batch) {
                Notification::where('inventory_id', $batch->id)->delete();
                RefundAmount::where('inventory_id', $batch->id)->delete();
            }

            Sale::where('batch_id', $batchId)->delete();
            Inventory::where('batch_id', $batchId)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Batch deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete batch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
