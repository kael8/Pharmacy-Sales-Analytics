<x-app-layout>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header text-center">
                        <h4 class="card-title">Inventory Batches</h4>
                    </div>

                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-3">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Search Products...">
                            </div>
                        </div>

                        <!-- Dashboard and Create Batch Button -->
                        <div class="dashboard mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <p>Total Batch Value: <strong id="totalValue"></strong></p>
                                <p>Total Batches: <strong id="totalProducts"></strong></p>
                            </div>
                            <!-- Create Batch Button -->
                            <a href="{{ route('addBatch') }}" class="btn btn-primary">
                                Create New Batch
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Batch ID</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Expiration Date</th>
                                    </tr>
                                </thead>
                                <tbody id="batchesTableBody">
                                    <!-- Dynamic batch rows will be populated here via AJAX -->
                                </tbody>
                            </table>

                            <!-- Pagination Links -->
                            <div class="d-flex justify-content-center mt-4">
                                <ul class="pagination" id="paginationContainer">
                                    <!-- Dynamic pagination links will be populated here via AJAX -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function loadBatches(page = 1, searchQuery = '') {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ route('batches') }}', // Adjust this to your route
                type: 'GET',
                data: { page: page, search: searchQuery }, // Pass search query
                success: (response) => {
                    const batchesTableBody = document.getElementById('batchesTableBody');
                    batchesTableBody.innerHTML = response.data.map(batch => `
                    <tr onclick="window.location.href='{{ url('inventory/editBatch') }}/${batch.id}'" style="cursor: pointer;">
                        <th scope="row">${batch.id}</th>
                        <td>${batch.batch_id}</td>
                        <td>${batch.product.product_name}</td>
                        <td>${batch.quantity}</td>
                        <td>₱${parseFloat(batch.price).toFixed(2)}</td>
                        <td>${batch.expiration_date}</td>
                    </tr>
                    `).join('');

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
                            loadBatches(page, document.getElementById('searchInput').value); // Include search query
                        });
                    });

                    // Update total inventory value and products count
                    document.getElementById('totalValue').innerText = `₱${response.totalProductValue.toFixed(2)}`;
                    document.getElementById('totalProducts').innerText = response.totalProducts;
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        // Load the first page of batches on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadBatches();

            // Handle search input keyup event
            document.getElementById('searchInput').addEventListener('keyup', function () {
                const searchQuery = this.value;
                loadBatches(1, searchQuery); // Trigger batch load with search query
            });
        });
    </script>
</x-app-layout>