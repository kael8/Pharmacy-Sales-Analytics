<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
  <div class="container-fluid navbar-inner">
    @php
    $dashboardRoute = auth()->user()->role === 'Staff' ? 'staff.dashboard' : (auth()->user()->role === 'Manager' ? 'manager.dashboard' : 'dashboard');
    @endphp

    <a class="nav-link {{ activeRoute(route($dashboardRoute)) }}" aria-current="page"
      href="{{ route($dashboardRoute) }}">

      <h3 class="logo-title">{{env('APP_NAME')}}</h3>
    </a>
    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
      <i class="icon">
        <svg width="20px" height="20px" viewBox="0 0 24 24">
          <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
        </svg>
      </i>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      <span class="navbar-toggler-icon">
        <span class="navbar-toggler-bar bar1 mt-2"></span>
        <span class="navbar-toggler-bar bar2"></span>
        <span class="navbar-toggler-bar bar3"></span>
      </span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto navbar-list mb-2 mb-lg-0">

        <!-- Notification Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <svg width="20px" height="20px" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12 2C10.34 2 9 3.34 9 5V6.29C7.72 6.63 6.63 7.72 6.29 9H5C3.9 9 3 9.9 3 11V12C3 13.1 3.9 14 5 14H6V17C6 18.1 6.9 19 8 19H16C17.1 19 18 18.1 18 17V14H19C20.1 14 21 13.1 21 12V11C21 9.9 20.1 9 19 9H17.71C17.37 7.72 16.28 6.63 15 6.29V5C15 3.34 13.66 2 12 2M12 4C12.55 4 13 4.45 13 5V6H11V5C11 4.45 11.45 4 12 4M5 11V12H19V11H5M8 16V14H16V16H8Z" />
            </svg>
            <span class="badge bg-danger" id="notificationCount">3</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" id="notificationList">
            <li><a class="dropdown-item" href="#">Loading...</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{asset('images/avatars/01.png')}}" alt="User-Profile"
              class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{asset('images/avatars/avtar_1.png')}}" alt="User-Profile"
              class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{asset('images/avatars/avtar_2.png')}}" alt="User-Profile"
              class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{asset('images/avatars/avtar_4.png')}}" alt="User-Profile"
              class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{asset('images/avatars/avtar_5.png')}}" alt="User-Profile"
              class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{asset('images/avatars/avtar_3.png')}}" alt="User-Profile"
              class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded">
            <div class="caption ms-3 d-none d-md-block ">
              <h6 class="mb-0 caption-title">{{ auth()->user()->fname ?? 'User'  }}</h6>
              <p class="mb-0 caption-sub-title text-capitalize">
                {{ str_replace('_', ' ', auth()->user()->user_type) ?? 'Marketing Administrator' }}
              </p>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li>
              <form method="POST" action="{{route('logout')}}">
                @csrf
                <a href="javascript:void(0)" class="dropdown-item" onclick="event.preventDefault();
              this.closest('form').submit();">
                  {{ __('Log out') }}
                </a>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('notificationDropdown').addEventListener('click', function () {
      viewNotifications();
    });

    function viewNotifications() {
      $.ajax({
        url: '{{ route('markAsViewed') }}',
        method: 'GET',
        success: function (data) {

        },
        error: function (error) {
          console.error('Error viewing notifications:', error);
        }
      });
    }

    function fetchNotifications() {
      $.ajax({
        url: '{{ route('notifications') }}',
        method: 'GET',
        success: function (data) {
          $('#notificationList').empty();
          const quantityNotifications = data.quantity_notifications;
          const expirationNotifications = data.expiration_notifications;

          if (quantityNotifications.length > 0 || expirationNotifications.length > 0) {
            // Handle quantity notifications
            quantityNotifications.forEach(function (notification) {
              const initialQuantity = notification.initial_quantity; // Assuming initial_quantity is provided
              const currentQuantity = notification.quantity;
              const percentageRemaining = (currentQuantity / initialQuantity) * 100;

              let quantityColorClass = '';
              let quantityMessage = '';

              if (percentageRemaining > 50) {
                quantityColorClass = 'text-success'; // Green for 100% to 51%
                quantityMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') has ' + percentageRemaining.toFixed(2) + '% remaining.';
              } else if (percentageRemaining > 10) {
                quantityColorClass = 'text-warning'; // Yellow for 50% to 11%
                quantityMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') has ' + percentageRemaining.toFixed(2) + '% remaining.';
              } else {
                quantityColorClass = 'text-danger'; // Red for 10% to 1%
                quantityMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') has ' + percentageRemaining.toFixed(2) + '% remaining.';
              }

              $('#notificationList').append('<li><a class="dropdown-item ' + quantityColorClass + '" href="/inventory/viewInventoryBatches?sort=quantity">' + quantityMessage + '</a></li>');
            });

            // Handle expiration notifications
            expirationNotifications.forEach(function (notification) {
              const expirationDate = new Date(notification.expiration_date);
              const currentDate = new Date();
              const timeDiff = expirationDate - currentDate;
              const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

              let expirationColorClass = '';
              let expirationMessage = '';

              if (daysDiff <= 7) {
                expirationColorClass = 'text-danger'; // Red for less than or equal to 7 days
                expirationMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') is expiring soon on ' + notification.expiration_date;
              } else if (daysDiff <= 30) {
                expirationColorClass = 'text-warning'; // Yellow for less than or equal to 30 days
                expirationMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') is expiring on ' + notification.expiration_date;
              } else {
                expirationColorClass = 'text-success'; // Green for more than 30 days
                expirationMessage = 'Product ' + notification.product.product_name + ' (Batch: ' + notification.batch_id + ') is expiring on ' + notification.expiration_date;
              }

              $('#notificationList').append('<li><a class="dropdown-item ' + expirationColorClass + '" href="/inventory/viewInventoryBatches?sort=expiration_date">' + expirationMessage + '</a></li>');
            });

            $('#notificationCount').text(quantityNotifications.length + expirationNotifications.length);
          } else {
            $('#notificationList').html('<li><a class="dropdown-item" href="#">No notifications</a></li>');
            $('#notificationCount').text('0');
          }
        },
        error: function (error) {
          console.error('Error fetching notifications:', error);
        }
      });
    }
    fetchNotifications();
  });
</script>