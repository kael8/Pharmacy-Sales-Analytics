<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title">Sales Trends</h4>
        </div>
        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownSalesTrends" data-bs-toggle="dropdown"
                aria-expanded="false">
                This day
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSalesTrends">
                <li class="dropdown-item-dash" data-period="day">This Day</li>
                <li class="dropdown-item-dash" data-period="week">This Week</li>
                <li class="dropdown-item-dash" data-period="month">This Month</li>
                <li class="dropdown-item-dash" data-period="year">This Year</li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <canvas id="salesTrendsChart"></canvas>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let salesTrendsChart;

        const bootstrapColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];

        const getCategoryColor = (index) => bootstrapColors[index % bootstrapColors.length];

        const updateSalesTrendsChart = (salesData, period) => {
            document.getElementById('dropdownSalesTrends').textContent = `This ${period}`;

            // Get all unique periods
            const periods = [...new Set(Object.values(salesData).flatMap(product => product.data.map(item => item.period)))].sort((a, b) => a - b);
            const datasets = Object.values(salesData).map((product, index) => {
                return {
                    label: product.product_name,
                    data: periods.map(period => {
                        const item = product.data.find(d => d.period === period);
                        return item ? item.total_quantity_sold : 0;
                    }),
                    borderColor: getCategoryColor(index),
                    backgroundColor: getCategoryColor(index), // Add transparency to the fill color

                };
            });

            const canvas = document.getElementById('salesTrendsChart');

            if (salesTrendsChart) {
                salesTrendsChart.destroy(); // Destroy the existing chart instance
            }

            const ctx = canvas.getContext('2d');
            salesTrendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: periods,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const productName = context.dataset.label;
                                    const totalQuantitySold = context.raw;
                                    const periodLabel = context.label;
                                    console.log(`Context:`, context);
                                    console.log(`Product: ${productName}, Period: ${periodLabel}`);
                                    const productData = Object.values(salesData).find(product => product.product_name === productName).data.find(d => d.period.toString() === periodLabel);
                                    console.log(`Product Data:`, productData);
                                    const inventoryPrice = productData ? productData.inventory_price : 'N/A';
                                    return `${productName}: ${totalQuantitySold} units sold, Price: $${inventoryPrice}`;
                                }
                            }
                        }
                    }
                }
            });
        };

        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                const period = this.getAttribute('data-period');
                fetchSalesTrends(period);
            });
        });

        const fetchSalesTrends = (period) => {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ url("/sale/trends") }}/' + period,
                type: 'GET',
                success: (response) => {
                    console.log('Sales Data:', response); // Log the sales data
                    updateSalesTrendsChart(response, period);
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                    const response = JSON.parse(xhr.responseText);
                    const errorMessage = response.message + "\n" + response.errors.join("\n");
                    alert(errorMessage);
                }
            });
        };

        fetchSalesTrends('day');
    });
</script>