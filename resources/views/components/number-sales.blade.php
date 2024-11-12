<div class="card-body "> <!-- Reduced padding -->
    <div class="row">
        <div class="col-9">
            <div class="progress-detail">
                <p class="mb-1 font-weight-bold text-primary" style="font-size: 0.9rem;">Number of Sales</p>
                <!-- Smaller font size -->
                <h4 id="current-sales-count" style="font-size: 1.5rem;">0</h4>
                <!-- Smaller font size -->
                <div class="d-flex">
                    <small id="percentage-change" class="me-2" style="font-size: 0.8rem;">Change: 0%</small>
                    <!-- Smaller font size -->
                </div>
            </div>
        </div>
        <div class="col-3 d-flex flex-column align-items-end">
            <div class="dropdown">
                <a href="#" class="text-secondary dropdown-toggle" id="dropdownTotalSale" data-bs-toggle="dropdown"
                    aria-expanded="false" style="font-size: 0.8rem;"> <!-- Smaller font size -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                        viewBox="0 0 24 24"> <!-- Smaller icon size -->
                        <path
                            d="M19 4h-1V2h-2v2H8V2H6v2H5C3.89 4 3 4.9 3 6v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z" />
                    </svg>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0 m-0" aria-labelledby="dropdownTotalSale">
                    <li class="dropdown-item-dash small ps-2" data-period="day">Day</li>
                    <li class="dropdown-item-dash small ps-2" data-period="week">Week</li>
                    <li class="dropdown-item-dash small ps-2" data-period="month">Month</li>
                    <li class="dropdown-item-dash small ps-2" data-period="year">Year</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        async function fetchSalesCount(period) {


            try {
                const response = await fetch('{{ url('/sale/count') }}/' + period, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();


                document.querySelector('#current-sales-count').textContent = data.current_sales_count;

                const percentageChangeElement = document.querySelector('#percentage-change');
                percentageChangeElement.textContent = `Change: ${data.percentage_change.toFixed(2)}%`;

                if (data.percentage_change > 0) {
                    percentageChangeElement.style.color = 'green';
                } else if (data.percentage_change < 0) {
                    percentageChangeElement.style.color = 'red';
                } else {
                    percentageChangeElement.style.color = 'yellow';
                }
            } catch (error) {
                console.error('AJAX Error:', error); // Debugging log
            }
        }

        // Trigger the AJAX request when a dropdown item is clicked
        document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
                var period = this.getAttribute('data-period');

                fetchSalesCount(period);
            });
        });


        // Optionally, trigger the AJAX request on page load with default period 'week'
        fetchSalesCount('day');
    });
</script>