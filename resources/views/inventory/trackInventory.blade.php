<x-app-layout>
    <div>
        <div class="row">
            <div class="col-xl-12 col-lg-12" style="padding: 0px;">
                <div class="container my-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="header-title">
                                <h4 class="card-title text-center">Inventory History</h4>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Filter Section -->
                            <div class="filter-section mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="productFilter">Filter by Product:</label>
                                        <select id="productFilter" class="form-control">
                                            <option value="">All Products</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="batchFilter">Filter by Batch:</label>
                                        <select id="batchFilter" class="form-control">
                                            <option value="">All Batches</option>
                                            @foreach($batches as $batch)
                                                <option value="{{ $batch->id }}">Batch {{ $batch->id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory List -->
                            <div class="inventory-list table-responsive mb-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Batch ID</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Stock Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventoryTableBody">
                                        <!-- Inventory rows will be populated here via AJAX -->
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
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function loadInventory(page = 1) {
            const productFilter = document.getElementById('productFilter').value;
            const batchFilter = document.getElementById('batchFilter').value;

            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ route('track') }}', // Adjust this to your route
                type: 'GET',
                data: {
                    page: page,
                    product_id: productFilter,
                    batch_id: batchFilter
                },
                success: (response) => {
                    const inventoryTableBody = document.getElementById('inventoryTableBody');
                    const inventory = response.inventory;

                    // Populate table rows
                    inventoryTableBody.innerHTML = inventory.map(item => `
                <tr>
                    <td>${item.batch_id}</td>
                    <td>${item.product.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>${item.stock_date}</td>
                    <td>${item.action_type}</td>
                </tr>
            `).join('');

                    // Update pagination links
                    const paginationContainer = document.getElementById('paginationContainer');
                    paginationContainer.innerHTML = '';

                    const totalPages = response.last_page;
                    const currentPage = response.current_page;

                    // Previous Page Link
                    if (currentPage > 1) {
                        paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a></li>`;
                    } else {
                        paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                    }

                    // Page Number Links with limiting (max 5 pages shown)
                    let startPage = Math.max(1, currentPage - 2); // Show up to 2 pages before current
                    let endPage = Math.min(totalPages, currentPage + 2); // Show up to 2 pages after current

                    if (startPage > 1) {
                        paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                        if (startPage > 2) {
                            paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                    }

                    for (let page = startPage; page <= endPage; page++) {
                        if (page === currentPage) {
                            paginationContainer.innerHTML += `<li class="page-item active"><span class="page-link">${page}</span></li>`;
                        } else {
                            paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
                        }
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                        paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
                    }

                    // Next Page Link
                    if (currentPage < totalPages) {
                        paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a></li>`;
                    } else {
                        paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                    }

                    // Attach click events to pagination links
                    paginationContainer.querySelectorAll('a').forEach(link => {
                        link.addEventListener('click', function (e) {
                            e.preventDefault();
                            const page = this.getAttribute('data-page');
                            loadInventory(page);
                        });
                    });

                    // Update the batch dropdown if a product is selected
                    const batchFilterElement = document.getElementById('batchFilter');
                    if (productFilter) {
                        batchFilterElement.disabled = false;
                        const batches = response.batches;
                        batchFilterElement.innerHTML = '<option value="">All Batches</option>';
                        batches.forEach(batch => {
                            batchFilterElement.innerHTML += `
                        <option value="${batch.batch_id}">
                            Batch ID: ${batch.batch_id} | Price: ₱${batch.price} | Quantity: ${batch.quantity}
                        </option>
                    `;
                        });
                    } else {
                        batchFilterElement.disabled = true;
                        batchFilterElement.innerHTML = '<option value="">All Batches</option>';
                    }
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                }
            });
        }




        // Load inventory on page load and when filters are changed
        document.addEventListener('DOMContentLoaded', function () {
            loadInventory();

            // Attach change event listeners to filters
            document.getElementById('productFilter').addEventListener('change', function () {
                loadInventory();
            });
            document.getElementById('batchFilter').addEventListener('change', function () {
                loadInventory();
            });
        });

    </script>
</x-app-layout>