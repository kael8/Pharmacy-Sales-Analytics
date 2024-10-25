<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title" id="totalGrossSales">₱0.00</h4>
            <p class="mb-0">Gross Sales</p>
        </div>

        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownGrossSales" data-bs-toggle="dropdown"
                aria-expanded="false">
                This week</a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownGrossSales">
                <li class="dropdown-item-dash" data-period="day">This Day</li>
                <li class="dropdown-item-dash" data-period="week">This Week</li>
                <li class="dropdown-item-dash" data-period="month">This Month</li>
                <li class="dropdown-item-dash" data-period="year">This Year</li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let salesChart;

        // Predefined list of Bootstrap colors
        const bootstrapColors = ['#28a745', '#dc3545']; // Success (Green) for Revenue, Danger (Red) for Cost

        // Function to shuffle an array
        const shuffleArray = (array) => {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        };

        // Function to update the chart
        const updateChart = (salesData, period) => {
            document.getElementById('dropdownGrossSales').textContent = `This ${period}`;

            // Calculate total sales
            const totalSales = salesData.reduce((sum, dataPoint) => sum + parseFloat(dataPoint.total_sales), 0);

            // Update the total sales in the HTML
            document.getElementById('totalGrossSales').textContent = `₱${totalSales.toFixed(2)}`;

            // Shuffle the colors to ensure random assignment without repetition
            const shuffledColors = shuffleArray(bootstrapColors.slice());

            const salesDataset = {
                label: 'Revenue',
                data: salesData.map(dataPoint => ({
                    x: formatPeriodLabel(dataPoint.period, period), // x-axis label
                    y: parseFloat(dataPoint.total_sales) // Sales value
                })),
                borderColor: '#28a745', // Line color for sales (Green)
                backgroundColor: '#28a745', // Fill color for sales with transparency
                borderWidth: 3,
                tension: 0.1, // Slight curve to the line
                fill: false // Don't fill under the line
            };

            const costDataset = {
                label: 'Cost',
                data: salesData.map(dataPoint => ({
                    x: formatPeriodLabel(dataPoint.period, period), // x-axis label
                    y: parseFloat(dataPoint.total_cost) // Cost value
                })),
                borderColor: '#dc3545', // Line color for cost (Red)
                backgroundColor: '#dc3545', // Fill color for cost with transparency
                borderWidth: 3,
                tension: 0.1, // Slight curve to the line
                fill: false // Don't fill under the line
            };

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

            if (salesChart) {
                salesChart.data.datasets = [salesDataset, costDataset]; // Update datasets
                salesChart.update();
            } else {
                const ctx = document.getElementById('salesChart').getContext('2d'); // Ensure correct canvas element

                salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [salesDataset, costDataset] // Two datasets: sales and cost
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'category', // Category for weeks, months, years
                                title: {
                                    display: true,
                                    text: period === 'day' ? 'Hours' : (period === 'week' ? 'Days' : (period === 'month' ? 'Weeks' : 'Months'))

                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Amount (₱)'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.dataset.label || '';
                                        return `${label}: ₱${context.raw.y.toFixed(2)}`; // Format as currency
                                    }
                                }
                            }
                        }
                    },
                    plugins: [backgroundColorPlugin]
                });
            }
        };

        const formatPeriodLabel = (periodValue, periodType) => {
            switch (periodType) {
                case 'day':
                    return periodValue; // e.g., "Monday"
                case 'week':
                    return periodValue; // Adjust for "Week"
                case 'month':
                    return periodValue; // e.g., "March"
                case 'year':
                    return periodValue.toString(); // e.g., "2021"
                default:
                    return periodValue;
            }
        };

        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                const period = this.getAttribute('data-period');
                fetchGrossSales(period);
            });
        });

        const fetchGrossSales = (period) => {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ url("/sale/gross") }}/' + period,
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

        fetchGrossSales('week');
    });
</script>