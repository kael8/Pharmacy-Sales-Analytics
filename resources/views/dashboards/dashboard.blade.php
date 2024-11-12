<x-app-layout :assets="$assets ?? []">
   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

   <!-- Swiper JS -->
   <style>
      .swiper-container {
         width: 100%;
         padding: 10px 0;
      }

      .swiper-slide {
         display: flex;
         justify-content: center;
         align-items: center;
         min-width: 20%;
         /* Adjust to control card width */
      }

      .card-slide {
         width: 100%;
         /* Ensure the cards take full width inside the swiper slide */
         /* Adjust based on your content */
      }

      /* Adjust the print area for PDF */
      #print {
         padding: 20px;
      }

      .card {
         border: 1px solid grey;
         /* Example border */
         box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
         /* Example shadow */
         padding: 15px;
         border-radius: 8px;
         /* Optional: for rounded corners */
      }

      .spinner {
         border: 4px solid rgba(0, 0, 0, 0.1);
         border-top: 4px solid #333;
         border-radius: 50%;
         width: 30px;
         height: 30px;
         animation: spin 1s linear infinite;
         margin: auto;
      }

      @keyframes spin {
         0% {
            transform: rotate(0deg);
         }

         100% {
            transform: rotate(360deg);
         }
      }



      @media print {
         .page-break {
            page-break-before: always;
         }
      }
   </style>

   <div class="container mt-4">


      <div id="printSection1"> <!-- Wrap the content to be printed in a div with id="print" -->
         <div class="row">
            <div class="col-md-12 col-lg-12 mb-4" id="dash">
               <div class="row row-cols-1">
                  <div class="d-slider1 overflow-hidden swiper-container"> <!-- Add swiper-container class here -->
                     <ul class="swiper-wrapper list-inline m-0 p-0 mb-0">
                        <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="700">
                           <x-number-sales></x-number-sales>
                        </li>


                        <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="1000">
                           <x-cost></x-cost>
                        </li>
                        <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="900">
                           <x-profit></x-profit>
                        </li>
                        <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="800">
                           <x-revenue></x-revenue>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>

            <div class="col-md-12 col-lg-8">
               <div class="row">
                  <div class="col-md-12">
                     <x-gross-sales></x-gross-sales>
                  </div>
                  <div class="col-md-12">
                     <x-sales-trends></x-sales-trends>
                  </div>



               </div>
            </div>
            <div class="col-md-12 col-lg-4">
               <div class="row">


                  <div class="col-md-6 col-lg-12">
                     <x-net-profit></x-net-profit>
                  </div>
               </div>
            </div>
         </div>

      </div>
      <!-- Second Print Section for x-sales-prediction -->
      <div id="printSection2" class="page-break">
         <div class="row">
            <div class="col-md-12">
               <x-top-products></x-top-products>
            </div>
         </div>
         <div class="row">
            <div class="container mt-4">
               <div class="row">
                  <div class="col-xl-12 col-lg-12">
                     <div class="card shadow-sm">
                        <div class="card-header text-center">
                           <h4 class="card-title">Stock Remaining</h4>
                        </div>

                        <div class="card-body">


                           <div class="table-responsive">
                              <table class="table table-striped table-bordered table-hover">
                                 <thead class="thead-light">
                                    <tr>
                                       <th scope="col">Batch Code</th>
                                       <th scope="col">Product Name</th>
                                       <th scope="col">Quantity</th>
                                       <th scope="col">Sold</th>
                                       <th scope="col">Remaining Stock</th>
                                    </tr>
                                 </thead>
                                 <tbody id="productsTableBody">
                                    <!-- Loading spinner by default -->
                                    <tr id="loadingSpinner">
                                       <td colspan="5" style="text-align: center;">
                                          <div class="spinner"></div>
                                          <p>Loading...</p>
                                       </td>
                                    </tr>
                                    <!-- Dynamic product rows will be populated here via AJAX -->
                                 </tbody>
                              </table>



                           </div>
                        </div>
                     </div>
                     <div class="card shadow-sm">
                        <!-- Header -->
                        <div class="card-header">
                           <h4 class="card-title text-center">Sales Report</h4>
                        </div>

                        <div class="card-body">


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
                                       <th>Sale ID</th>
                                       <th>Date</th>
                                       <th>Product Name</th>
                                       <th>Quantity</th>
                                       <th>Unit Price</th>
                                       <th>Total Price</th>
                                    </tr>
                                 </thead>
                                 <tbody id="salesTableBody">
                                    <!-- Loading spinner by default -->
                                    <tr id="loadingSpinner">
                                       <td colspan="6" style="text-align: center;">
                                          <div class="spinner"></div>
                                          <p>Loading...</p>
                                       </td>
                                    </tr>
                                    <!-- Table rows will be dynamically inserted here -->
                                 </tbody>
                              </table>
                           </div>



                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Centered and styled print button -->
      <div class="print-btn-container">
         <button id="printBtn" class="btn btn-primary mb-4">Print</button>
      </div>
   </div>
   <input type="text" id="date" name="date" class="form-control w-100" value="{{ request('date', date('Y-m-d')) }}"
      hidden>
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
   <!-- Include Flatpickr -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
   <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest"></script>
   <script>
      document.addEventListener('DOMContentLoaded', function () {
         var swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 10,
            breakpoints: {
               768: { slidesPerView: 2, spaceBetween: 15 },
               992: { slidesPerView: 3, spaceBetween: 20 },
               1200: { slidesPerView: 4, spaceBetween: 30 },
            },
            loop: false,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
         });
         var period = 'week';
         // Event listener for period selection
         document.querySelectorAll('.dropdown-item-dash').forEach(item => {
            item.addEventListener('click', function () {
               period = this.getAttribute('data-period');
            });
         });

         // Function to update the date and time display
         function updateDateTime() {
            const now = new Date();
            const currentDate = now.toLocaleDateString(undefined, {
               year: 'numeric',
               month: 'long',
               day: 'numeric'
            });
            const currentTime = now.toLocaleTimeString(undefined, {
               hour: '2-digit',
               minute: '2-digit',
               second: '2-digit'
            });
            document.getElementById('dateTimeDisplay').innerText = `Date and Time: ${currentDate} ${currentTime}`;
         }

         // Update the date and time display initially and every second
         updateDateTime();
         setInterval(updateDateTime, 1000);

         document.getElementById('printBtn').addEventListener('click', () => {
            const section1 = document.getElementById('printSection1');
            const section2 = document.getElementById('printSection2');

            period = period.charAt(0).toUpperCase() + period.slice(1);

            // Capture both sections as images using html2canvas
            Promise.all([html2canvas(section1), html2canvas(section2)]).then(canvases => {
               const [canvas1, canvas2] = canvases;
               const image1 = canvas1.toDataURL('image/png');
               const image2 = canvas2.toDataURL('image/png');
               const printWindow = window.open('', '_blank');

               // Get the current date and time in a readable format
               const currentDate = new Date().toLocaleDateString(undefined, {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric'
               });
               const currentTime = new Date().toLocaleTimeString(undefined, {
                  hour: '2-digit',
                  minute: '2-digit',
                  second: '2-digit'
               });

               // Set up the HTML structure for the new window
               printWindow.document.write('<html><head><title>Print</title></head><body>');
               printWindow.document.write('<style>@media print {  .page-break { page-break-before: always; }}</style>');

               // Heading for the new window with date and time
               printWindow.document.write(`
   <h1 style="text-align: center; font-family: Arial; font-size: 24px; color: #333;">
      ${period} Performance Report
   </h1>
   <p style="text-align: center; font-family: Arial; font-size: 16px; color: #555; margin-top: 5px;">
      An overview of key metrics and insights for the selected reporting period.
   </p>
   <p style="text-align: center; font-family: Arial; font-size: 14px; color: #777; margin-top: 5px;">
      Report Date: ${currentDate} ${currentTime}
   </p>
`);

               // Insert the first image
               printWindow.document.write(`<img src="${image1}" style="width:100%; display:block;" />`);

               // Page break before the second section
               printWindow.document.write('<div class="page-break"></div>');

               // Insert the second image
               printWindow.document.write(`<img src="${image2}" style="width:100%; display:block;" />`);

               // Close the document and initiate print
               printWindow.document.write('</body></html>');
               printWindow.document.close();

               printWindow.onload = () => {
                  printWindow.print();
                  printWindow.close();
               };
            });
         });
      });
   </script>

   <script>
      function loadProducts(page = 1, searchQuery = '') {
         // Show the loading spinner initially
         const loadingSpinner = document.getElementById('loadingSpinner');
         loadingSpinner.style.display = 'table-row';

         $.ajax({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            url: '{{ route('sales.stock') }}', // Adjust this to your route
            type: 'GET',
            data: { page: page, search: searchQuery },
            success: (response) => {
               // Hide loading spinner after data load
               loadingSpinner.style.display = 'none';

               // Populate products table
               const productsTableBody = document.getElementById('productsTableBody');
               productsTableBody.innerHTML = response.data.map(product => `
            <tr class="product-row" data-id="${product.product.id}">
               <td>${product.batch_id}</td>
               <td>${product.product.product_name}</td>
               <td>${parseInt((Number(product.quantity) + Number(product.quantity_sold)) ?? 0)}</td>
               <td>${product.quantity_sold}</td>
               <td>${product.quantity}</td>
            </tr>
         `).join('');

               // Add click event listeners for row redirection
               document.querySelectorAll('.product-row').forEach(row => {
                  row.addEventListener('click', function () {
                     const productId = this.getAttribute('data-id');
                     window.location.href = `/inventory/editProduct?id=${productId}`;
                  });
               });
            },
            error: (xhr) => {
               // Hide loading spinner on error
               loadingSpinner.style.display = 'none';
               console.error('Error:', xhr.responseText);
            }
         });
      }

      // Load the first page of products on page load
      document.addEventListener('DOMContentLoaded', function () {
         loadProducts();
      });

   </script>

   <script>
      document.addEventListener('DOMContentLoaded', () => {
         const dateInput = document.getElementById('date');
         const loadingSpinner = document.getElementById('loadingSpinner');

         // Set default value to today's date
         const today = new Date();
         const formattedToday = today.toISOString().split('T')[0];
         dateInput.value = formattedToday;

         // Initialize Flatpickr
         flatpickr(dateInput, {
            dateFormat: "Y-m-d",
            defaultDate: today,
            onChange: function (selectedDates, dateStr) {
               if (dateStr) {
                  fetchSales(dateStr);
               }
            }
         });

         // Fetch total sales for today on page load
         fetchSales(formattedToday);

         function fetchSales(date) {
            const data = { date: date };

            // Show the spinner by default
            loadingSpinner.style.display = 'table-row';

            $.ajax({
               headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
               url: '{{ route('sales.report') }}',
               type: 'GET',
               data: data,
               success: (response) => {
                  // Hide loading spinner after successful data load
                  loadingSpinner.style.display = 'none';

                  // Update summary statistics
                  document.getElementById('totalSales').textContent = `Total Sales: ₱${response.totalSales}`;
                  document.getElementById('totalOrders').textContent = `Total Orders: ${response.totalOrders}`;
                  document.getElementById('totalQuantity').textContent = `Total Quantity Sold: ${response.totalQuantity}`;

                  // Populate sales table
                  const salesTableBody = document.getElementById('salesTableBody');
                  salesTableBody.innerHTML = response.sales.map(sale => {
                     const saleDate = new Date(sale.sale_date);
                     const localDate = saleDate.toLocaleString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' });

                     return `
                  <tr>
                     <td>${sale.id}</td>
                     <td>${localDate.split(',')[0]}</td>
                     <td>${sale.product.product_name}</td>
                     <td>${sale.quantity_sold}</td>
                     <td>₱${parseFloat(sale.inventory ? sale.inventory.price : 0).toFixed(2)}</td>
                     <td>₱${parseFloat((sale.inventory ? sale.inventory.price : 0) * sale.quantity_sold).toFixed(2)}</td>
                  </tr>
               `;
                  }).join('');
               },
               error: (xhr) => {
                  // Hide loading spinner on error
                  loadingSpinner.style.display = 'none';

                  console.error('Error:', xhr.responseText);
                  const response = JSON.parse(xhr.responseText);
                  const errorMessage = response.message + "\n" + (response.errors ? response.errors.join("\n") : "");
                  alert(errorMessage);
               }
            });
         }
      });



   </script>
</x-app-layout>