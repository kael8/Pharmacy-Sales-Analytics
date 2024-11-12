@php
    use Carbon\Carbon;
@endphp

<x-app-layout :assets="$assets ?? []">
    <div class="container mt-4">
        <form id="stock-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Refund Sale</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-user-info">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="sales">Select Sale</label>
                                            <select class="form-control" id="sales" name="sales"
                                                onchange="displaySaleDetails()">
                                                <option value="">Select Sale</option>
                                                @foreach ($sales as $sale)
                                                    <option value="{{ $sale->id }}" data-batch-id="{{ $sale->batch_id }}"
                                                        data-product-name="{{ $sale->product->product_name }}"
                                                        data-sale-date="{{ Carbon::parse($sale->sale_date)->format('F j, Y g:i A') }}"
                                                        data-total-amount="{{ $sale->total_amount }}"
                                                        data-quantity-sold="{{$sale->quantity_sold}}"
                                                        data-inventory-id="{{$sale->inventory->id}}">
                                                        {{ $sale->id }} -
                                                        {{ Carbon::parse($sale->sale_date)->format('F j, Y g:i A') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sale Details Sections -->
                                <div class="row mt-4" id="sale-details" style="display: none;">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="sale-id">Sale ID</label>
                                            <input type="text" class="form-control" id="sale-id" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="product-name">Product Name</label>
                                            <input type="text" class="form-control" id="product-name" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="sale-details-quantity-date" style="display: none;">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="quantity-sold">Quantity Sold</label>
                                            <input type="text" class="form-control" id="quantity-sold" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="sale-date">Sale Date</label>
                                            <input type="text" class="form-control" id="sale-date" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="sale-details-price" style="display: none;">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="total-price">Total Price</label>
                                            <input type="text" class="form-control" id="total-price" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Modal Button Row -->
                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-warning" id="initiateRefundBtn"
                                            onclick="openRefundModal()">
                                            Initiate Refund
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const salesData = @json($sales);

        function displaySaleDetails() {
            const saleId = document.getElementById('sales').value;
            const selectedOption = document.querySelector(`#sales option[value="${saleId}"]`);

            if (saleId) {
                // Update form fields with selected sale details
                document.getElementById('sale-id').value = saleId;
                document.getElementById('product-name').value = selectedOption.dataset.productName;
                document.getElementById('quantity-sold').value = selectedOption.dataset.quantitySold || '';
                document.getElementById('sale-date').value = selectedOption.dataset.saleDate;
                document.getElementById('total-price').value = '₱' + selectedOption.dataset.totalAmount;

                // Show sale details
                document.getElementById('sale-details').style.display = 'flex';
                document.getElementById('sale-details-quantity-date').style.display = 'flex';
                document.getElementById('sale-details-price').style.display = 'flex';

                // Store sale data for modal
                window.selectedSaleData = {
                    id: saleId,
                    batchId: selectedOption.dataset.batchId,
                    productName: selectedOption.dataset.productName,
                    saleDate: selectedOption.dataset.saleDate,
                    amount: selectedOption.dataset.totalAmount,
                    inventoryId: selectedOption.dataset.inventoryId
                };
            } else {
                document.getElementById('sale-details').style.display = 'none';
                document.getElementById('sale-details-quantity-date').style.display = 'none';
                document.getElementById('sale-details-price').style.display = 'none';
                window.selectedSaleData = null;
            }
        }

        function openRefundModal() {
            const saleData = window.selectedSaleData;
            if (!saleData) {
                alert('Please select a sale to initiate refund.');
                return;
            }
            showRefundModal(saleData.id, saleData.batchId, saleData.productName, saleData.saleDate, saleData.amount, saleData.inventoryId);
        }

        function showRefundModal(id, batchId, productName, saleDate, amount, inventoryId) {
            const modal = document.createElement('div');
            modal.classList.add('modal', 'fade');
            modal.id = 'refundModal';
            modal.tabIndex = -1;
            modal.setAttribute('aria-labelledby', 'refundModalLabel');
            modal.setAttribute('aria-hidden', 'true');

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
                            <p><strong>Sale Date:</strong> ${saleDate}</p>
                            <p><strong>Refund Amount:</strong> ₱${amount}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="confirmRefund(${id})">Confirm</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const refundModal = new bootstrap.Modal(modal);
            refundModal.show();

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
                    // redirect to the inventory page
                    window.location.href = '/inventory/trackInventory';

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
    </script>
</x-app-layout>