<x-app-layout :assets="$assets ?? []">
    <div>
        <form id="stock-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">
                                    {{ isset($product) ? 'Edit Product Information' : (isset($batch) ? 'Edit Batch Information' : (request()->is('inventory/addProduct') ? 'Add Product Information' : 'New Batch Information')) }}
                                </h4>
                            </div>
                            @if (!isset($product) && !isset($batch) && !request()->is('inventory/addBatch'))
                                <div class="header-action">
                                    <!-- Create Batch Button -->
                                    <a href="{{ route('addBatch') }}" class="btn btn-primary">
                                        Create New Batch
                                    </a>
                                </div>
                            @elseif(request()->is('inventory/editBatch/*'))
                                <div class="header-action" style="margin-right: 100px;">
                                    <!-- Edit Batch Search Bar -->
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Search Products...">
                                    <div id="searchResults" class="list-group"
                                        style="position: absolute; z-index: 1000; width: 100%;"></div>
                                </div>
                            @endif


                        </div>
                        <div class="card-body">
                            <div class="new-user-info">
                                <div class="row">
                                    <!-- Product Name Input or Select -->
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="product_name">Product Name: <span
                                                class="text-danger">*</span></label>
                                        @if ($action == 'edit')
                                            <!-- Input field for editBatch -->
                                            <input type="text" name="product_name" id="product_name" class="form-control"
                                                value="{{ $batch->product_name }}" {{ $action == 'edit' ? 'disabled' : '' }}
                                                placeholder="Product" required>
                                        @elseif($action == 'add')
                                            <!-- Select dropdown for addBatch -->
                                            <select name="product_name" class="form-control" id="product_name"
                                                onchange="updateProductDetails()">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                        data-cost="{{ $product->cost_price }}" data-id="{{ $product->id }}"
                                                        data-description="{{ $product->product_description }}">
                                                        {{ $product->product_name }}

                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($action == 'addProduct')
                                            <input type="text" name="product_name" id="product_name" class="form-control"
                                                value="" placeholder="Product" required>
                                        @elseif($action == 'editProduct')
                                            <input type="text" name="product_name" id="product_name" class="form-control"
                                                value="{{ $product->product_name }}" placeholder="Product" required>
                                        @endif
                                    </div>

                                    @if($action != 'editProduct' && $action != 'addProduct')
                                        <!-- Product Price -->
                                        <!-- Price -->
                                        <div class="form-group col-md-6">
                                            <label class="form-label" for="product_price">Price Unit: <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="product_price" id="product_price"
                                                class="form-control" value="{{ isset($batch) ? $batch->price : '' }}"
                                                placeholder="Price Unit" required>
                                        </div>
                                    @endif

                                    @if($action != 'editProduct' && $action != 'addProduct')
                                        <!-- Cost Price -->
                                        <div class="form-group col-md-6">
                                            <label class="form-label" for="cost_price">Price Ceiling: <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="cost_price" id="cost_price" class="form-control"
                                                value="{{ isset($batch) ? $batch->cost_price : '' }}"
                                                placeholder="Price Ceiling" required>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="form-group col-md-6">
                                            <label class="form-label" for="product_quantity">Quantity: <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="product_quantity" id="product_quantity"
                                                class="form-control" value="{{ isset($batch) ? $batch->quantity : '' }}"
                                                placeholder="Quantity" required>
                                        </div>
                                    @endif

                                    <!-- Product Description -->
                                    <div class="form-group col-md-12">
                                        <label class="form-label" for="product_description">Product Description: <span
                                                class="text-danger">*</span></label>
                                        <textarea name="product_description" id="product_description"
                                            class="form-control" placeholder="Product Description" required
                                            @if($action == 'addProduct')@elseif($action == 'editProduct') @else disabled
                                            @endif>{{ isset($product) ? $product->product_description : (isset($batch) ? $batch->product_description : '') }}</textarea>
                                    </div>

                                    @if($action != 'editProduct' && $action != 'addProduct')
                                        <!-- Expiration Date -->
                                        <div class="form-group col-md-6">
                                            <label class="form-label" for="expiration_date">Expiration Date:</label>
                                            <input type="text" id="expiration_date" name="expiration_date"
                                                class="form-control"
                                                value="{{ isset($batch) ? $batch->expiration_date : '' }}">
                                        </div>
                                    @endif

                                    <!-- Hidden Batch ID for Editing -->
                                    <input type="hidden" name="batch_id" value="{{ $batch->batch_id ?? '' }}">
                                    <input type="hidden" name="product_id" id="product_id"
                                        value="{{isset($product) ? $product->id : ''}}">
                                </div>

                                <!-- Submit Button -->
                                <hr>
                                <div class="row">
                                    <!-- Additional fields can go here -->
                                </div>
                                <button type="button" class="btn btn-primary" onclick="submitForm()">
                                    {{ isset($product) ? 'Update Product' : (isset($batch) ? 'Update Batch' : (request()->is('inventory/addProduct') ? 'Add Product' : (request()->is('inventory/addBatch') ? 'Add Batch' : 'Add Batch'))) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

<!-- Include Flatpickr CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    // Initialize Flatpickr on the expiration_date field
    flatpickr("#expiration_date", {
        dateFormat: "Y-m-d",
        allowInput: true // Allow manual typing if needed
    });

    // Function to update product details when a product is selected
    function updateProductDetails() {
        const selectElement = document.getElementById('product_name');
        const selectedOption = selectElement.options[selectElement.selectedIndex];

        if (selectedOption.value) {
            console.log(selectedOption.getAttribute('data-id'));
            // Update all related fields with data from selected option

            document.getElementById('product_description').value = selectedOption.getAttribute('data-description');
            document.getElementById('product_id').value = selectedOption.getAttribute('data-id');
        } else {
            // Clear all fields if no product is selected
            document.getElementById('product_quantity').value = '';
            document.getElementById('product_price').value = '';
            document.getElementById('cost_price').value = '';
            document.getElementById('product_description').value = '';
            document.getElementById('product_id').value = '';
            document.getElementById('expiration_date').value = '';
        }
    }

    // Handle form submission for both Add and Edit Batch
    function submitForm() {
        const form = document.getElementById('stock-form');
        const formData = new FormData(form);

        const url = @if($action == 'edit')
            "{{ route('updateBatch', $batch->id) }}"
        @elseif($action == 'add')
            "{{ route('createBatch') }}"
        @elseif($action == 'addProduct')
            "{{ route('createProduct') }}"
        @elseif($action == 'editProduct')
            "{{ route('updateProduct', $product->id) }}"
        @endif;

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
                    window.location.href = '/inventory/viewInventoryBatches';
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.errors && typeof response.errors === 'object') {
                        var errorMessage = '';
                        for (var key in response.errors) {
                            if (response.errors.hasOwnProperty(key)) {
                                errorMessage += response.errors[key].join("\n") + "\n";
                            }
                        }
                        alert(errorMessage);
                    } else if (response.message) {
                        alert(response.message);
                    } else {
                        alert('An unknown error occurred.');
                    }
                } catch (e) {
                    alert('An error occurred while processing the error response.');
                }
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (/^\/inventory\/editBatch\/\d+$/.test(window.location.pathname)) {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            @if(isset($searchBatches))
                const searchBatches = @json($searchBatches); // Convert PHP searchBatches array to JavaScript array
            @else
                const searchBatches = [];
            @endif


            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase();

                if (query.length > 0) { // Only search if the query is longer than 0 characters
                    const filteredBatches = searchBatches.filter(batch => {
                        return batch.product.product_name.toLowerCase().includes(query) ||
                            batch.batch_id.toLowerCase().includes(query);
                    });

                    searchResults.innerHTML = ''; // Clear previous results

                    filteredBatches.forEach(batch => {
                        const resultItem = document.createElement('a');
                        resultItem.href = '/inventory/editBatch/' + batch.id;
                        resultItem.classList.add('list-group-item', 'list-group-item-action');
                        resultItem.textContent = `${batch.product.product_name} - ${batch.batch_id}`;
                        searchResults.appendChild(resultItem);
                    });
                } else {
                    searchResults.innerHTML = ''; // Clear results if query is too short
                }
            });
        }
    });
</script>