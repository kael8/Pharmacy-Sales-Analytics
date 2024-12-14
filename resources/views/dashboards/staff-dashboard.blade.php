<x-app-layout :assets="$assets ?? []">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header  text-white text-center">
                        <h5 class="card-title mb-0 text-black">Staff Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <div class="card mb-4">
                            <div class="card-header  text-white text-center">
                                <h6 class="card-title mb-0 text-black">Total Amount of Sales</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex justify-content-center mt-3">
                                        <input type="text" id="salesDate" class="form-control text-center"
                                            style="max-width: 200px;" placeholder="Select Date">
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        <h4 id="totalSales" class="text-success">₱0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Add more cards or content here as needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Include Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Flatpickr
            const salesDatePicker = flatpickr("#salesDate", {
                dateFormat: "Y-m-d",
                defaultDate: new Date(), // Set default date to today
                onChange: function (selectedDates, dateStr, instance) {
                    if (dateStr) {
                        fetchTotalSales(dateStr);
                    }
                }
            });

            // Fetch total sales for today on page load
            fetchTotalSales(salesDatePicker.input.value);

            function fetchTotalSales(date) {
                fetch(`/total-sales?date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('totalSales').textContent = '₱' + data.totalSales;
                    })
                    .catch(error => console.error('Error fetching sales:', error));
            }


            // Function to update the date and time display
            function updateDateTime() {
                const now = new Date();
                const currentDate = now.toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                const currentTime = now.toLocaleTimeString(undefined, {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                document.getElementById('dateTimeDisplay').innerText = `Date and Time: ${currentDate} ${currentTime}`;
            }

            // Update the date and time display initially and every second
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });


    </script>
</x-app-layout>