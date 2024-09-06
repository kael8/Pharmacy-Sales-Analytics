<x-guest-layout>
   <section class="login-content">
      <div class="row m-0 align-items-center bg-white vh-100">
         <div class="col-md-12">
            <div class="row justify-content-center">
               <div class="col-md-6">
                  <div class="card card-transparent shadow-xl d-flex justify-content-center mb-0 auth-card">
                     <div class="card-body">
                        <a href="{{route('dashboard')}}" class="navbar-brand d-flex justify-content-center mb-3">
                           <h4 class="logo-title text-center">{{env('APP_NAME')}}</h4>
                           <!-- Added text-center class here -->
                        </a>
                        <h2 class="mb-2 justify-content-center text-center">Sign In</h2>
                        <p class="justify-content-center text-center">Login to stay connected.</p>
                        <x-auth-session-status class="mb-4" :status="session('status')" />
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />
                        <form method="POST" action="{{ route('login') }}" data-toggle="validator">
                           {{csrf_field()}}
                           <div class="row">
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" type="email" name="email"
                                       value="{{env('IS_DEMO') ? 'admin@example.com' : old('email')}}"
                                       class="form-control" placeholder="admin@example.com" required autofocus>
                                 </div>
                              </div>
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="********" name="password"
                                       value="{{ env('IS_DEMO') ? 'password' : '' }}" required
                                       autocomplete="current-password">
                                 </div>
                              </div>
                              <div class="col-lg-6">
                                 <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="customCheck1">
                                    <label class="form-check-label" for="customCheck1">Remember Me</label>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center">
                                 <button type="submit" class="btn btn-primary"> {{ __('sign up') }}</button>
                              </div>
                              <div class="col-lg-6">
                                 <a href="{{route('auth.recoverpw')}}" class="float-end">Forgot Password?</a>
                              </div>
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