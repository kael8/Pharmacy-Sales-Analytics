<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title">Predict Future Sales</h4>
        </div>
        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownSalesPredict" data-bs-toggle="dropdown"
                aria-expanded="false">
                This week
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSalesPredict">
                <li class="dropdown-item-dash" data-period="day">Next Day</li>
                <li class="dropdown-item-dash" data-period="week">Next Week</li>
                <li class="dropdown-item-dash" data-period="month">Next Month</li>
                <li class="dropdown-item-dash" data-period="year">Next Year</li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;"><!-- Loading Spinner -->




            <canvas id="salesPredictChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1"></script> <!-- Zoom plugin -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let salesTrendsChart;

        // Function to update the sales trends chart based on the selected period
        const updateSalesTrendsChart = (salesData, period) => {
            document.getElementById('dropdownSalesPredict').textContent = `Next ${period}`;

            const labels = Object.values(salesData).map(dataPoint => dataPoint.product_name);
            const data = Object.values(salesData).map(dataPoint => {
                switch (period) {
                    case 'day': return dataPoint.predicted_sales.next_day;
                    case 'week': return dataPoint.predicted_sales.next_week;
                    case 'month': return dataPoint.predicted_sales.next_month;
                    case 'year': return dataPoint.predicted_sales.next_year;
                    default: return 0;
                }
            });

            const backgroundColors = labels.map((_, index) => {
                const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];
                return colors[index % colors.length];
            });

            if (salesTrendsChart) {
                salesTrendsChart.data.labels = labels;
                salesTrendsChart.data.datasets[0].data = data;
                salesTrendsChart.data.datasets[0].label = `Predicted Sales for Next ${period.charAt(0).toUpperCase() + period.slice(1)}`;
                salesTrendsChart.data.datasets[0].backgroundColor = backgroundColors;
                salesTrendsChart.update();
            } else {
                const ctx = document.getElementById('salesPredictChart').getContext('2d');
                salesTrendsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `Predicted Sales for Next ${period.charAt(0).toUpperCase() + period.slice(1)}`,
                            data: data,
                            backgroundColor: backgroundColors,
                            borderColor: '#007bff',
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                enabled: true
                            },
                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: 'x'
                                },
                                zoom: {
                                    enabled: true,
                                    mode: 'x'
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Product'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Predicted Sales Quantity'
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
                url: '{{ url("/sale/predict") }}/' + period,
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