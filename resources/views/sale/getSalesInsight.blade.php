<x-app-layout>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-center">Sales Insights</h4>
            </div>
            <div class="card-body">
                <form id="filterForm" method="GET">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" id="month" name="month" class="form-control"
                                value="{{ request('month') }}">
                        </div>
                        <div class="col-md-4 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sales (₱)</th>
                                <th>Total Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody id="insightsTableBody">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#filterForm').on('submit', function (event) {
                event.preventDefault();
                const month = $('#month').val();

                $.ajax({
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    url: '{{ route('sales.insights') }}',
                    type: 'GET',
                    data: { month: month },
                    success: (response) => {
                        const insightsTableBody = $('#insightsTableBody');
                        insightsTableBody.empty();

                        response.insights.forEach(insight => {
                            insightsTableBody.append(`
                                <tr>
                                    <td>${insight.product_name}</td>
                                    <td>₱${parseFloat(insight.total_sales).toFixed(2)}</td>
                                    <td>${insight.total_quantity}</td>
                                </tr>
                            `);
                        });
                    },
                    error: (xhr) => {
                        console.error('Error:', xhr.responseText);
                        alert('An error occurred while fetching the data.');
                    }
                });
            });
        });
    </script>
</x-app-layout>