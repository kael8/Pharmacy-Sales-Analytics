<x-app-layout :assets="$assets ?? []">
   <style>
      .total-sales {
         word-wrap: break-word;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
         max-width: 100%;
      }
   </style>
   <div>
      <form id="staff-form" enctype="multipart/form-data">
         @csrf
         <div class="row">
            <div class="col-xl-3 col-lg-4">
               <div class="card">
                  <div class="card-header">
                     <div class="header-title">
                        <h4 class="card-title text-center">{{ isset($user) ? 'Edit User' : 'Add User' }}</h4>
                     </div>
                  </div>
                  <div class="card-body">
                     <div class="form-group">
                        <div class="profile-img-edit position-relative d-flex justify-content-center">


                           <img id="profile-pic" src="{{ $profile_image->name ?? 'https://via.placeholder.com/100' }}"
                              alt="User-Profile" class="profile-pic rounded avatar-100">
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                           <button type="button" class="btn btn-primary"
                              onclick="document.getElementById('file-upload').click();">
                              Upload Image
                           </button>
                           <input id="file-upload" class="file-upload" type="file" accept="image/*" name="profile_image"
                              style="display: none;" onchange="previewImage(event)">
                        </div>
                     </div>
                  </div>
               </div>
               @if(isset($user))

               <div class="card" style="max-width: 400px; margin: auto;">
                 <div class="card-header text-center">
                   <h6 class="card-title">Total amount of Sales</h6>
                 </div>
                 <div class="card-body">
                   <div class="form-group">
                     <div class="d-flex justify-content-center mt-3">
                        <input type="text" id="salesDate" class="form-control" style="max-width: 150px;">
                     </div>
                     <div class="d-flex justify-content-center mt-3">
                        <h4 id="totalSalesD" class="text-primary text-center total-sales">0</h4>
                     </div>
                   </div>
                 </div>
               </div>
            @endif
            </div>
            <div class="col-xl-9 col-lg-8">
               <div class="card">
                  <div class="card-header d-flex justify-content-between">
                     <div class="header-title">
                        <h4 class="card-title">{{ isset($user) ? 'Edit User Information' : 'New User Information' }}
                        </h4>
                     </div>

                  </div>
                  <div class="card-body">
                     <div class="new-user-info">
                        <div class="row">
                           <div class="form-group col-md-6">
                              <label class="form-label" for="fname">First Name: <span
                                    class="text-danger">*</span></label>
                              <input type="text" name="fname" class="form-control" placeholder="First Name"
                                 value="{{ $user->fname ?? '' }}" required>
                           </div>
                           <div class="form-group col-md-6">
                              <label class="form-label" for="lname">Last Name: <span
                                    class="text-danger">*</span></label>
                              <input type="text" name="lname" class="form-control" placeholder="Last Name"
                                 value="{{ $user->lname ?? '' }}" required>
                           </div>

                           <div class="form-group col-md-6">
                              <label class="form-label" for="phone">Mobile Number:</label>
                              <input type="phone" name="phone" class="form-control" placeholder="Mobile Number"
                                 value="{{ $user->phone ?? '' }}" required>
                           </div>
                           <div class="form-group col-md-6">
                              <label class="form-label" for="email">Email: <span class="text-danger">*</span></label>
                              <input type="email" name="email" class="form-control" placeholder="Email"
                                 value="{{ $user->email ?? '' }}" required>
                           </div>
                        </div>
                        <hr>
                        <h5 class="mb-3">Security</h5>
                        <div class="row">
                           <div class="form-group col-md-6">
                              <label class="form-label" for="password">Password:</label>
                              <input type="password" name="password" class="form-control" placeholder="Password" {{ isset($user) ? '' : 'required' }}>
                           </div>
                           <div class="form-group col-md-6">
                              <label class="form-label" for="con_password">Repeat Password:</label>
                              <input type="password" name="password_confirmation" class="form-control"
                                 placeholder="Repeat Password" {{ isset($user) ? '' : 'required' }}>
                           </div>
                        </div>
                        <button type="button" class="btn btn-primary"
                           onclick="submitForm()">{{ isset($user) ? 'Update User' : 'Add User' }}</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @php
         $currentUrl = url()->current();
         $isEditStaffPage = preg_match('/\/admin\/staff\/editStaff\/\d+$/', $currentUrl);
        @endphp
         @if($isEditStaffPage)
          <div class="row mt-4">
            <div class="col-12"><!-- Sales Report Card -->
               <div class="card shadow-sm">
                 <!-- Header -->
                 <div class="card-header">
                   <h1 class="card-title text-center">Sales Report</h1>
                 </div>

                 <div class="card-body">
                   <!-- Day Selection Form -->
                   <form id="filterForm" class="form-inline mb-4">
                     <div class="row w-100">
                        <div class="col-md-4 col-sm-12 mb-3">
                          <div class="form-group w-100">
                            <label for="date" class="mr-2">Select Date:</label>
                            <input type="text" id="date" name="date" class="form-control w-100"
                              value="{{ request('date', date('Y-m-d')) }}">
                          </div>
                        </div>


                     </div>
                   </form>

                   <!-- Summary Statistics -->
                   <div class="row text-center mb-4">
                     <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalSales" class="card-text">Total Sales: ₱0.00</h5>
                     </div>
                     <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalOrders" class="card-text">Total Orders: 0</h5>
                     </div>
                     <div class="col-md-4 col-sm-12 mb-3">
                        <h5 id="totalQuantity" class="card-text">Total Quantity Sold: 0</h5>
                     </div>
                   </div>

                   <!-- Detailed Sales Data Table -->
                   <div class="table-responsive">
                     <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                          <tr>
                            <th class="text-center">Sale ID</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Product Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Total Price</th>
                          </tr>
                        </thead>
                        <tbody id="salesTableBody">
                          <!-- Table rows will be filled dynamically -->
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
       @endif
      </form>
   </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
   integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
   crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Include Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@latest"></script>
