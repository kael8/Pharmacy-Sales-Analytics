<x-app-layout :assets="$assets ?? []">
    <div class="container mt-4">
        <!-- Header -->
        <div class="header mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title text-center">Sales Report</h1>
                    <form id="filterForm" class="form-inline">
                        <div class="row w-100">
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group w-100">
                                    <label for="start-date" class="mr-2">Start Date:</label>
                                    <input type="date" id="start-date" name="start_date" class="form-control w-100"
                                        value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group w-100">
                                    <label for="end-date" class="mr-2">End Date:</label>
                                    <input type="date" id="end-date" name="end_date" class="form-control w-100"
                                        value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group w-100">
                                    <label class="d-block">&nbsp;</label> <!-- Empty label for alignment -->
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 col-sm-12 mb-3">
                            <h5 id="totalSales" class="card-text">Total Sales: ₱{{ number_format($totalSales, 2) }}</h5>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3">
                            <h5 id="totalOrders" class="card-text">Total Orders: {{ $totalOrders }}</h5>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3">
                            <h5 id="totalQuantity" class="card-text">Total Quantity Sold: {{ $totalQuantity }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Sales Data -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h4 class="card-title">Sales Details</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ $sale->formatted_sale_date = date('Y-m-d', strtotime($sale->sale_date)) }}</td>
                                    <td>{{ $sale->product->product_name }}</td>
                                    <td>{{ $sale->quantity_sold }}</td>
                                    <td>₱{{ number_format($sale->product->price, 2) }}</td>
                                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charts and Graphs -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="charts">
                    <button id="downloadChart" class="btn btn-success btn-lg mb-3">
                        <i class="fas fa-download"></i> Download Chart
                    </button>
                    <div style="overflow-x: auto;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</x-app-layout>
<script>

    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('salesChart').getContext('2d');
        let salesChart;

        const getRandomColor = () => {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        };

        const aggregateSalesByMonth = (salesData) => {
            return salesData.reduce((acc, sale) => {
                const date = new Date(sale.sale_date);
                const yearMonth = `${date.getFullYear()}-${date.getMonth() + 1}`; // Format YYYY-MM

                if (!acc[yearMonth]) {
                    acc[yearMonth] = {};
                }

                if (!acc[yearMonth][sale.product.product_name]) {
                    acc[yearMonth][sale.product.product_name] = 0;
                }

                acc[yearMonth][sale.product.product_name] += parseFloat(sale.total_amount);
                return acc;
            }, {});
        };

        const updateChart = (salesData) => {
            // Aggregate sales data by month
            const aggregatedData = aggregateSalesByMonth(salesData);

            // Group sales data by product and month
            const products = new Set(); // To track unique product names
            Object.keys(aggregatedData).forEach(month => {
                Object.keys(aggregatedData[month]).forEach(productName => {
                    products.add(productName);
                });
            });

            const datasets = Array.from(products).map(productName => {
                const dataPoints = [];

                Object.keys(aggregatedData).forEach(month => {
                    const monthData = aggregatedData[month];
                    dataPoints.push({
                        x: new Date(`${month}-01`).toISOString(), // Use ISO date format
                        y: monthData[productName] || 0 // Default to 0 if no sales data
                    });
                });

                const color = getRandomColor(); // Generate a single color for both fill and border

                return {
                    label: productName,
                    data: dataPoints,
                    backgroundColor: color, // Solid background color
                    borderColor: color, // Same color for border
                    borderWidth: 3, // Thicker lines
                    tension: 0.1, // Curved lines
                    fill: true // Ensure bars are filled
                };
            });

            // Plugin to add background color to the chart area
            const backgroundColorPlugin = {
                id: 'customCanvasBackgroundColor',
                beforeDraw: (chart) => {
                    const ctx = chart.canvas.getContext('2d');
                    ctx.save();
                    ctx.globalCompositeOperation = 'destination-over';
                    ctx.fillStyle = 'white'; // Set your desired background color here
                    ctx.fillRect(0, 0, chart.width, chart.height);
                    ctx.restore();
                }
            };

            // Create or update the chart
            if (salesChart) {
                salesChart.data.datasets = datasets;
                salesChart.update();
            } else {
                salesChart = new Chart(ctx, {
                    type: 'bar', // Bar chart
                    data: { datasets },
                    options: {
                        scales: {
                            x: {
                                type: 'time',
                                time: { unit: 'month' },
                                title: { display: true, text: 'Month' }
                            },
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Total Amount (₱)' }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.dataset.label || '';
                                        return `${label ? `${label}: ` : ''}₱${context.raw.y.toFixed(2)}`; // Show total_amount in tooltips
                                    }
                                }
                            }
                        }
                    },
                    plugins: [backgroundColorPlugin]
                });
            }
        };

        // Initial chart rendering
        const initialSalesData = @json($sales);
        updateChart(initialSalesData);

        // Function to download the chart as an image
        document.getElementById('downloadChart').addEventListener('click', () => {
            const link = document.createElement('a');
            link.href = salesChart.toBase64Image();
            link.download = 'sales_chart.png';
            link.click();
        });

        // AJAX form submission
        document.getElementById('filterForm').addEventListener('submit', (event) => {
            event.preventDefault();
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ route('report') }}',
                type: 'POST',
                data: { start_date: startDate, end_date: endDate },
                success: (response) => {
                    // Update summary statistics
                    document.getElementById('totalSales').textContent = `Total Sales: ₱${response.totalSales.toFixed(2)}`;
                    document.getElementById('totalOrders').textContent = `Total Orders: ${response.totalOrders}`;
                    document.getElementById('totalQuantity').textContent = `Total Quantity Sold: ${response.totalQuantity}`;

                    // Update sales table
                    const salesTableBody = document.getElementById('salesTableBody');
                    salesTableBody.innerHTML = response.sales.map(sale => `
                        <tr>
                            <td>${sale.id}</td>
                            <td>${new Date(sale.sale_date).toISOString().split('T')[0]}</td>
                            <td>${sale.product.product_name}</td>
                            <td>${sale.quantity_sold}</td>
                            <td>₱${parseFloat(sale.product.price).toFixed(2)}</td>
                            <td>₱${parseFloat(sale.total_amount).toFixed(2)}</td>
                        </tr>
                    `).join('');

                    // Update chart
                    console.log(response.sales);
                    updateChart(response.sales);
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                    const response = JSON.parse(xhr.responseText);
                    const errorMessage = response.message + "\n" + response.errors.join("\n");
                    alert(errorMessage);
                }
            });
        });
    });

</script>