<x-app-layout :assets="$assets ?? []">
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
                        @php
$profileImagePath = isset($profile_image->name) ? Storage::url($profile_image->name) : asset('images/avatars/01.png');
                  @endphp
                        
                        <img id="profile-pic" src="{{ $profileImagePath }}" alt="User-Profile" class="profile-pic rounded avatar-100">
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
            </div>
            <div class="col-xl-9 col-lg-8">
               <div class="card">
                  <div class="card-header d-flex justify-content-between">
                     <div class="header-title">
                        <h4 class="card-title">{{ isset($user) ? 'Edit User Information' : 'New User Information' }}
                        </h4>
                     </div>
                     <div class="card-action">
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary" role="button">Back</a>
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
      </form>
   </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
   integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
   crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
               window.location.href = '/viewStaff'; // Redirect to /viewStaff
            } else {
               alert('An error occurred. Please try again.');
            }
         },
         error: function (xhr, status, error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
         }
      });
   }
</script>