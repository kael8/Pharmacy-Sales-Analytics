<x-app-layout :assets="$assets ?? []"><!-- Swiper CSS -->
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
   </style>

   <div class="row">
      <div class="col-md-12 col-lg-12 mb-4">
         <div class="row row-cols-1">
            <div class="d-slider1 overflow-hidden swiper-container"> <!-- Add swiper-container class here -->
               <ul class="swiper-wrapper list-inline m-0 p-0 mb-0">
                  <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="700">
                     <x-number-sales></x-number-sales>
                  </li>
                  <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="800">
                     <x-revenue></x-revenue>
                  </li>
                  <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="900">
                     <x-profit></x-profit>
                  </li>
                  <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="1000">
                     <x-cost></x-cost>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <script>document.addEventListener('DOMContentLoaded', function () {
            var swiper = new Swiper('.swiper-container', {
               slidesPerView: 1,    // 1 slide per view on small screens (mobile)
               spaceBetween: 10,    // Space between slides
               breakpoints: {
                  768: {
                     slidesPerView: 2,  // Show 2 slides on tablet-sized devices
                     spaceBetween: 15,
                  },
                  992: {
                     slidesPerView: 3,  // Show 3 slides on smaller desktops
                     spaceBetween: 20,
                  },
                  1200: {
                     slidesPerView: 4,  // Show 4 slides on larger desktops
                     spaceBetween: 30,
                  },
               },
               loop: false,         // Optional: Set to true if you want to loop the slides
               pagination: {
                  el: '.swiper-pagination',
                  clickable: true,
               },
               navigation: {
                  nextEl: '.swiper-button-next',
                  prevEl: '.swiper-button-prev',
               },
            });
         });
      </script>


      <div class="col-md-12 col-lg-8">
         <div class="row">
            <div class="col-md-12">
               <x-gross-sales></x-gross-sales>
            </div>

            <div class="col-md-12">
               <x-sales-prediction></x-sales-prediction>
            </div>
         </div>
      </div>
      <div class="col-md-12 col-lg-4">
         <div class="row">
            <div class="col-md-6 col-lg-12">
               <x-sales-trends></x-sales-trends>

            </div>
            <div class="col-md-6 col-lg-12">
               <x-top-products></x-top-products>
            </div>

            <div class="col-md-6 col-lg-12">
               <x-net-profit></x-net-profit>
            </div>

         </div>

      </div>
   </div>
</x-app-layout>

<script>
   function updateDashboard(period) {
      console.log('Fetching profit for period:', period); // Debugging log
      fetchSalesCount(period);


   }
</script>