<script>
   function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function () {
         const output = document.getElementById('profile-pic');
         output.src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
   }

   function submitForm() {
      const form = document.getElementById('staff-form');
      const formData = new FormData(form);
      const url = "{{ isset($user) ? route('updateStaff', $user->id) : route('createStaff') }}";
      const method = "{{ isset($user) ? 'POST' : 'POST' }}";

      $.ajax({
         headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
         },
         url: url,
         type: method,
         data: formData,
         processData: false,
         contentType: false,
         success: function (response) {
            if (response.message) {
               alert(response.message);
               // Optionally, redirect or update the UI
               window.location.href = '/admin/staff/viewStaff'; // Redirect to /viewStaff
            } else {
               alert(response.errors);
            }
         },
         error: function (xhr, status, error) {
            console.error('Error:', error);

            // Parse the JSON response
            let response = JSON.parse(xhr.responseText);

            // Check if the response contains validation errors
            if (response.errors) {
               let errorMessages = '';

               // Loop through the errors and concatenate them into a single string
               for (let field in response.errors) {
                  if (response.errors.hasOwnProperty(field)) {
                     response.errors[field].forEach(function (message) {
                        errorMessages += message + '\n';
                     });
                  }
               }

               // Display the validation errors in an alert
               alert(errorMessages);
            } else {
               // Display the general error message
               alert('Error: ' + error);
            }
         }
      });
   }
</script>

<script>
   document.addEventListener('DOMContentLoaded', function () {
      // Initialize Flatpickr
      const salesDatePicker = flatpickr("#salesDate", {
         dateFormat: "Y-m-d",
         defaultDate: new Date(), // Set default date to today
         onChange: function (selectedDates, dateStr, instance) {
            if (dateStr) {
               fetchTotalSales(dateStr);
            }
         }
      });

      // Fetch total sales for today on page load
      fetchTotalSales(salesDatePicker.input.value);

      function fetchTotalSales(date) {
         @if(isset($user->id))
          const user_id = @json($user->id); // Convert PHP user ID to JavaScript variable
       @else
       const user_id = null;
    @endif
         fetch(`/total-sales?date=${date}&id=${user_id}`)
            .then(response => response.json())
            .then(data => {
               document.getElementById('totalSalesD').textContent = '₱' + data.totalSales;
            })
            .catch(error => console.error('Error fetching sales:', error));
      }
   });
