@extends('components.app')

@section('content')

<style>
    #usersTable td{
        white-space: nowrap;
    }

    /* Ensure parent structure doesn't mess up the layout */
    .table-responsive-wrapper {
        position: relative;
    }

    .fixed-header-scroll {
        position: sticky;
        top: 0;
        z-index: 999; /* Make sure it stays on top */
        background-color: #fff;
        border-bottom: 2px solid #ddd;
    }

    .table-responsive {
        max-height: 670px; /* Adjust this based on your layout */
        overflow-y: auto;
        overflow-x: auto;
    }

    /* Style the scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 12px; /* Scrollbar width */
        height: 12px; /* Horizontal scrollbar height */
    }

    /* Track (the empty space behind the scrollbar) */
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1; /* Light gray track */
    }

    /* Handle (the draggable part of the scrollbar) */
    .table-responsive::-webkit-scrollbar-thumb {
        background: #6f767e; /* Primary color, change to your color */
        border-radius: 10px; /* Rounded edges */
    }

    /* Hover effect on the handle */
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #233446; /* Darker shade on hover */
    }

    /* Style for horizontal scrollbar */
    .table-responsive::-webkit-scrollbar-horizontal {
        height: 10px; /* Horizontal scrollbar height */
    }
</style>

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bx-male-female text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">User Management</h4>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    @csrf
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="edit_usertype_id" class="form-label required">User Type</label>
                            <select class="form-control" id="edit_usertype_id" name="usertype_id">
                                <option value="">Select User Type</option>
                            </select>
                            <small class="text-danger" id="edit_validate_usertype">Please select user type</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="edit_first_name" class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name">
                            <small class="text-danger" id="edit_validate_first_name">Please enter first name</small>
                        </div>
                        <div class="col-md">
                            <label for="edit_last_name" class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name">
                            <small class="text-danger" id="edit_validate_last_name">Please enter last name</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="edit_email" class="form-label required">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                            <small class="text-danger" id="edit_validate_email">Please enter valid email</small>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md">
                            <label for="edit_team" class="form-label">Group</label>
                            <select class="form-control" id="edit_team" name="team_id">
                                <option value="">Select Group</option>
                            </select>
                            <small class="text-danger" id="edit_validate_team">Please select group</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="edit_password" class="form-label text-info">Update Password?</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <small class="text-danger" id="edit_validate_password">Please enter password</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark">Update User</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add User Form --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card" id="addUserCard" style="display: none;">
            <div class="card-header">
                <h5 class="text-primary card-title">Add New User</h5>
            </div>
            <div class="card-body">
                <form id="addUserForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md">
                            <label for="usertype_id" class="form-label required">User Type</label>
                            <select class="form-control" id="usertype_id" name="usertype_id">
                                <option value="">Select User Type</option>
                            </select>
                            <small class="text-danger" id="validate_usertype_id">Please select user type</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="first_name" class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name">
                            <small class="text-danger" id="validate_first_name">Please enter first name</small>
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name">
                            <small class="text-danger" id="validate_last_name">Please enter last name</small>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <small class="text-danger" id="validate_email">Please enter valid email</small>
                        </div>
                    </div>

                    <div class="row mb-3" id="teamRow" style="display: none;">
                        <div class="col-md">
                            <label for="team" class="form-label">Group</label>
                            <select class="form-control" id="team" name="team_id">
                                <option value="">Select Group</option>
                            </select>
                            <small class="text-danger" id="validate_team">Please select group</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-label-danger" id="cancelAddUserBtn">Cancel</button>
                            <button type="submit" class="btn btn-dark">Add User</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add User Button --}}
<div class="row mb-2">
    <div class="col-md d-flex justify-content-end">
        <button class="btn btn-primary me-2" id="addUserBtn">Add New User</button>
        <button class="btn btn-danger d-none" id="changeUsersPassword">Change Agents Password</button>
    </div>
</div>

