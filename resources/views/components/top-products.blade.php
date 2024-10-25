<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title">Top Products</h4>

        </div>
        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownTopProducts" data-bs-toggle="dropdown"
                aria-expanded="false">
                This week
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownTopProducts">
                <li class="dropdown-item-dash" data-period="day">This Day</li>
                <li class="dropdown-item-dash" data-period="week">This Week</li>
                <li class="dropdown-item-dash" data-period="month">This Month</li>
                <li class="dropdown-item-dash" data-period="year">This Year</li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let topProductsChart;

        // Predefined list of Bootstrap colors
        const bootstrapColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];

        // Function to get color for a specific category
        const getCategoryColor = (index) => {
            return bootstrapColors[index % bootstrapColors.length];
        };

        // Function to update the chart
        const updateChart = (productData, period) => {
            document.getElementById('dropdownTopProducts').textContent = `This ${period}`;

            // Create dataset for each product
            const datasets = productData.map((dataPoint, index) => ({
                label: dataPoint.product_name,
                data: [parseFloat(dataPoint.total_revenue)],
                backgroundColor: getCategoryColor(index), // Unique solid color for each product
            }));

            // Adjust labels to match the data
            const labels = ['Products']; // Single label for the y-axis

            const backgroundColorPlugin = {
                id: 'customCanvasBackgroundColor',
                beforeDraw: (chart) => {
                    const ctx = chart.canvas.getContext('2d');
                    ctx.save();
                    ctx.globalCompositeOperation = 'destination-over';
                    ctx.fillStyle = 'white'; // Set background color here
                    ctx.fillRect(0, 0, chart.width, chart.height);
                    ctx.restore();
                }
            };

            if (topProductsChart) {
                topProductsChart.data.labels = labels; // Update labels
                topProductsChart.data.datasets = datasets; // Update datasets
                topProductsChart.update();
            } else {
                const ctx = document.getElementById('topProductsChart').getContext('2d'); // Ensure correct canvas element

                topProductsChart = new Chart(ctx, {
                    type: 'bar', // Use 'bar' for a bar chart
                    data: {
                        labels: labels, // Single label for the y-axis
                        datasets: datasets // Multiple datasets for each product
                    },
                    options: {
                        scales: {
                            x: {
                                // Control bar width
                                barPercentage: 0.5, // Adjust to fit bars within the width
                                categoryPercentage: 1.0 // Full width for each category
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Revenue (₱)'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.dataset.label || '';
                                        return `${label}: ₱${context.raw.toFixed(2)}`; // Format as currency
                                    }
                                }
                            }
                        }
                    },
                    plugins: [backgroundColorPlugin]
                });
            }
        };

        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                const period = this.getAttribute('data-period');
                fetchTopProducts(period);
            });
        });

        const fetchTopProducts = (period) => {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ url("/sale/top") }}/' + period,
                type: 'GET',
                success: (response) => {
                    updateChart(response, period); // Pass response to update chart
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                    const response = JSON.parse(xhr.responseText);
                    const errorMessage = response.message + "\n" + response.errors.join("\n");
                    alert(errorMessage);
                }
            });
        };

        fetchTopProducts('week'); // Default period
    });
</script>