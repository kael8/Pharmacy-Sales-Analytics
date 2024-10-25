<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between flex-wrap">
        <div class="header-title">
            <h4 class="card-title">Net Profit</h4>
        </div>
        <div class="dropdown">
            <a href="#" class="text-secondary dropdown-toggle" id="dropdownNetProfit" data-bs-toggle="dropdown"
                aria-expanded="false">
                This week
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownNetProfit">
                <li class="dropdown-item-dash" data-period="day">This Day</li>
                <li class="dropdown-item-dash" data-period="week">This Week</li>
                <li class="dropdown-item-dash" data-period="month">This Month</li>
                <li class="dropdown-item-dash" data-period="year">This Year</li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <canvas id="netProfitChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let netProfitChart;

        // Bootstrap colors for each period type
        const periodColors = {
            week: '#007bff',   // Primary (Blue)
            month: '#28a745',  // Success (Green)
            year: '#dc3545'    // Danger (Red)
        };

        // Function to update the chart
        const updateChart = (profitData, period) => {
            document.getElementById('dropdownNetProfit').textContent = `This ${period}`;

            // Create dataset for net profit with solid color based on period type
            const datasets = [{
                label: 'Net Profit',
                data: profitData.map(dataPoint => parseFloat(dataPoint.total_profit)),
                backgroundColor: periodColors[period], // Use solid color for entire dataset
                borderColor: periodColors[period],     // Use the same color for line chart border
                fill: true // Ensures the area under the line is filled with color
            }];

            // Create labels for each time period
            const labels = profitData.map(dataPoint => dataPoint.period);

            if (netProfitChart) {
                netProfitChart.data.labels = labels; // Update labels
                netProfitChart.data.datasets = datasets; // Update datasets
                netProfitChart.update();
            } else {
                const ctx = document.getElementById('netProfitChart').getContext('2d'); // Ensure correct canvas element

                netProfitChart = new Chart(ctx, {
                    type: 'line', // Use 'line' for a line chart
                    data: {
                        labels: labels, // Labels for each time period
                        datasets: datasets // Dataset with net profit values
                    },
                    options: {
                        scales: {
                            x: {
                                // Control line width
                                categoryPercentage: 1.0
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Net Profit'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.dataset.label || '';
                                        return `${label}: $${context.raw.toFixed(2)}`; // Show net profit
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };

        // Event listener for dropdown items
        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                const period = this.getAttribute('data-period');
                fetchNetProfitData(period);
            });
        });

        // Function to fetch net profit data
        const fetchNetProfitData = (period) => {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ url("/sale/net-profit") }}/' + period,
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

        fetchNetProfitData('week'); // Default period
    });
</script>