{{-- Users Table --}}
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                {{-- <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-hover" style="width:100%">
                    </table>
                </div> --}}
                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="usersTable" class="table table-bordered table-hover" style="width:100%">
                          <tbody>
                          </tbody>
                        </table>
                      </div>
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
        // Hide validation messages initially
        $(".text-danger").hide();

        // Initialize DataTable
        const usersTable = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("user.management.list") }}',
            lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
            pageLength: 10,
            paging: true,
            responsive: true,
            dom: '<"top"lf>rt<"bottom"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search..."
            },
            order: [[5, 'desc']], // Sort by 'updated_at' (6th column, index 5) in descending order

            columns: [
                {
                    data: 'id',
                    name: 'id',
                    title: 'Name',
                    render: function(data, type, row) {
                        return row.first_name + ' ' + row.last_name;
                    }
                },
                { data: 'email', name: 'email', title: 'Email' },
                { data: 'usertype', name: 'usertype', title: 'User Type' },
                { data: 'team', name: 'team', title: 'Group' },
                { data: 'status', name: 'status', title: 'Status' },
                // { data: 'updated_at', name: 'updated_at', title: 'Updated_at' },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    title: 'Updated_at',
                    render: {
                        _: 'display',   // shows formatted date
                        sort: 'timestamp' // sorts by raw timestamp
                    }
                },

                {
                    data: 'id',
                    name: 'id',
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<div class="d-flex gap-2">
                            <button type="button" class="btn btn-icon btn-success edit-user" data-id="${data}" title="Edit User">
                                <span class="tf-icons bx bx-pencil"></span>
                            </button>
                            <button type="button" class="btn btn-icon btn-warning send-temporary-password" data-id="${data}" title="Generate Temporary Password">
                                <span class="tf-icons bx bx-key"></span>
                            </button>
                        </div>`;
                    }
                }

            ],
            columnDefs: [
                // {
                //     targets: 0, // The 'Name' column (zero-based index)
                //     render: function(data, type, row) {
                //         if (type === 'display') {
                //             return (row.first_name + ' ' + row.last_name).toUpperCase();
                //         }
                //         return data;
                //     }
                // },
                {
                    targets: 2, // The 'User Type' column (zero-based index)
                    render: function(data, type, row) {
                        return type === 'display' && typeof data === 'string' ? data.toUpperCase() : data;
                    }
                },
                {
                    targets: 4, // The 'User Type' column (zero-based index)
                    render: function(data, type, row) {
                        return type === 'display' && typeof data === 'string' ? data.toUpperCase() : data;
                    }
                }
            ]
        });

        // Show/Hide Add User Form
        $('#addUserBtn').click(function() {
            $('#addUserCard').show();
            $('#addUserForm')[0].reset();
            $('#usertype').val('');
            $('#team').val('');
            $(".text-danger").hide();
        });

        $('#cancelAddUserBtn').click(function() {
            $('#addUserCard').hide();
            $('#addUserForm')[0].reset();
            $(".text-danger").hide();
        });

        // Load User Types and Teams
        function loadUserTypes() {
            $.ajax({
                url: '{{ route("usertypes.list") }}',
                type: 'GET',
                success: function(data) {
                    let options = '<option value="">Select User Type</option>';
                    data.forEach(function(type) {
                        options += `<option value="${type.id}">${type.name}</option>`;
                    });
                    $('#usertype_id, #edit_usertype_id').html(options);
                }
            });

            // Handle change event to show/hide #teamRow
            $('#usertype_id').on('change', function() {
                const selectedType = $(this).find('option:selected').text().toLowerCase();
                if (selectedType === 'agent' || selectedType === 'group manager') {
                    $('#teamRow').show(); // Show the team row
                } else {
                    $('#teamRow').hide(); // Hide the team row
                }
            });

            // Ensure #teamRow is hidden initially
            $('#teamRow').hide();
        }

        function loadTeams() {
            $.ajax({
                url: '{{ route("teams.list") }}',
                type: 'GET',
                success: function(data) {
                    let options = '<option value="">Select Group</option>';
                    data.forEach(function(team) {
                        options += `<option value="${team.id}">${team.name}</option>`;
                    });
                    $('#team, #edit_team').html(options);
                }
            });
        }

        loadUserTypes();
        loadTeams();

        // Add User Form Submission
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                first_name: $('#first_name').val(),
                last_name: $('#last_name').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                usertype_id: $('#usertype_id').val(),
                team_id: $('#team').val()
            };

            $.ajax({
                url: '{{ route("user.management.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        }).then(() => {
                            $('#addUserCard').hide();
                            $('#addUserForm')[0].reset();
                            usersTable.ajax.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(field => {
                        $(`#validate_${field}`).text(errors[field][0]).show();
                        $(`#${field}`).addClass('border-danger');
                    });
                }
            });
        });

        // Edit User
        $(document).on('click', '.edit-user', function(e) {
            e.preventDefault();
            let userId = $(this).data('id');
            $.ajax({
                url: `user-management/${userId}/edit`,
                type: 'GET',
                success: function(response) {
                    $('#edit_user_id').val(userId);
                    $('#edit_first_name').val(response.first_name);
                    $('#edit_last_name').val(response.last_name);
                    $('#edit_email').val(response.email);
                    $('#edit_usertype_id').val(response.usertype_id);
                    $('#edit_team').val(response.team_id);
                    $('#edit_status').val(response.status);
                    $('#edit_password').val('');
                    $('#editUserModal').modal('show');
                }
            });
        });

        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: '{{ route("user.management.update") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        }).then(() => {
                            $('#editUserModal').modal('hide');
                            usersTable.ajax.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(field => {
                        $(`#edit_validate_${field}`).text(errors[field][0]).show();
                        $(`#edit_${field}`).addClass('border-danger');
                    });
                }
            });
        });

        $(document).on('click', '.send-temporary-password', function(e) {
            e.preventDefault();
            let userId = $(this).data('id');
            $.ajax({
                url: `user-management/${userId}/send-temporary-password`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Temporary Password',
                            text:  response.password,
                        })
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'An error occurred',
                    });
                }
            });
        });

        // Real-time Uppercase Transformation
        $("input[type='text'], textarea").on("input", function () {
            $(this).val($(this).val().toUpperCase());
        });

        $('#changeUsersPassword').click(function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "This will change the password of all agents to a temporary password. Do you want to proceed?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change all agents password'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                    url: '{{ route("user.managemen.changePasswordAll") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                    Swal.fire({
                        title: 'Loading...',
                        html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    },
                    success: function(response) {
                        Swal.close(); // Hide loader

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Temporary password has been sent to all agents',
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close(); // Hide loader

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'An error occurred',
                        });
                    }
                });
                }
            });


        });

    });
</script>
@endsection
