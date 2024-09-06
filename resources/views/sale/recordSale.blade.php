<x-app-layout :assets="$assets ?? []">
    <div>
        <form id="sales-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="header-title">
                                <h4 class="card-title text-center">Record a Sale</h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="card-body">
                                <table class="table table-bordered" id="sales-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Product Name</th>
                                            <th style="width: 15%;">Quantity</th>
                                            <th style="width: 20%;">Price</th>
                                            <th style="width: 20%;">Total</th>
                                            <th style="width: 10%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="product_id[]" class="form-control product-select">
                                                    <option value="" selected disabled>Select Product</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}" data-price="{{ $item->price }}">
                                                            {{ $item->product_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="quantity_sold[]"
                                                    class="form-control quantity" /></td>
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
                                <!-- Submit button triggers the confirmation modal -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    $(document).ready(function () {
        // Add new row with pre-populated products
        $('#add-row').click(function () {
            var newRow = `<tr>
                <td>
                    <select name="product_id[]" class="form-control product-select">
                        <option value="" selected disabled>Select Product</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->product_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="quantity_sold[]" class="form-control quantity" /></td>
                <td><input type="number" name="price[]" class="form-control price" readonly /></td>
                <td><input type="number" name="total[]" class="form-control total" readonly /></td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>`;
            $('#sales-table tbody').append(newRow);
            updateDropdowns();
        });

        // Remove row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateDropdowns();
        });

        // Update price and total when product is selected
        $(document).on('change', '.product-select', function () {
            var row = $(this).closest('tr');
            var selectedOption = $(this).find('option:selected');
            var price = selectedOption.data('price');
            row.find('.price').val(price);
            var quantity = row.find('.quantity').val();
            var total = price * quantity;
            row.find('.total').val(total);
            updateDropdowns();
        });

        // Calculate total when quantity changes
        $(document).on('input', '.quantity', function () {
            var row = $(this).closest('tr');
            var price = row.find('.price').val();
            var quantity = $(this).val();
            var total = price * quantity;
            row.find('.total').val(total);
        });

        // Update dropdown options to exclude selected products
        function updateDropdowns() {
            var selectedProducts = [];
            $('.product-select').each(function () {
                if ($(this).val()) {
                    selectedProducts.push($(this).val());
                }
            });

            $('.product-select').each(function () {
                var currentVal = $(this).val();
                $(this).find('option').each(function () {
                    if (selectedProducts.includes($(this).val()) && $(this).val() != currentVal) {
                        $(this).hide();
                    } else {
                        $(this).show();
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
            $('#confirmModal').modal('hide');
            createSale();
        });

        function createSale() {
            const form = document.getElementById('sales-form');
            const formData = new FormData(form);
            const url = "{{ route('createSale') }}"; // Replace with the appropriate route

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.message) {
                        alert(response.message);
                        // window.location.href = '/viewSales'; // Redirect to the desired page
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    var response = JSON.parse(xhr.responseText);
                    var errorMessage = response.errors.join("\n");
                    alert(errorMessage);
                }
            });
        }
    });
</script>