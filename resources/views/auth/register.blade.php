<x-guest-layout>
   <section class="login-content">
      <div class="row m-0 align-items-center bg-white vh-100">
         <div class="col-md-12">
            <div class="row justify-content-center">
               <div class="col-md-6">
                  <div class="card card-transparent shadow-xl d-flex justify-content-center mb-0 auth-card">
                     <div class="card-body">
                     @php
                  $dashboardRoute = auth()->user()->role === 'Staff' ? 'staff.dashboard' : (auth()->user()->role === 'Manager' ? 'manager.dashboard' : 'dashboard');
               @endphp
                     
                     <a class="nav-link {{ activeRoute(route($dashboardRoute)) }}" aria-current="page" href="{{ route($dashboardRoute) }}">
                           <h4 class="logo-title text-center">{{env('APP_NAME')}}</h4>
                           <!-- Added text-center class here -->
                        </a>
                        <h2 class="mb-2 text-center">Sign Up</h2>
                        <p class="text-center">Create your {{env('APP_NAME')}} account.</p>
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />
                        <form method="POST" action="{{ route('register') }}" data-toggle="validator">
                           {{csrf_field()}}
                           <div class="row">
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label for="fname" class="form-label">First Name</label>
                                    <input id="fname" name="fname" class="form-control" type="text"
                                       placeholder="Endter First Name" required autofocus>
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label for="lname" class="form-label">Last Name</label>
                                    <input class="form-control" type="text" name="lname" placeholder="Enter Last Name"
                                       required>
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" placeholder="Enter Email" id="email"
                                       name="email" required>
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label>Phone <span class="text-danger">*</span></label>
                                    <input class="form-control" type="phone" placeholder="Enter Phone Number" id="phone"
                                       name="phone" required>
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="Enter Password"
                                       id="password" name="password" required autocomplete="new-password">
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-group">
                                    <label for="confirm-password" class="form-label">Confirm Password</label>
                                    <input id="password_confirmation" class="form-control" type="password"
                                       placeholder="Confirm Password" name="password_confirmation" required>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center">
                                 <div class="form-check mb-3">
                                    <label class="form-check-label" for="customCheck1">I agree with the terms of
                                       use</label>
                                    <input type="checkbox" class="custom-control-input" id="customCheck1" required>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center">
                                 <button type="submit" class="btn btn-primary"> {{ __('sign up') }}</button>
                              </div>
                              <p class="mt-3 text-center">
                                 Already have an Account <a href="{{route('auth.signin')}}" class="text-underline">Sign
                                    In</a>
                              </p>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</x-guest-layout>