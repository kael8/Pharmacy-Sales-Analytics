<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use DateTime;
use App\Models\Analytic;

class SaleController extends Controller
{
    public function recordSale()
    {
        $items = Product::all();
        return view('sale.recordSale', compact('items'));
    }

    public function createSale(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'product_id.*' => 'required|numeric|exists:products,id', // Ensure each product_id is numeric and exists
            'quantity_sold.*' => 'required|numeric|min:1', // Ensure each quantity_sold is numeric and at least 1
        ]);

        $products = $request->input('product_id');
        $quantities = $request->input('quantity_sold');
        $errors = [];
        $updatedProducts = [];

        DB::beginTransaction();

        try {
            foreach ($products as $index => $productId) {
                $quantitySold = $quantities[$index];
                $product = Product::find($productId);

                if (!$product) {
                    $errors[] = "Product with ID $productId not found.";
                    throw new \Exception("Product with ID $productId not found.");
                }

                if ($product->quantity_in_stock < $quantitySold) {
                    $errors[] = "Insufficient stock for product ID $productId.";
                    throw new \Exception("Insufficient stock for product ID $productId.");
                }

                // Update stock
                $product->quantity_in_stock -= $quantitySold;
                $product->save();
                $updatedProducts[] = $product;

                // Insert into inventory
                Inventory::create([
                    'product_id' => $productId,
                    'quantity' => $product->quantity_in_stock,
                    'price' => $product->price,
                    'stock_date' => now(),
                ]);

                // Insert into sales
                Sale::create([
                    'product_id' => $productId,
                    'user_id' => auth()->id(),
                    'quantity_sold' => $quantitySold,
                    'total_amount' => $quantitySold * $product->price,
                    'sale_date' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Errors occurred during processing',
                'errors' => $errors,
            ], 409);
        }

        return response()->json([
            'message' => 'Sale recorded successfully',
            'products' => $updatedProducts,
        ], 201);
    }

    public function viewSales(Request $request)
    {
        // Validate date range
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Default date range: last 30 days
        $startDate = $request->input('start_date', (new DateTime('-30 days'))->format('Y-m-d'));
        $endDate = $request->input('end_date', (new DateTime())->format('Y-m-d'));

        // Query sales data
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->with('product') // Assuming a Sale has a Product relationship
            ->get();

        // Calculate summary statistics
        $totalSales = $sales->sum('total_amount');
        $totalOrders = $sales->count();
        $totalQuantity = $sales->sum('quantity_sold');

        return view('sale.viewSales', compact('sales', 'totalSales', 'totalOrders', 'totalQuantity', 'startDate', 'endDate'));
    }

    public function report(Request $request)
    {
        // Validate date range
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Default date range: last 30 days
        $startDate = $request->input('start_date', (new DateTime('-30 days'))->format('Y-m-d'));
        $endDate = $request->input('end_date', (new DateTime())->format('Y-m-d'));

        // Query sales data
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with('product') // Assuming a Sale has a Product relationship
            ->get();

        // Calculate summary statistics
        $totalSales = $sales->sum('total_amount');
        $totalOrders = $sales->count();
        $totalQuantity = $sales->sum('quantity_sold');

        if ($request->ajax()) {
            return response()->json([
                'sales' => $sales,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'totalQuantity' => $totalQuantity,
            ]);
        }

    }

    public function viewSalesInsight()
    {
        return view('sale.getSalesInsight');
    }

    public function generateSalesInsights(Request $request)
    {
        // Fetch sales data with related product information
        $query = Sale::with('product');

        // Filter by selected month
        if ($request->has('month')) {
            $month = $request->input('month');
            $query->whereYear('sale_date', '=', date('Y', strtotime($month)))
                ->whereMonth('sale_date', '=', date('m', strtotime($month)));
        }

        $sales = $query->get();

        // Process sales data to generate insights
        $insights = $this->processSalesData($sales);

        // Store insights in the analytics table
        //$this->storeSalesInsights($insights);

        // Check if the request is an AJAX request
        if ($request->ajax()) {
            return response()->json(['insights' => $insights]);
        }

        // Return a view to display the insights
        return view('sale.getSalesInsight', compact('insights'));
    }

    private function processSalesData($sales)
    {
        // Example: Aggregate total sales amount by product
        $insights = $sales->groupBy('product_id')->map(function ($salesGroup) {
            return [
                'product_name' => $salesGroup->first()->product->product_name,
                'total_sales' => $salesGroup->sum('total_amount'),
                'total_quantity' => $salesGroup->sum('quantity_sold'),
            ];
        })->values();

        return $insights;
    }

    private function storeSalesInsights($insights)
    {
        // Store insights in the analytics table
        Analytic::create([
            'user_id' => auth()->id(),
            'analysis_type' => 'sales_insights',
            'results' => $insights->toJson(),
        ]);
    }

}
