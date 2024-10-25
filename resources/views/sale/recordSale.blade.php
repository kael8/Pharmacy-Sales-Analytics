<x-app-layout :assets="$assets ?? []">
    <div>
        <form id="sales-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="header-title">
                                <h4 class="card-title text-center">Purchase Sales</h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="card-body">
                                <table class="table table-bordered" id="sales-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Product Name</th>
                                            <th style="width: 15%;">Batch</th>
                                            <th style="width: 15%;">Quantity</th>
                                            <th style="width: 15%;">Price</th>
                                            <th style="width: 15%;">Total</th>
                                            <th style="width: 10%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="product_id[]" class="form-control product-select">
                                                    <option value="" selected disabled>Select Product</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->product_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="batch_id[]" class="form-control batch-select" disabled>
                                                    <option value="" selected disabled>Select Batch</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity_sold[]"
                                                    class="form-control quantity" />
                                                <small class="text-muted available-quantity">Available:
                                                    <span>0</span></small>
                                            </td>
                                            <td><input type="number" name="price[]" class="form-control price"
                                                    readonly /></td>
                                            <td><input type="number" name="total[]" class="form-control total"
                                                    readonly /></td>
                                            <td><button type="button" class="btn btn-danger remove-row">Remove</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-primary" id="add-row">Add Row</button>
                                <button type="button" class="btn btn-success" id="submit-sales">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to submit the sales record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-submit">Yes, Submit</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function () {
        // Add new row with pre-populated products
        $('#add-row').click(function () {
            var newRow = `<tr>
            <td>
                <select name="product_id[]" class="form-control product-select">
                    <option value="" selected disabled>Select Product</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->product_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="batch_id[]" class="form-control batch-select" disabled>
                    <option value="" selected disabled>Select Batch</option>
                </select>
            </td>
            <td>
                <input type="number" name="quantity_sold[]" class="form-control quantity" />
                <small class="text-muted available-quantity">Available: <span>0</span></small>
            </td>
            <td><input type="number" name="price[]" class="form-control price" readonly /></td>
            <td><input type="number" name="total[]" class="form-control total" readonly /></td>
            <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
        </tr>`;
            $('#sales-table tbody').append(newRow);
        });

        // Remove row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateBatchOptions();
        });

        // Update batches and prices when product is selected
        $(document).on('change', '.product-select', function () {
            var row = $(this).closest('tr');
            var productId = $(this).val();

            if (productId) {
                // Enable batch dropdown and load batches for the selected product
                var batchSelect = row.find('.batch-select');
                batchSelect.prop('disabled', false);
                loadBatches(batchSelect, productId);
            } else {
                row.find('.batch-select').prop('disabled', true).empty().append('<option value="" selected disabled>Select Batch</option>');
            }
        });

        // Update price, available quantity, and total when batch is selected
        $(document).on('change', '.batch-select', function () {
            var row = $(this).closest('tr');
            var selectedBatch = $(this).find('option:selected');
            var price = selectedBatch.data('price');
            var availableQty = selectedBatch.data('available');
            row.find('.price').val(price);
            row.find('.available-quantity span').text(availableQty);
            var quantity = row.find('.quantity').val();
            if (quantity > availableQty) {
                alert('Quantity exceeds available stock.');
                row.find('.quantity').val(availableQty);
                quantity = availableQty;
            }
            var total = price * quantity;
            row.find('.total').val(total);

            updateBatchOptions();
        });

        // Calculate total and ensure quantity is within available limit when quantity changes
        $(document).on('input', '.quantity', function () {
            var row = $(this).closest('tr');
            var price = row.find('.price').val();
            var availableQty = parseInt(row.find('.available-quantity span').text(), 10);
            var quantity = parseInt($(this).val(), 10);

            if (quantity > availableQty) {
                alert('Quantity exceeds available stock.');
                $(this).val(availableQty);
                quantity = availableQty;
            }

            var total = price * quantity;
            row.find('.total').val(total);
        });

        // Load batches for a selected product
        function loadBatches(batchSelect, productId) {
            batchSelect.empty().append('<option value="" selected disabled>Loading...</option>');

            $.ajax({
                url: '/get-batches',
                type: 'GET',
                data: { product_id: productId },
                success: function (response) {
                    batchSelect.empty().append('<option value="" selected disabled>Select Batch</option>');
                    $.each(response.batches, function (index, batch) {
                        batchSelect.append(`<option value="${batch.batch_id}" data-price="${batch.price}" data-available="${batch.quantity}">
                        ${batch.batch_id} - Price: ${batch.price}, Available: ${batch.quantity}</option>`);
                    });
                    updateBatchOptions(); // Update options to prevent duplicate selection
                },
                error: function () {
                    batchSelect.empty().append('<option value="" selected disabled>Error loading batches</option>');
                }
            });
        }

        // Prevent batch selection duplication
        function updateBatchOptions() {
            var selectedBatches = [];

            // Collect all selected batches
            $('#sales-table .batch-select').each(function () {
                var selectedBatch = $(this).val();
                if (selectedBatch) {
                    selectedBatches.push(selectedBatch);
                }
            });

            // Disable already selected batches in other rows
            $('#sales-table .batch-select').each(function () {
                var batchSelect = $(this);
                batchSelect.find('option').each(function () {
                    var batchId = $(this).val();
                    if (selectedBatches.includes(batchId) && batchId !== batchSelect.val()) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
        }

        // Trigger the confirmation modal on submit button click
        $('#submit-sales').click(function () {
            $('#confirmModal').modal('show');
        });

        // Handle form submission on confirmation
        $('#confirm-submit').click(function () {
            let allValid = true;
            $('#sales-table tbody tr').each(function () {
                const batchSelect = $(this).find('.batch-select');
                if (batchSelect.prop('disabled') || !batchSelect.val()) {
                    alert('Please select a batch for all products.');
                    allValid = false;
                    return false; // Exit loop
                }
            });

            if (allValid) {
                $('#confirmModal').modal('hide');
                createSale();
            }
        });

        function createSale() {
            const saleData = {
                product_id: [],
                batch_id: [],
                quantity_sold: [],
                price: [],
                total: [],
            };

            $('#sales-table tbody tr').each(function () {
                saleData.product_id.push($(this).find('.product-select').val());
                saleData.batch_id.push($(this).find('.batch-select').val());
                saleData.quantity_sold.push($(this).find('.quantity').val());
                saleData.price.push($(this).find('.price').val());
                saleData.total.push($(this).find('.total').val());
            });

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('createSale') }}',
                method: 'POST',
                data: saleData,
                success: function (response) {
                    alert('Sales submitted successfully.');
                    window.location.reload();
                },
                error: function (xhr) {
                    console.error(xhr.responseText); // Log the full response for debugging
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.errors) {

                            alert(response.errors);
                        } else {
                            alert('An error occurred while submitting sales. Please try again.');
                        }
                    } catch (e) {
                        alert('An error occurred while processing the error response. Please try again.');
                    }
                }




            });
        }
    });

</script>