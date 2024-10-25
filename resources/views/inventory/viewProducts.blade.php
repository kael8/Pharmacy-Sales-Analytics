<x-app-layout>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header text-center">
                        <h4 class="card-title">Inventory</h4>
                    </div>

                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-3">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Search Products...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Product Description</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <!-- Dynamic product rows will be populated here via AJAX -->
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
        function loadProducts(page = 1, searchQuery = '') {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ route('products') }}', // Adjust this to your route
                type: 'GET',
                data: { page: page, search: searchQuery }, // Pass search query
                success: (response) => {
                    const productsTableBody = document.getElementById('productsTableBody');
                    productsTableBody.innerHTML = response.data.map(product => `
                    <tr class="product-row" data-id="${product.id}">
                        <th scope="row">${product.id}</th>
                        <td>${product.product_name}</td>
                        <td>${product.product_description}</td>
                        <td>₱${parseFloat(product.total_price).toFixed(2)}</td>
                        <td>${product.total_quantity}</td>
                    </tr>
                    `).join('');

                    // Add click event listeners for row redirection
                    document.querySelectorAll('.product-row').forEach(row => {
                        row.addEventListener('click', function () {
                            const productId = this.getAttribute('data-id');
                            window.location.href = `/inventory/editProduct?id=${productId}`;
                        });
                    });

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
                            loadProducts(page, document.getElementById('searchInput').value); // Include search query
                        });
                    });
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        // Load the first page of products on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProducts();

            // Handle search input keyup event
            document.getElementById('searchInput').addEventListener('keyup', function () {
                const searchQuery = this.value;
                loadProducts(1, searchQuery); // Trigger product load with search query
            });
        });
    </script>
</x-app-layout>