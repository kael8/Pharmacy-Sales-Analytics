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
                                            <th>Batch Code</th>
                                            <th>Product Name</th>
                                            <th>Date</th>
                                            <th>Quantity</th>
                                            <th>Sold</th>
                                            <th>Available</th>
                                            <th>Quantity Refund</th>
                                            <th>Status</th>
                                            <th>Refund</th>
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

                    inventoryTableBody.innerHTML = inventory.map(item => {
                        // Determine row color based on action type, sale count, and latest status
                        let rowColor = '';
                        if (item.action_type === 'added') {
                            rowColor = 'style="background-color: #ffdddd;"'; // Red for added
                        } else if (item.action_type === 'cancelled') {
                            // Lighter grey for cancelled
                            rowColor = 'style="background-color: #f0f0f0;"';
                        } else if (item.action_type === 'refunded') {
                            // Blue for refunded
                            rowColor = 'style="background-color: #e5e5ff;"';
                        }
                        else if (item.sale_count > 0 && item.is_latest) {
                            rowColor = 'style="background-color: #e5ffe5;"'; // Green for latest sale
                        } else if (item.sale_count > 0 && !item.is_latest) {
                            rowColor = 'style="background-color: #fff4e5;"'; // Yellow for non-latest sale
                        }

                        // Format created_at date to display year, month, and day
                        const createdAt = new Date(item.created_at);
                        const formattedDate = new Date(createdAt).toLocaleDateString(undefined, {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        }) + ' ' + new Date(createdAt).toLocaleTimeString(undefined, {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        }).replace(' ', ''); // Remove the space between time and AM/PM

                        return `
    <tr id="row_${item.id}" ${rowColor}>
        <td>${item.batch_id}</td>
        <td>${item.product.product_name}</td>
        <td>${formattedDate}</td>
        <td>${parseInt(Number(item.quantity) + Number(item.quantity_sold) ?? 0)}</td>
        <td>${item.action_type === 'cancelled' ? 0 : (item.quantity_sold ?? (item.action_type === 'added' ? 0 : (item.action_type === 'adjustment' ? 0 : 0)))}</td>
        <td>${item.quantity}</td>
        <td>${item.action_type === 'refunded' ? item.refunded_amount : 0}</td>
        <td id="refund_${item.id}">
            ${item.action_type === 'reduced' ? 'Reduced' :
                                (item.action_type === 'cancelled' ? 'Refunded' :
                                    (item.action_type === 'refunded' ? 'Refunded' :
                                        (item.action_type === 'added' ? 'Added' :
                                            (item.action_type === 'adjustment' ? 'Adjusted' : ''))))}
        </td>
         <td>
            ${item.action_type === 'reduced' ? `<button class="btn btn-sm btn-warning" id="button_${item.id}" name="Refund" onclick="showRefundModal(${item.id}, '${item.batch_id}', '${item.product.product_name}', '${item.created_at}', ${item.quantity_sold})">Refund</button>` : ''}
        </td>
    </tr>
`;
                    }).join('');

                    // Update pagination and batch dropdown logic
                    updatePagination(response);
                    updateBatchDropdown(response.batches);
                },
                error: (xhr) => {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function showRefundModal(id, batchId, productName, saleDate, amount) {
            // Create modal element 
            const modal = document.createElement('div');
            modal.classList.add('modal', 'fade');
            modal.id = 'refundModal';
            modal.tabIndex = -1;
            modal.setAttribute('aria-labelledby', 'refundModalLabel');
            modal.setAttribute('aria-hidden', 'true');

            // Format sale date
            const formattedSaleDate = new Date(saleDate).toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) + ' ' + new Date(saleDate).toLocaleTimeString(undefined, {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            // Define modal's inner HTML content
            modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">Refund Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to process this refund?</p>
                    <p><strong>Product Name:</strong> ${productName}</p>
                    <p><strong>Batch ID:</strong> ${batchId}</p>
                    <p><strong>Sale Date:</strong> ${formattedSaleDate}</p>
                    <p><strong>Refund Amount:</strong> ₱${Number(amount)}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmRefund(${id})">Confirm</button>
                </div>
            </div>
        </div>
    `;

            // Append modal to the body 
            document.body.appendChild(modal);

            // Initialize and show the modal 
            const refundModal = new bootstrap.Modal(modal);
            refundModal.show();

            // Remove the modal from the DOM after it is hidden
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
        }


        function confirmRefund(id) {
            // AJAX call to perform the refund action
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '/refund', // Replace with the actual URL of your refund API endpoint
                method: 'POST',
                data: {
                    id: id // Send the ID or any other required data for the refund
                },
                success: function (response) {
                    // Destroy the row and hide the modal after a successful refund
                    const row = document.getElementById(`row_${id}`);
                    row.remove();



                    // Hide and remove the modal after a successful refund
                    const refundModal = bootstrap.Modal.getInstance(document.getElementById('refundModal'));
                    refundModal.hide();

                    // Optionally, remove the modal element from the DOM to clean up
                    refundModal._element.remove();
                },
                error: function (error) {
                    // Handle any errors here, such as showing an error message
                    alert('An error occurred while processing the refund. Please try again.');
                }
            });
        }



        function updatePagination(response) {
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
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

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

            paginationContainer.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadInventory(page);
                });
            });
        }

        function updateBatchDropdown(batches) {
            const batchFilterElement = document.getElementById('batchFilter');
            const productFilter = document.getElementById('productFilter').value;

            if (productFilter) {
                batchFilterElement.disabled = false;
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