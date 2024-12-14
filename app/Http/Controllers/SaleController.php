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
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleController extends Controller
{
    public function recordSale()
    {
        $items = Product::where('isRemoved', false)
            ->orderBy('product_name', 'asc')->get();
        return view('sale.recordSale', compact('items'));
    }

    public function getBatches(Request $request)
    {
        $productId = $request->input('product_id');
        $batches = Inventory::getBatchesByProduct($productId)
            ->where('expiration_date', '>', now())
            ->where('quantity', '>', 0); // Filter out expired batches

        return response()->json([
            'batches' => $batches
        ]);
    }


    public function createSale(Request $request)
    {
        // Initialize arrays for errors and updated products
        $errors = [];
        $updatedProducts = [];

        // Retrieve input arrays, using empty arrays as defaults to avoid undefined index errors
        $products = $request->input('product_id', []);
        $batches = $request->input('batch_id', []);
        $quantities = $request->input('quantity_sold', []);
        $prices = $request->input('price', []);
        $totals = $request->input('total', []);

        DB::beginTransaction();

        try {
            foreach ($products as $index => $productId) {
                // Validate each sale entry manually
                $productError = [];

                // Validate product ID
                if (empty($productId) || !is_numeric($productId) || !Product::find($productId)) {
                    $productError[] = "The selected product is invalid.";
                }

                // Validate batch ID
                if (empty($batches[$index]) || !Inventory::where('batch_id', $batches[$index])->exists()) {
                    $productError[] = "The selected batch is invalid.";
                }

                // Validate quantity sold
                if (empty($quantities[$index]) || !is_numeric($quantities[$index]) || $quantities[$index] < 1) {
                    $productError[] = "The quantity sold must be at least 1.";
                }

                // Validate price
                if (empty($prices[$index]) || !is_numeric($prices[$index]) || $prices[$index] < 0) {
                    $productError[] = "The price must be a valid number.";
                }

                // Validate total
                if (empty($totals[$index]) || !is_numeric($totals[$index]) || $totals[$index] < 0) {
                    $productError[] = "The total amount must be a valid number.";
                }

                // If any validation error exists for this sale, add it to the errors array
                if (!empty($productError)) {
                    $errors[] = $productError; // Add the error messages without referencing index
                    continue; // Skip this sale entry if there's an error
                }

                // Process the sale if no errors exist for this index
                $quantitySold = $quantities[$index];
                $batchId = $batches[$index];
                $price = $prices[$index];
                $total = $totals[$index];

                // Fetch product and inventory (latest created_at record)
                $product = Product::findOrFail($productId);
                $inventory = Inventory::where('product_id', $productId)
                    ->where('batch_id', $batchId)
                    ->latest('created_at') // Fetch the latest record based on created_at
                    ->firstOrFail();

                // Check for sufficient stock
                if ($inventory->quantity < $quantitySold) {
                    $errors[] = ["Insufficient stock in the selected batch for product {$product->product_name}. Please reduce the quantity sold."];
                    continue; // Log error and continue to next sale
                }

                // Calculate revenue, cost, and profit
                $revenue = $quantitySold * $inventory->price;  // Use inventory price for revenue calculation
                $cost = $quantitySold * $inventory->cost_price;  // Use product's cost price
                $profit = $revenue - $cost;

                // Create new inventory record reflecting the current state after sale (track history)
                Inventory::create([
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'expiration_date' => $inventory->expiration_date, // Maintain the same expiration date
                    'quantity' => $inventory->quantity - $quantitySold, // Adjust the quantity
                    'action_type' => 'reduced', // Indicates the stock was reduced
                    'price' => $inventory->price, // Maintain the same price
                    'cost_price' => $inventory->cost_price, // Maintain the same cost price
                    'stock_date' => $inventory->stock_date, // Maintain the original stock date
                ]);

                // Insert sale into the database
                Sale::create([
                    'user_id' => auth()->id(), // Assuming the authenticated user is recording the sale
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'quantity_sold' => $quantitySold,
                    'total_amount' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'sale_date' => now(),
                ]);

                // Track updated product for response
                $updatedProducts[] = $product;
            }

            // If there were any errors, roll back the transaction and return the errors
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Some sales were not processed due to errors.',
                    'errors' => \Illuminate\Support\Arr::flatten($errors), // Flatten the error array for better readability
                ], 409);
            }

            // Commit the transaction if no errors
            DB::commit();

            return response()->json([
                'message' => 'Sale recorded successfully',
                'products' => $updatedProducts,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception for debugging purposes
            \Log::error('Sale creation error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e // Include exception for detailed info
            ]);
            return response()->json([
                'message' => 'An error occurred during processing. Please try again later.',
                'error' => $e->getMessage(), // Return the actual error message
            ], 500);
        }
    }


    public function viewSales(Request $request)
    {
        $staffs = null;
        if (auth()->user()->role == 'Manager') {
            $staffs = User::where('role', 'Staff')->get();
        }

        return view('sale.viewSales', compact('staffs'));
    }

    public function report(Request $request)
    {
        // Validate the date input (year-month-day format)
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        // Extract the day from the input
        $day = $request->input('date');
        $startDate = Carbon::parse($day)->startOfDay();
        $endDate = Carbon::parse($day)->endOfDay();

        // Initialize the query
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->whereHas('inventory_date', function ($query) {
                $query->where('action_type', 'reduced');
            })
            ->with(['product', 'inventory']) // Ensure Sale has relationships defined for Product and Inventory
            ->orderBy('sale_date', 'desc'); // Sort by sale_date in descending order

        // Filter by staff if provided
        if ($request->has('staff') && $request->input('staff') != '') {
            $query->where('user_id', $request->input('staff'));
        }

        if (auth()->user()->role == 'Staff') {
            $query->where('user_id', auth()->id());
        }

        // Get all results
        $sales = $query->get();

        // Filter out records where the related inventory's action_type is not 'reduced'
        $filteredSales = $sales;

        // Paginate the filtered collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $filteredSales->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedSales = new LengthAwarePaginator($currentPageItems, $filteredSales->count(), $perPage);
        $paginatedSales->setPath(request()->url());
        $paginatedSales->appends(request()->query());

        // Calculate summary statistics for total sales, orders, and quantities
        $totalSales = $filteredSales->sum(function ($sale) {
            return $sale->inventory ? $sale->inventory->price * $sale->quantity_sold : 0; // Use inventory price
        });
        $totalOrders = $filteredSales->count(); // Number of orders
        $totalQuantity = $filteredSales->sum('quantity_sold'); // Total quantity of products sold

        // Return the results as JSON with the pagination data
        return response()->json([
            'sales' => $paginatedSales,
            'totalSales' => number_format($totalSales, 2), // Format total sales to 2 decimal places
            'totalOrders' => $totalOrders,
            'totalQuantity' => $totalQuantity,
            'current_page' => $paginatedSales->currentPage(), // Current pagination page
            'last_page' => $paginatedSales->lastPage(), // Last pagination page
            'links' => $paginatedSales->links()->render(), // Optional: HTML pagination links
        ]);
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
    public function saleCount($period = 'week')
    {
        // Get the current period sales count
        $currentSalesCount = $this->getSalesCountForPeriod($period);

        // Get the previous period sales count
        $previousPeriod = $this->getPreviousPeriod($period);
        $previousSalesCount = $this->getSalesCountForPeriod($previousPeriod);

        // Calculate the percentage change
        $percentageChange = $this->calculatePercentageChange($previousSalesCount, $currentSalesCount);

        return response()->json([
            'current_sales_count' => $currentSalesCount,
            'previous_sales_count' => $previousSalesCount,
            'percentage_change' => $percentageChange,
        ]);
    }

    private function getSalesCountForPeriod($period)
    {
        return $this->getSalesData($period, 'count');
    }

    private function getSalesData($period, $type)
    {
        $query = Sale::with('inventory_date')
            ->whereHas('inventory_date', function ($query) {
                $query->where('action_type', 'reduced');
            });
        [$startDate, $endDate] = $this->getDateRangeForPeriod($period);

        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }

        switch ($type) {
            case 'revenue':
                return $query->sum('total_amount');
            case 'profit':
                return $query->sum('profit');
            case 'cost':
                return $query->sum('cost');
            case 'count':
                return $query->count('id');
            default:
                return 0;
        }
    }

    private function getDateRangeForPeriod($period)
    {
        switch ($period) {
            case 'day':
                return [
                    date('Y-m-d 00:00:00'),
                    date('Y-m-d 23:59:59')
                ];
            case 'yesterday':
                return [
                    date('Y-m-d 00:00:00', strtotime('yesterday')),
                    date('Y-m-d 23:59:59', strtotime('yesterday'))
                ];
            case 'week':
                return [
                    date('Y-m-d', strtotime('monday this week')),
                    date('Y-m-d', strtotime('sunday this week'))
                ];
            case 'last_week':
                return [
                    date('Y-m-d', strtotime('monday last week')),
                    date('Y-m-d', strtotime('sunday last week'))
                ];
            case 'month':
                return [date('Y-m-01'), date('Y-m-t')];
            case 'last_month':
                return [
                    date('Y-m-01', strtotime('first day of last month')),
                    date('Y-m-t', strtotime('last day of last month'))
                ];
            case 'year':
                return [date('Y-01-01'), date('Y-12-31')];
            case 'last_year':
                return [
                    date('Y-01-01', strtotime('-1 year')),
                    date('Y-12-31', strtotime('-1 year'))
                ];
            default:
                return [null, null];
        }
    }




    private function getPreviousPeriod($period)
    {
        $map = [
            'day' => 'previous_hour',
            'week' => 'last_week',
            'month' => 'last_month',
            'year' => 'last_year',
        ];

        return $map[$period] ?? 'last_week';
    }



    private function calculatePercentageChange($previousCount, $currentCount)
    {
        if ($previousCount === 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return (($currentCount - $previousCount) / $previousCount) * 100;
    }

    public function revenue($period = 'week')
    {
        $currentRevenue = $this->getSalesData($period, 'revenue');
        $previousRevenue = $this->getSalesData($this->getPreviousPeriod($period), 'revenue');
        $percentageChange = $this->calculatePercentageChange($previousRevenue, $currentRevenue);

        return response()->json([
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'percentage_change' => $percentageChange,
        ]);
    }

    public function profit($period = 'week')
    {
        $currentProfit = $this->getSalesData($period, 'profit');
        $previousProfit = $this->getSalesData($this->getPreviousPeriod($period), 'profit');
        $percentageChange = $this->calculatePercentageChange($previousProfit, $currentProfit);

        return response()->json([
            'current_profit' => $currentProfit,
            'previous_profit' => $previousProfit,
            'percentage_change' => $percentageChange,
        ]);
    }

    public function cost($period = 'week')
    {
        $currentCost = $this->getSalesData($period, 'cost');
        $previousCost = $this->getSalesData($this->getPreviousPeriod($period), 'cost');
        $percentageChange = $this->calculatePercentageChange($previousCost, $currentCost);

        return response()->json([
            'current_cost' => $currentCost,
            'previous_cost' => $previousCost,
            'percentage_change' => $percentageChange,
        ]);
    }

    public function gross($period = 'week')
    {
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Subquery to get the latest inventory record for each batch_id
        $latestInventoriesSubquery = DB::table('inventories as inv')
            ->select('inv.batch_id', DB::raw('MAX(inv.created_at) as latest_created_at'))
            ->groupBy('inv.batch_id');

        $query = Sale::query()
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->joinSub($latestInventoriesSubquery, 'latest_inv', function ($join) {
                $join->on('sales.batch_id', '=', 'latest_inv.batch_id');
            })
            ->join('inventories', function ($join) {
                $join->on('sales.batch_id', '=', 'inventories.batch_id')
                    ->on('inventories.created_at', '=', 'sales.sale_date');
            })
            ->where('inventories.action_type', 'reduced') // Filter based on action_type
            ->selectRaw('SUM(quantity_sold * price) as total_sales, SUM(cost) as total_cost');

        // Adjusting the query based on the period (day, week, month, year)
        switch ($period) {
            case 'day':
                $query->whereDate('sale_date', date('Y-m-d'))
                    ->selectRaw('HOUR(sale_date) as period, COUNT(*) as total_sales_count')
                    ->groupBy(DB::raw('HOUR(sale_date)'))
                    ->orderBy(DB::raw('HOUR(sale_date)'), 'asc');
                break;

            case 'week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

                $query->whereBetween('sale_date', [$startOfWeek, $endOfWeek])
                    ->selectRaw('DAY(sale_date) as period, COUNT(*) as total_sales_count')
                    ->groupBy(DB::raw('DAY(sale_date)'))
                    ->orderBy(DB::raw('DAY(sale_date)'), 'asc');
                break;

            case 'month':
                $startOfMonth = date('Y-m-01');
                $endOfMonth = date('Y-m-t');

                $query->whereBetween('sale_date', [$startOfMonth, $endOfMonth])
                    ->selectRaw('WEEK(sale_date, 1) as period, COUNT(*) as total_sales_count')
                    ->groupBy(DB::raw('WEEK(sale_date, 1)'))
                    ->orderBy(DB::raw('WEEK(sale_date, 1)'), 'asc');
                break;

            case 'year':
                $query->whereYear('sale_date', date('Y'))
                    ->selectRaw('MONTH(sale_date) as period, COUNT(*) as total_sales_count')
                    ->groupBy(DB::raw('MONTH(sale_date)'))
                    ->orderBy(DB::raw('MONTH(sale_date)'), 'asc');
                break;
        }

        $results = $query->get();

        // Map the results to a structured response
        $data = $results->map(function ($item) use ($period) {
            $formattedPeriod = '';

            switch ($period) {
                case 'day':
                    $formattedPeriod = 'Hour ' . $item->period;
                    break;
                case 'week':
                    $formattedPeriod = 'Day ' . $item->period;
                    break;
                case 'month':
                    $formattedPeriod = 'Week ' . $item->period;
                    break;
                case 'year':
                    $formattedPeriod = date('F', mktime(0, 0, 0, $item->period, 10));
                    break;
            }

            return [
                'period' => $formattedPeriod,
                'total_sales_count' => $item->total_sales_count,
                'total_sales' => $item->total_sales,
                'total_cost' => $item->total_cost,
            ];
        });

        return response()->json($data);
    }






    public function topProducts($period = 'week')
    {
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Subquery to get the latest inventory record for each batch_id
        $latestInventoriesSubquery = DB::table('inventories as inv')
            ->select('inv.batch_id', DB::raw('MAX(inv.created_at) as latest_created_at'))
            ->groupBy('inv.batch_id');

        // Main query joining the latest inventories
        $query = Sale::query()
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->joinSub($latestInventoriesSubquery, 'latest_inv', function ($join) {
                $join->on('sales.batch_id', '=', 'latest_inv.batch_id');
            })
            ->join('inventories', function ($join) {
                $join->on('sales.batch_id', '=', 'inventories.batch_id')
                    ->on('inventories.created_at', '=', 'sales.sale_date');
            })
            ->select('sales.product_id', 'products.product_name')
            ->selectRaw('SUM(sales.quantity_sold) as total_quantity_sold, SUM(sales.total_amount) as total_revenue')
            ->where('inventories.action_type', 'reduced') // Filter based on action_type
            ->groupBy('sales.product_id', 'products.product_name')
            ->orderBy('total_revenue', 'desc');

        // Adjust the query based on the selected period
        switch ($period) {
            case 'day':
                $query->whereDate('sales.sale_date', date('Y-m-d'));
                break;

            case 'week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $query->whereBetween('sales.sale_date', [$startOfWeek, $endOfWeek]);
                break;

            case 'month':
                $startOfMonth = date('Y-m-01');
                $endOfMonth = date('Y-m-t');
                $query->whereBetween('sales.sale_date', [$startOfMonth, $endOfMonth]);
                break;

            case 'year':
                $query->whereYear('sales.sale_date', date('Y'));
                break;
        }

        // Limit to top 3 products
        $results = $query->take(10)->get();

        // Format the response
        $data = $results->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_quantity_sold' => $item->total_quantity_sold,
                'total_revenue' => $item->total_revenue,
            ];
        });

        return response()->json($data);
    }


    public function trends($period = 'week')
    {
        // Ensure the requested period is valid
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Subquery to get the latest inventory record for each batch_id
        $latestInventoriesSubquery = DB::table('inventories as inv')
            ->select('inv.batch_id', DB::raw('MAX(inv.created_at) as latest_created_at'))
            ->groupBy('inv.batch_id');

        // Start building the query
        $query = Sale::query()
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->joinSub($latestInventoriesSubquery, 'latest_inv', function ($join) {
                $join->on('sales.batch_id', '=', 'latest_inv.batch_id');
            })
            ->join('inventories', function ($join) {
                $join->on('sales.batch_id', '=', 'inventories.batch_id')
                    ->on('inventories.created_at', '=', 'sales.sale_date');
            })
            ->select('sales.product_id', 'products.product_name')
            ->selectRaw('SUM(sales.quantity_sold) as total_quantity_sold, MAX(inventories.price) as inventory_price');

        // Adjust the query based on the selected period
        switch ($period) {
            case 'day':
                $query->whereDate('sales.sale_date', date('Y-m-d'))
                    ->selectRaw('HOUR(sale_date) as period')
                    ->groupBy('sales.product_id', 'products.product_name', 'period');
                break;

            case 'week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $query->whereBetween('sales.sale_date', [$startOfWeek, $endOfWeek])
                    ->selectRaw('DATE(sale_date) as period')
                    ->groupBy('sales.product_id', 'products.product_name', 'period');
                break;

            case 'month':
                $startOfMonth = date('Y-m-01');
                $endOfMonth = date('Y-m-t');
                $query->whereBetween('sales.sale_date', [$startOfMonth, $endOfMonth])
                    ->selectRaw('WEEK(sale_date) as period')
                    ->groupBy('sales.product_id', 'products.product_name', 'period');
                break;

            case 'year':
                $query->whereYear('sales.sale_date', date('Y'))
                    ->selectRaw('MONTH(sale_date) as period')
                    ->groupBy('sales.product_id', 'products.product_name', 'period');
                break;
        }

        // Execute the query and get the results
        $results = $query->get();

        // Group the results by product and period
        $groupedResults = $results->groupBy('product_name');

        // Calculate the total quantity sold for each product and sort by it
        $sortedResults = $groupedResults->map(function ($items, $productName) {
            $totalQuantitySold = $items->sum('total_quantity_sold');
            return [
                'product_name' => $productName,
                'total_quantity_sold' => $totalQuantitySold,
                'data' => $items->map(function ($item) {
                    return [
                        'period' => $item->period,
                        'total_quantity_sold' => (float) $item->total_quantity_sold,
                        'inventory_price' => (float) $item->inventory_price,
                    ];
                })
            ];
        })->sortByDesc('total_quantity_sold')->take(5);

        // Format the data for the line graph
        $formattedData = $sortedResults->values()->map(function ($item) {
            return [
                'product_name' => $item['product_name'],
                'data' => $item['data']
            ];
        });

        // Return the results as JSON
        return response()->json($formattedData);
    }


    public function netProfit($period = 'week')
    {
        // Ensure the requested period is valid
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Initialize the query to sum profits and join products and inventories tables
        $query = Sale::with(['product', 'inventory_date'])
            ->whereHas('inventory_date', function ($query) {
                $query->where('action_type', 'reduced');
            })
            ->selectRaw('SUM(profit) as total_profit');

        // Apply date filters and period grouping based on the selected period
        switch ($period) {
            case 'day':
                $query->whereDate('sale_date', Carbon::today())
                    ->selectRaw('HOUR(sale_date) as period')
                    ->groupBy(DB::raw('HOUR(sale_date)'))
                    ->orderBy(DB::raw('HOUR(sale_date)'), 'asc');
                break;

            case 'week':
                $query->whereBetween('sale_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                    ->selectRaw('DAY(sale_date) as period')
                    ->groupBy(DB::raw('DAY(sale_date)'))
                    ->orderBy(DB::raw('DAY(sale_date)'), 'asc');
                break;

            case 'month':
                $query->whereYear('sale_date', Carbon::now()->year)
                    ->whereMonth('sale_date', Carbon::now()->month)
                    ->selectRaw('DAY(sale_date) as period')
                    ->groupBy(DB::raw('DAY(sale_date)'))
                    ->orderBy(DB::raw('DAY(sale_date)'), 'asc');
                break;

            case 'year':
                $query->whereYear('sale_date', '>=', Carbon::now()->year - 1)
                    ->selectRaw('MONTH(sale_date) as period')
                    ->groupBy(DB::raw('MONTH(sale_date)'))
                    ->orderBy(DB::raw('MONTH(sale_date)'), 'asc');
                break;
        }

        // Execute the query and get the results
        $results = $query->get();

        // Format the results for the response
        $data = $results->map(function ($item) use ($period) {
            switch ($period) {
                case 'day':
                    $formattedPeriod = 'Hour ' . $item->period;
                    break;

                case 'week':
                    $formattedPeriod = 'Day ' . $item->period;
                    break;

                case 'month':
                    $formattedPeriod = 'Day ' . $item->period;
                    break;

                case 'year':
                    $formattedPeriod = date('F', mktime(0, 0, 0, $item->period, 10));
                    break;
            }

            return [
                'period' => $formattedPeriod,
                'total_profit' => (float) $item->total_profit, // Ensure accurate float conversion
            ];
        });

        // Return the formatted data as JSON
        return response()->json($data);
    }


    public function predict($period = 'week')
    {
        // Get all products
        $products = Product::all();
        $predictions = [];

        // Get today's date
        $currentDate = now()->format('Y-m-d');
        $targetDate = now()->addWeek()->format('Y-m-d'); // Use this for week, month, year

        foreach ($products as $product) {
            // Initialize salesData to null
            $salesData = null;

            // Get sales data for each product based on the period
            switch ($period) {
                case 'day':
                    // Get today's sales data
                    $todaySales = Sale::where('product_id', $product->id)
                        ->whereDate('sale_date', $currentDate)
                        ->sum('quantity_sold');

                    // If no sales today, return a prediction message
                    if ($todaySales == 0) {
                        $predictions[$product->id] = [
                            'product_name' => $product->product_name,
                            'predicted_sales' => 0,
                            'message' => 'No sales data available for today, prediction not possible',
                        ];
                        break; // Skip further processing for this product
                    }

                    // Get sales data for the last 7 days
                    $salesData = Sale::where('product_id', $product->id)
                        ->select(DB::raw('SUM(quantity_sold) as total_quantity_sold'), DB::raw('DATE(sale_date) as sale_period'))
                        ->whereBetween('sale_date', [now()->subDays(7), now()])
                        ->groupBy('sale_period')
                        ->orderBy('sale_period')
                        ->get();
                    break;

                case 'week':
                    // Get the start and end date of the current week (Monday to Sunday)
                    $startOfWeek = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                    $endOfWeek = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

                    // Get sales data for the current week (grouped by day)
                    $salesData = Sale::where('product_id', $product->id)
                        ->select(DB::raw('SUM(quantity_sold) as total_quantity_sold'), DB::raw('DATE(sale_date) as sale_period'))
                        ->whereBetween('sale_date', [$startOfWeek, $endOfWeek])
                        ->groupBy('sale_period')
                        ->orderBy('sale_period')
                        ->get();

                    // Check if there's no sales data for the current week
                    if ($salesData->isEmpty()) {
                        // Skip prediction for this product
                        $predictions[$product->id] = [
                            'product_name' => $product->product_name,
                            'predicted_sales' => 0,  // No prediction possible
                            'message' => 'No sales data for the current week, prediction not possible',
                        ];
                        break; // Skip further processing for this product
                    }

                    // Process sales data for the current week and predict for the next week
                    $periodicSales = [];
                    foreach ($salesData as $sale) {
                        $periodicSales[$sale->sale_period] = $sale->total_quantity_sold;
                    }

                    // Predict sales for the next week using current week's data
                    if (!empty($periodicSales)) {
                        $predictedSales = $this->predictFutureSales($periodicSales, $period);
                        $predictions[$product->id] = [
                            'product_name' => $product->product_name,
                            'predicted_sales' => $predictedSales['next_week'],
                            'target_date' => $startOfWeek,  // Or the start date of next week
                        ];
                    }
                    break;



                case 'month':
                    // Get sales data grouped by month for the last 3 months
                    $salesData = Sale::where('product_id', $product->id)
                        ->select(DB::raw('SUM(quantity_sold) as total_quantity_sold'), DB::raw('MONTH(sale_date) as sale_period'))
                        ->whereBetween('sale_date', [now()->subMonths(3), now()])
                        ->groupBy('sale_period')
                        ->orderBy('sale_period')
                        ->get();
                    break;

                case 'year':
                    // Get sales data grouped by year for the last year
                    $salesData = Sale::where('product_id', $product->id)
                        ->select(DB::raw('SUM(quantity_sold) as total_quantity_sold'), DB::raw('YEAR(sale_date) as sale_period'))
                        ->whereBetween('sale_date', [now()->subYear(), now()])
                        ->groupBy('sale_period')
                        ->orderBy('sale_period')
                        ->get();
                    break;
            }

            // Prepare data for prediction
            $periodicSales = [];
            if (!empty($salesData)) {
                foreach ($salesData as $sale) {
                    $periodicSales[$sale->sale_period] = $sale->total_quantity_sold;
                }

                // Predict sales for the specified period
                if (!empty($periodicSales)) {
                    $predictedSales = $this->predictFutureSales($periodicSales, $period);

                    // Store predictions
                    $predictions[$product->id] = [
                        'product_name' => $product->product_name,
                        'predicted_sales' => $predictedSales,
                        'target_date' => ($period === 'day') ? $currentDate : $targetDate,
                    ];
                }
            } else {
                // Handle case when salesData is empty
                $predictions[$product->id] = [
                    'product_name' => $product->product_name,
                    'predicted_sales' => [],
                    'target_date' => ($period === 'day') ? $currentDate : $targetDate,
                ];
            }
        }
        $predictions = array_slice($predictions, 0, 15); // Limit to 15 products

        return response()->json($predictions);
    }

    /**
     * Calculate future sales predictions based on historical data.
     *
     * @param array $periodicSales
     * @param string $period
     * @return array
     */
    private function predictFutureSales(array $periodicSales, string $period)
    {
        $predictedSales = [];

        switch ($period) {
            case 'day':
                // Predict sales for tomorrow using the average of the last seven days
                $lastSevenDays = array_slice($periodicSales, -7, null, true);
                $averageSales = count($lastSevenDays) > 0 ? array_sum($lastSevenDays) / count($lastSevenDays) : 0;
                $predictedSales['next_day'] = round($averageSales);
                break;

            case 'week':
                // Predict sales for the next week using the average of the last four weeks
                $lastFourWeeks = array_slice($periodicSales, -4, null, true);
                $averageSales = count($lastFourWeeks) > 0 ? array_sum($lastFourWeeks) / count($lastFourWeeks) : 0;
                $predictedSales['next_week'] = round($averageSales);
                break;

            case 'month':
                // Predict sales for the next month using the average of the last three months
                $lastThreeMonths = array_slice($periodicSales, -3, null, true);
                $averageSales = count($lastThreeMonths) > 0 ? array_sum($lastThreeMonths) / count($lastThreeMonths) : 0;
                $predictedSales['next_month'] = round($averageSales);
                break;

            case 'year':
                // Predict sales for the next year using the average monthly sales
                $averageSales = count($periodicSales) > 0 ? array_sum($periodicSales) / count($periodicSales) : 0;
                $predictedSales['next_year'] = round($averageSales * 12);
                break;
        }

        return $predictedSales;
    }


    public function stock()
    {
        // Start the query, joining with the related product table
        $query = Inventory::with('product')  // Load the related 'product' data
            ->wherehas('product', function ($query) {
                $query->where('isRemoved', false);
            })
            ->select('inventories.*')  // Select all columns from the 'inventories' table
            ->join(
                DB::raw('(SELECT MAX(created_at) as max_created_at, batch_id FROM inventories GROUP BY batch_id) as latest_batches'),
                function ($join) {
                    $join->on('inventories.batch_id', '=', 'latest_batches.batch_id')
                        ->on('inventories.created_at', '=', 'latest_batches.max_created_at');
                }
            )
            ->join('products', 'products.id', '=', 'inventories.product_id') // Join with products table
            ->where('inventories.action_type', '!=', 'removed') // Filter based on action_type
            ->where('inventories.expiration_date', '>', now()); // Filter out expired inventory

        // Get all results without pagination, searches, or sorting
        $batches = $query->get();

        // Attach the count of related sales data to each inventory batch
        foreach ($batches as $batch) {
            $batch->quantity_sold = Sale::where('batch_id', $batch->batch_id)
                ->sum('quantity_sold');
        }

        // Return the batch data along with sales count
        return response()->json([
            'data' => $batches,  // The actual batch data
        ]);
    }

    public function salesReport(Request $request)
    {
        // Validate the date input (year-month-day format)
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);


        // Extract the day from the input
        $day = $request->input('date');
        $startDate = Carbon::parse($day)->startOfDay();
        $endDate = Carbon::parse($day)->endOfDay();

        // Query sales data based on the provided day, include related product and inventory details
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->whereHas('inventory_date', function ($query) {
                $query->where('action_type', 'reduced');
            })
            ->with(['product', 'inventory']) // Ensure Sale has relationships defined for Product and Inventory
            ->orderBy('sale_date', 'desc') // Sort by sale_date in descending order
            ->get(); // Get all results

        // Filter out records where the related inventory's action_type is not 'reduced'
        $filteredSales = $sales;

        // Calculate summary statistics for total sales, orders, and quantities
        $totalSales = $filteredSales->sum(function ($sale) {
            return $sale->inventory ? $sale->inventory->price * $sale->quantity_sold : 0; // Use inventory price
        });
        $totalOrders = $filteredSales->count(); // Number of orders
        $totalQuantity = $filteredSales->sum('quantity_sold'); // Total quantity of products sold

        // Return the results as JSON without pagination data
        return response()->json([
            'sales' => $filteredSales->values(), // Return all filtered sales
            'totalSales' => number_format($totalSales, 2), // Format total sales to 2 decimal places
            'totalOrders' => $totalOrders,
            'totalQuantity' => $totalQuantity,
        ]);
    }


}
