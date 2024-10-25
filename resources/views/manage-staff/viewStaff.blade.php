<x-app-layout>
   <div>
      <div class="row">
         <div class="col-xl-12 col-lg-12">
            <div class="card">
               <div class="card-header">
                  <div class="header-title">
                     <h4 class="card-title text-center">Staff</h4>
                  </div>
               </div>
               <div class="card-body">
                  <div class="bd-example table-responsive">
                     <table class="table table-striped table-hover">
                        <thead>
                           <tr>
                              <th scope="col">#</th>
                              <th scope="col">Name</th>
                              <th scope="col">Email</th>
                              <th scope="col">Phone</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($staffs as $staff)
                        <tr>
                          <th scope="row">
                            <a href="{{ url('/admin/staff/editStaff/' . $staff->id) }}"
                              class="text-decoration-none text-dark">
                              {{ $loop->iteration }}
                            </a>
                          </th>
                          <td>
                            <a href="{{ url('/admin/staff/editStaff/' . $staff->id) }}"
                              class="text-decoration-none text-dark">
                              {{ $staff->fname }} {{ $staff->lname }}
                            </a>
                          </td>
                          <td>
                            <a href="{{ url('/admin/staff/editStaff/' . $staff->id) }}"
                              class="text-decoration-none text-dark">
                              {{ $staff->email }}
                            </a>
                          </td>
                          <td>
                            <a href="{{ url('/admin/staff/editStaff/' . $staff->id) }}"
                              class="text-decoration-none text-dark">
                              {{ $staff->phone }}
                            </a>
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
</x-app-layout>