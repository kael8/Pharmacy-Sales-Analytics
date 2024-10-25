<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title">Sales Trends</h4>
        </div>
        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownSalesTrends" data-bs-toggle="dropdown"
                aria-expanded="false">
                This week
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

        // Predefined list of Bootstrap colors excluding gray
        const bootstrapColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8']; // Primary, Success, Danger, Warning, Info

        // Function to get color for a specific category
        const getCategoryColor = (index) => {
            return bootstrapColors[index % bootstrapColors.length];
        };

        // Function to update the sales trends chart as a pie chart
        const updateSalesTrendsChart = (salesData, period) => {
            // Update the dropdown label with the selected period
            document.getElementById('dropdownSalesTrends').textContent = `This ${period}`;

            // Aggregate sales data
            const productTotals = salesData.reduce((acc, dataPoint) => {
                if (!acc[dataPoint.product_name]) {
                    acc[dataPoint.product_name] = {
                        total_quantity_sold: 0,
                        inventory_price: dataPoint.inventory_price
                    };
                }
                acc[dataPoint.product_name].total_quantity_sold += parseInt(dataPoint.total_quantity_sold);
                return acc;
            }, {});

            // Prepare data for the pie chart
            const labels = Object.keys(productTotals);
            const data = labels.map(label => productTotals[label].total_quantity_sold);
            const backgroundColors = labels.map((_, index) => getCategoryColor(index));

            // Initialize or update the pie chart
            if (salesTrendsChart) {
                salesTrendsChart.data.labels = labels;
                salesTrendsChart.data.datasets[0].data = data;
                salesTrendsChart.data.datasets[0].backgroundColor = backgroundColors;
                salesTrendsChart.update();
            } else {
                const ctx = document.getElementById('salesTrendsChart').getContext('2d');
                salesTrendsChart = new Chart(ctx, {
                    type: 'pie', // Set the chart type to pie
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors
                        }]
                    },
                    options: {
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const productName = context.label;
                                        const totalQuantitySold = context.raw;
                                        const inventoryPrice = productTotals[productName].inventory_price;
                                        return `${totalQuantitySold} units sold, Price: $${inventoryPrice}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };

        // Event listener for period selection
        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                const period = this.getAttribute('data-period');
                fetchSalesTrends(period);
            });
        });

        // Function to fetch sales trends from the server
        const fetchSalesTrends = (period) => {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ url("/sale/trends") }}/' + period,
                type: 'GET',
                success: (response) => {
                    updateSalesTrendsChart(response, period); // Pass response data to update chart
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                    const response = JSON.parse(xhr.responseText);
                    const errorMessage = response.message + "\n" + response.errors.join("\n");
                    alert(errorMessage);
                }
            });
        };

        // Default chart load for 'week' period
        fetchSalesTrends('week');
    });
</script>