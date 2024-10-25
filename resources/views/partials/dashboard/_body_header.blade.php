<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
  <div class="container-fluid navbar-inner">
    @php
    $dashboardRoute = auth()->user()->role === 'Staff' ? 'staff.dashboard' : (auth()->user()->role === 'Manager' ? 'manager.dashboard' : 'dashboard');
    @endphp

    <a class="nav-link {{ activeRoute(route($dashboardRoute)) }}" aria-current="page"
      href="{{ route($dashboardRoute) }}">

      <h4 class="logo-title">{{env('APP_NAME')}}</h4>
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
      <ul class="navbar-nav ms-auto  navbar-list mb-2 mb-lg-0">



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