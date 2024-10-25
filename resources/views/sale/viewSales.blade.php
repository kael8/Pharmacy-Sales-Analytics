<x-app-layout :assets="$assets ?? []">
    <div class="container mt-4">
        <!-- Sales Report Card -->
        <div class="card shadow-sm">
            <!-- Header -->
            <div class="card-header">
                <h1 class="card-title text-center">Sales Report</h1>
            </div>

            <div class="card-body">
                <!-- Month Selection Form -->
                <form id="filterForm" class="form-inline mb-4">
                    <div class="row w-100">
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group w-100">
                                <label for="month" class="mr-2">Select Month:</label>
                                <input type="month" id="month" name="month" class="form-control w-100"
                                    value="{{ request('month', date('Y-m')) }}">
                            </div>
                        </div>
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
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
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
    <!-- Include Flatpickr and Flatpickr Month Select Plugin -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/plugins/monthSelect/index.js"></script>


    <!-- Custom CSS for Month Grid Layout and Pagination -->
    <style>
        /* Month Grid Layout */
        .flatpickr-monthSelect-months {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 10px;
        }

        .flatpickr-monthSelect-month {
            text-align: center;
            padding: 10px 0;
            cursor: pointer;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .flatpickr-monthSelect-month:hover,
        .flatpickr-monthSelect-month.selected {
            background-color: #007bff;
            color: #fff;
        }

        .flatpickr-monthSelect-theme-light .flatpickr-monthSelect-month {
            background-color: #f9f9f9;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const monthInput = document.getElementById('month');

            // Trigger initial sales load with the current month
            sales(monthInput.value);

            // Handle change event for month input
            monthInput.addEventListener('change', (event) => {
                event.preventDefault();
                sales(monthInput.value);
            });

            function sales(month = null, page = 1) {
                const data = { month: month || monthInput.value, page: page };

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
                            const localDate = saleDate.toLocaleString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' });

                            return `
        <tr>
            <td>${sale.id}</td>
            <td>${localDate.split(',')[0]}</td>
            <td>${sale.product.product_name}</td>
            <td>${sale.quantity_sold}</td>
            <td>₱${parseFloat(sale.inventory.price).toFixed(2)}</td> <!-- Adjusted to get price from inventory -->
            <td>₱${parseFloat(sale.inventory.price * sale.quantity_sold).toFixed(2)}</td> <!-- Calculate total price -->
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
                                sales(month, page);
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

            function initializeFlatpickr(inputElement) {
                flatpickr(inputElement, {
                    dateFormat: "Y-m",
                    plugins: [new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y",
                        theme: "light"
                    })]
                });
            }
        });
    </script>
</x-app-layout>