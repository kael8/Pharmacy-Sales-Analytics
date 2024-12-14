<x-app-layout>
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header text-center">
                        <h4 class="card-title">Expired Batches</h4>
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

                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" data-column="id" class="sortable">#</th>
                                        <th scope="col" data-column="batch_id" class="sortable">Batch ID</th>
                                        <th scope="col" data-column="product_name" class="sortable">Product Name</th>
                                        <th scope="col" data-column="quantity" class="sortable">Remaining Stock</th>
                                        <th scope="col" data-column="price" class="sortable">Price</th>
                                        <th scope="col" data-column="expiration_date" class="sortable">Expiration Date
                                        </th>

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
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this batch? This action cannot be undone.</p>
                    <p><strong>Batch ID:</strong> <span id="modalBatchId"></span></p>
                    <p><strong>Product Name:</strong> <span id="modalProductName"></span></p>
                    <p><strong>Remaining Stock:</strong> <span id="modalQuantity"></span></p>
                    <p><strong>Price:</strong> <span id="modalPrice"></span></p>
                    <p><strong>Expiration Date:</strong> <span id="modalExpirationDate"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDelete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        let currentSortColumn = 'id';
        let currentSortDirection = 'asc';

        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        function loadBatches(page = 1, searchQuery = '', sortColumn = 'id', sortDirection = 'asc') {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '{{ route('expiredBatches') }}', // Adjust this to your route
                type: 'GET',
                data: { page: page, search: searchQuery, sort: sortColumn, direction: sortDirection }, // Pass search query and sorting parameters
                success: (response) => {
                    const batchesTableBody = document.getElementById('batchesTableBody');
                    batchesTableBody.innerHTML = response.data.map(batch => {
                        const expirationDate = new Date(batch.expiration_date);
                        const currentDate = new Date();
                        const isExpired = expirationDate < currentDate;
                        const expiredIndicator = isExpired ? '<span class="badge bg-danger">Expired</span>' : '';



                        return `
    <tr style="cursor: pointer;">
        <th scope="row">${batch.id}</th>
        <td>${batch.batch_id}</td>
        <td>${batch.product.product_name}</td>
        <td>${batch.quantity}</td>
        <td>₱${parseFloat(batch.price).toFixed(2)}</td>
        <td>${batch.expiration_date} ${expiredIndicator}</td>
       
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
                            loadBatches(page, document.getElementById('searchInput').value, currentSortColumn, currentSortDirection); // Include search query and sorting parameters
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
            // Get sorting parameters from URL
            const urlSortColumn = getUrlParameter('sort') || 'product_name';
            const urlSortDirection = getUrlParameter('direction') || 'asc';
            currentSortColumn = urlSortColumn;
            currentSortDirection = urlSortDirection;

            loadBatches(1, '', currentSortColumn, currentSortDirection);

            // Handle search input keyup event
            document.getElementById('searchInput').addEventListener('keyup', function () {
                const searchQuery = this.value;
                loadBatches(1, searchQuery, currentSortColumn, currentSortDirection); // Trigger batch load with search query and sorting parameters
            });

            // Handle column header click event for sorting
            document.querySelectorAll('.sortable').forEach(header => {
                header.addEventListener('click', function () {
                    const column = this.getAttribute('data-column');
                    if (currentSortColumn === column) {
                        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSortColumn = column;
                        currentSortDirection = 'asc';
                    }
                    loadBatches(1, document.getElementById('searchInput').value, currentSortColumn, currentSortDirection); // Trigger batch load with sorting parameters
                });
            });
            // Delete button listener to set batch ID for deletion
            const deleteModal = document.getElementById('deleteModal');
            let deleteBatchId;

            document.getElementById('batchesTableBody').addEventListener('click', function (event) {
                const deleteButton = event.target.closest('.btn-danger');
                if (deleteButton) {
                    deleteBatchId = deleteButton.getAttribute('data-id');

                    // Retrieve batch row information
                    const batchRow = deleteButton.closest('tr');
                    const batchId = batchRow.children[1].innerText;
                    const productName = batchRow.children[2].innerText;
                    const quantity = batchRow.children[3].innerText;
                    const price = batchRow.children[4].innerText;
                    const expirationDate = batchRow.children[5].innerText;

                    // Set the batch details in the modal
                    document.getElementById('modalBatchId').innerText = batchId;
                    document.getElementById('modalProductName').innerText = productName;
                    document.getElementById('modalQuantity').innerText = quantity;
                    document.getElementById('modalPrice').innerText = price;
                    document.getElementById('modalExpirationDate').innerText = expirationDate;
                }
            });


            // Handle delete confirmation in the modal
            document.getElementById('confirmDelete').addEventListener('click', function () {
                if (deleteBatchId) {
                    const deleteUrl = `{{ url('/deleteBatch') }}/${deleteBatchId}`;
                    // Perform the delete operation, e.g., using AJAX
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        url: deleteUrl,
                        type: 'POST',
                        success: (response) => {
                            // Hide the modal
                            $('#deleteModal').modal('hide');
                            // Reload batches or remove deleted row from DOM
                            loadBatches();
                        },
                        error: (xhr) => {
                            console.error('Delete error:', xhr.responseText);
                            // Hide the modal in case of error as well
                            $('#deleteModal').modal('hide');
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout>