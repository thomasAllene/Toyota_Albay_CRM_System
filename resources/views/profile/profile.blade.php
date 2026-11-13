@extends('components.app')
@section('content')

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-user-detail text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Profile</h4>
        </div>
    </div>
</div>

  <!-- Modal -->
  <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">Update Profile Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="updateProfileForm">
            @csrf
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}" required />
                    </div>
                    <div class="col-md">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}" required />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" />
                        <small class="text-muted">Leave blank if you don't want to change password</small>
                    </div>
                    <div class="col-md">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-dark">Save changes</button>
            </div>
        </form>
      </div>
    </div>
  </div>

<div class="row ">
    <div class="col-md d-flex justify-content-center">
        <div class="card w-50">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <i class='bx bxs-user-circle text-dark fs-1'></i>
                    <h2 class="fw-bold text-dark">{{ $user->first_name }} {{ $user->last_name }}</h2>
                </div>
                <div class="divider divider-secondary">
                    <div class="divider-text">
                        <i class='bx bx-detail text-secondary' ></i>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <i class='bx bxs-envelope' ></i>
                    <div class="fw-bold">Email:</div>
                    <div>{{ $user->email }}</div>
                </div>
                <div class="d-flex gap-2">
                    <i class='bx bxs-badge' ></i>
                    <div class="fw-bold">Group:</div>
                    <div>{{ $user->team->name ?? 'N/A' }}</div>
                </div>
                <div class="d-flex gap-2">
                    <i class='bx bxs-user-badge'></i>
                    <div class="fw-bold">Position:</div>
                    <div>{{ $user->usertype->name ?? 'N/A' }}</div>
                </div>
                <div class="divider divider-secondary">
                    <div class="divider-text">
                        <i class='bx bx-time-five text-secondary'></i>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <i class='bx bx-revision'></i>
                    <div class="fw-bold">Last Updated:</div>
                    <div>{{ Carbon\Carbon::parse($user->updated_at)->format('M d, Y h:i A') }}</div>
                </div>
                @if($user->updated_by)
                <div class="d-flex gap-2">
                    <i class='bx bx-user-check'></i>
                    <div class="fw-bold">Updated By:</div>
                    <div>{{ App\Models\User::find($user->updated_by)->first_name ?? 'N/A' }} {{ App\Models\User::find($user->updated_by)->last_name ?? '' }}</div>
                </div>
                @endif

                <div class="row mt-3">
                    <div class="col-md d-flex justify-content-end">
                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#updateProfileModal">Update Profile</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection



@section('components.specific_page_scripts')
<script>
$(document).ready(function() {
    $('#updateProfileForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("profile.update") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Update the profile display
                $('.fw-bold.text-dark').text(response.user.first_name + ' ' + response.user.last_name);
                $('.bxs-envelope').next().next().text(response.user.email);

                // Update the last updated info
                $('.bx-revision').next().next().text(response.user.updated_at);
                $('.bx-user-check').next().next().text(response.user.updated_by);

                // Close modal and show success message
                $('#updateProfileModal').modal('hide');
                toastr.success(response.message);
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(function(key) {
                    toastr.error(errors[key][0]);
                });
            }
        });
    });
});
</script>
@endsection
