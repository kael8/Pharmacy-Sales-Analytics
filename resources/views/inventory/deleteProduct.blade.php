<x-app-layout :assets="$assets ?? []">
    <div class="container mt-4">
        <form id="stock-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Delete Product</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="productSelect" class="form-label">Select a Product</label>
                                        <select id="productSelect" class="form-control" name="product_id">
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div id="productInfo" class="border p-3 rounded" style="display: none;">
                                        <h5 class="mb-3">Product Information</h5>
                                        <p id="productDescription" class="mb-1"></p>
                                        <p id="productCreatedAt" class="mb-1"></p>
                                        <p id="productUpdatedAt" class="mb-1"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="button" id="deleteButton" class="btn btn-danger" disabled>Delete
                                        Product</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const products = @json($products);

            document.getElementById('productSelect').addEventListener('change', function () {
                const selectedProductId = this.value;
                const selectedProduct = products.find(product => product.id == selectedProductId);

                if (selectedProduct) {
                    document.getElementById('productInfo').style.display = 'block';
                    document.getElementById('productDescription').textContent = `Description: ${selectedProduct.product_description}`;
                    document.getElementById('productCreatedAt').textContent = `Created At: ${selectedProduct.created_at}`;
                    document.getElementById('productUpdatedAt').textContent = `Updated At: ${selectedProduct.updated_at}`;
                    document.getElementById('deleteButton').disabled = false;
                    document.getElementById('deleteButton').setAttribute('data-product-id', selectedProductId);
                } else {
                    document.getElementById('productInfo').style.display = 'none';
                    document.getElementById('deleteButton').disabled = true;
                    document.getElementById('deleteButton').removeAttribute('data-product-id');
                }
            });

            document.getElementById('deleteButton').addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');

                if (productId) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        url: '{{ url("/deleteProduct") }}',
                        type: 'POST',
                        data: { product_id: productId },
                        success: (response) => {
                            alert(response.message);
                            location.reload();
                        },
                        error: (xhr) => {
                            console.error('Error:', xhr.responseText);
                            const response = JSON.parse(xhr.responseText);
                            const errorMessage = response.message + "\n" + response.errors.join("\n");
                            alert(errorMessage);
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout>