<x-app-layout>
    <div>

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="header-title">
                            <h4 class="card-title text-center">Inventory</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="bd-example table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Product Description</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <th scope="row">
                                                <a href="{{ url('/editProduct/' . $product->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $loop->iteration }}
                                                </a>
                                            </th>
                                            <td>
                                                <a href="{{ url('/editProduct/' . $product->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $product->product_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url('/editProduct/' . $product->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $product->product_description }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url('/editProduct/' . $product->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $product->price }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url('/editProduct/' . $product->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $product->quantity_in_stock }}
                                                </a>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>