</script>

<script>
   document.addEventListener('DOMContentLoaded', () => {
      const dateInput = document.getElementById('date');


      // Initialize Flatpickr
      flatpickr(dateInput, {
         dateFormat: "Y-m-d",
         defaultDate: new Date(), // Set default date to today
         onChange: function (selectedDates, dateStr, instance) {
            if (dateStr) {
               fetchSales(dateStr);
            }
         }
      });

      // Fetch total sales for today on page load
      fetchSales(dateInput.value);



      function fetchSales(date, page = 1) {
         // Get the staff ID from the URL
         const url = window.location.href;
         const urlSegments = url.split('/');
         const staffId = urlSegments[urlSegments.length - 1];

         // Use the staff ID in your data object
         const data = { date: date, staff: staffId, page: page };

         $.ajax({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            url: '{{ route('report') }}',
            type: 'POST',
            data: data,
            success: (response) => {
               // Update summary statistics
               document.getElementById('totalSales').textContent = `Total Sales: ₱${response.totalSales}`;
               document.getElementById('totalOrders').textContent = `Total Orders: ${response.totalOrders}`;
               document.getElementById('totalQuantity').textContent = `Total Quantity Sold: ${response.totalQuantity}`;

               // Update sales table
               const salesTableBody = document.getElementById('salesTableBody');
               salesTableBody.innerHTML = response.sales.data.map(sale => {
                  const saleDate = new Date(sale.sale_date);
                  const localDate = saleDate.toLocaleString('en-US', {
                     timeZone: 'Asia/Manila',
                     year: 'numeric',
                     month: '2-digit',
                     day: '2-digit',
                     hour: '2-digit',
                     minute: '2-digit',
                     second: '2-digit'
                  });

                  return `
                    <tr>
                        <td>${sale.id}</td>
                        <td>${localDate}</td>
                        <td>${sale.product.product_name}</td>
                        <td>${sale.quantity_sold}</td>
                        <td>₱${parseFloat(sale.inventory ? sale.inventory.price : 0).toFixed(2)}</td>
                        <td>₱${parseFloat((sale.inventory ? sale.inventory.price : 0) * sale.quantity_sold).toFixed(2)}</td>
                    </tr>
                `;
               }).join('');

               // Update pagination links
               const paginationContainer = document.getElementById('paginationContainer');
               paginationContainer.innerHTML = '';

               // Previous Page Link
               if (response.current_page > 1) {
                  paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page - 1}">&laquo;</a></li>`;
               } else {
                  paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
               }

               // Page Number Links
               for (let page = 1; page <= response.last_page; page++) {
                  if (page === response.current_page) {
                     paginationContainer.innerHTML += `<li class="page-item active"><span class="page-link">${page}</span></li>`;
                  } else {
                     paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
                  }
               }

               // Next Page Link
               if (response.current_page < response.last_page) {
                  paginationContainer.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page + 1}">&raquo;</a></li>`;
               } else {
                  paginationContainer.innerHTML += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
               }

               // Attach click events to pagination links
               paginationContainer.querySelectorAll('a').forEach(link => {
                  link.addEventListener('click', function (e) {
                     e.preventDefault();
                     const page = this.getAttribute('data-page');
                     fetchSales(date, page);
                  });
               });
            },
            error: (xhr) => {
               console.error('Error:', xhr.responseText);
               const response = JSON.parse(xhr.responseText);
               const errorMessage = response.message + "\n" + (response.errors ? response.errors.join("\n") : "");
               alert(errorMessage);
            }
         });
      }
   });
</script>