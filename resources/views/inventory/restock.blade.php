<x-app-layout :assets="$assets ?? []">
    <div>
        <form id="stock-form" enctype="multipart/form-data">
            @csrf
            <div class="row">

                <div class=" col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">
                                    {{ isset($stock) ? 'Edit Stock Information' : 'New Stock Information' }}
                                </h4>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary"
                                    role="button">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-user-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="fname">Product Name: <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="product_name" class="form-control"
                                            placeholder="Product Name" value="{{ $stock->product_name ?? '' }}"
                                            required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="phone">Price:</label>
                                        <input type="number" name="product_price" class="form-control"
                                            placeholder="Price" value="{{ $stock->price ?? '' }}" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-label" for="lname">Product Description: <span
                                                class="text-danger">*</span></label>

                                        <textarea name="product_description" class="form-control"
                                            placeholder="Product Description">{{ $stock->product_description ?? '' }}</textarea>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="email">Quantity: <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="product_quantity" class="form-control"
                                            placeholder="Quantity" value="{{ $stock->quantity_in_stock ?? '' }}"
                                            required>
                                    </div>
                                </div>
                                <hr>

                                <div class="row">

                                </div>
                                <button type="button" class="btn btn-primary"
                                    onclick="submitForm()">{{ isset($stock) ? 'Update Item' : 'Add Item' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>


    function submitForm() {
        const form = document.getElementById('stock-form');
        const formData = new FormData(form);
        const url = "{{ isset($stock) ? route('updateProduct', $stock->id) : route('createProduct') }}";


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
                    // Optionally, redirect or update the UI
                    window.location.href = '/viewProducts'; // Redirect to /viewStaff
                } else {
                    alert('An error occurred. Please try again.');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    }
</script>