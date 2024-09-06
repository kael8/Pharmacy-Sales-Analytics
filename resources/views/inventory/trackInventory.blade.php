<x-app-layout>
    <div>

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="container my-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="header-title">
                                <h4 class="card-title text-center">Inventory History</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Dashboard Overview -->
                            <div class="dashboard mb-4">

                                <p>Total Inventory Value: ${{ number_format($totalProductValue, 2) }}</p>
                                <p>Total Products: {{ $totalProducts }}</p>

                            </div>



                            <!-- Inventory List -->
                            <div class="inventory-list mb-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Stock Date</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Inventory rows will go here -->
                                        @foreach ($inventory as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->product->product_name }}
                                                </td>
                                                <td>
                                                    {{ $item->quantity }}
                                                </td>
                                                <td>
                                                    {{ $item->price }}
                                                </td>
                                                <td>
                                                    {{ $item->stock_date }}
                                                </td>
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

    </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>