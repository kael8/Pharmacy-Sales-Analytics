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
        <!-- Percentages section -->
        <div id="salesPercentages" class="mt-3"></div>
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

            const labels = Object.keys(productTotals);
            const data = labels.map(label => productTotals[label].total_quantity_sold);
            const backgroundColors = labels.map((_, index) => getCategoryColor(index));
            const totalSales = data.reduce((a, b) => a + b, 0);

            // Calculate and display percentages below the chart
            const percentageDescriptions = labels.map((label, index) => {
                const percentage = ((data[index] / totalSales) * 100).toFixed(2);
                return `<span style="font-size: 12px; margin: 0;">${label}: ${percentage}%</span><br>`;
            }).join('');
            document.getElementById('salesPercentages').innerHTML = percentageDescriptions;

            const canvas = document.getElementById('salesTrendsChart');

            if (salesTrendsChart) {
                salesTrendsChart.data.labels = labels;
                salesTrendsChart.data.datasets[0].data = data;
                salesTrendsChart.data.datasets[0].backgroundColor = backgroundColors;
                salesTrendsChart.update();
            } else {
                const ctx = canvas.getContext('2d');
                salesTrendsChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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