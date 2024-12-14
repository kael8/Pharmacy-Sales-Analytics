<x-app-layout :assets="$assets ?? []">
    <div class="container mt-4">
        <!-- Sales Report Card -->
        <div class="card shadow-sm">
            <!-- Header -->
            <div class="card-header">
                <h1 class="card-title text-center">Sales Report</h1>
            </div>

            <div class="card-body">
                <!-- Day Selection Form -->
                <form id="filterForm" class="form-inline mb-4">
                    <div class="row w-100">
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group w-100">
                                <label for="date" class="mr-2">Select Date:</label>
                                <input type="text" id="date" name="date" class="form-control w-100"
                                    value="{{ request('date', date('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3"></div>
                        @role('Manager')
                        <div class="col-md-4 col-sm-12 mb-3 end">
                            <div class="form-group w-100">
                                <label for="staff" class="mr-2">Select Staff:</label>
                                <select id="staff" name="staff" class="form-control w-100">
                                    <option value="">All Staff</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->fname . ' ' . $staff->lname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endrole
                    </div>
                </form>

                <!-- Summary Statistics -->
                <div class="row text-center mb-4">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalSales" class="card-text">Total Sales: ₱0.00</h5>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalOrders" class="card-text">Total Orders: 0</h5>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalQuantity" class="card-text">Total Quantity Sold: 0</h5>
                    </div>
                </div>

                <!-- Detailed Sales Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">Sale ID</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            <!-- Table rows will be filled dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Container -->
                <div class="d-flex justify-content-center mt-4">
                    <ul class="pagination" id="paginationContainer">
                        <!-- Pagination links will be inserted here -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('date');
            const staffSelect = document.getElementById('staff') ?? '';

            // Initialize Flatpickr
            flatpickr(dateInput, {
                dateFormat: "Y-m-d",
                defaultDate: new Date(), // Set default date to today
                onChange: function (selectedDates, dateStr, instance) {
                    if (dateStr) {
                        fetchSales(dateStr, staffSelect.value);
                    }
                }
            });

            // Fetch total sales for today on page load
            fetchSales(dateInput.value, staffSelect.value);

            if (staffSelect != '') {
                // Fetch sales when staff selection changes
                staffSelect.addEventListener('change', () => {
                    fetchSales(dateInput.value, staffSelect.value);
                });
            }

            function fetchSales(date, staff, page = 1) {
                const data = { date: date, staff: staff, page: page };

                $.ajax({
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    url: '{{ route('report') }}',
                    type: 'POST',
                    data: data,
                    success: (response) => {
                        // Update summary statistics
                        document.getElementById('totalSales').textContent = `Total Sales: ₱${response.totalSales}`;
                        document.getElementById('totalOrders').textContent = `Total Orders: ${response.totalOrders}`;
                        document.getElementById('totalQuantity').textContent = `Total Quantity Sold: ${response.totalQuantity}`;

                        // Update sales table
                        const salesTableBody = document.getElementById('salesTableBody');
                        salesTableBody.innerHTML = response.sales.data.map(sale => {
                            const saleDate = new Date(sale.sale_date);
                            const localDate = saleDate.toLocaleString('en-US', {
                                timeZone: 'Asia/Manila',
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });

                            return `
                    <tr>
                        <td>${sale.id}</td>
                        <td>${localDate}</td>
                        <td>${sale.product.product_name}</td>
                        <td>${sale.quantity_sold}</td>
                        <td>₱${parseFloat(sale.inventory ? sale.inventory.price : 0).toFixed(2)}</td>
                        <td>₱${parseFloat((sale.inventory ? sale.inventory.price : 0) * sale.quantity_sold).toFixed(2)}</td>
                    </tr>
                `;
                        }).join('');

                        // Update pagination links
                        const paginationContainer = document.getElementById('paginationContainer');
                        paginationContainer.innerHTML = '';

                        // Previous Page Link
                        if (response.current_page > 1) {
                            paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page - 1}">&laquo;</a></li>`;
                        } else {
                            paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                        }

                        // Page Number Links
                        for (let page = 1; page <= response.last_page; page++) {
                            if (page === response.current_page) {
                                paginationContainer.innerHTML += `<li class="page-item active"><span class="page-link">${page}</span></li>`;
                            } else {
                                paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
                            }
                        }

                        // Next Page Link
                        if (response.current_page < response.last_page) {
                            paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page + 1}">&raquo;</a></li>`;
                        } else {
                            paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                        }

                        // Attach click events to pagination links
                        paginationContainer.querySelectorAll('a').forEach(link => {
                            link.addEventListener('click', function (e) {
                                e.preventDefault();
                                const page = this.getAttribute('data-page');
                                fetchSales(date, staff, page);
                            });
                        });
                    },
                    error: (xhr) => {
                        console.error('Error:', xhr.responseText);
                        const response = JSON.parse(xhr.responseText);
                        const errorMessage = response.message + "\n" + (response.errors ? response.errors.join("\n") : "");
                        alert(errorMessage);
                    }
                });
            }
        });
    </script>

</x-app-layout>