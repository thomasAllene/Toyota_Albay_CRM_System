@extends('components.app')

@section('content')

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-user-account text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Team Management</h4>
        </div>
    </div>
</div>

{{-- Edit Team Modal --}}
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <div class="modal-body">
            <form action="" id="editTeamForm">
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="edit_team" class="form-label required">Team Name</label>
                        <input type="text" class="form-control" id="edit_team" name="name" placeholder="" aria-describedby="defaultFormControlHelp"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" aria-label="Default select example">
                                <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md d-flex justify-content-end gap-2">
                        <button class="btn btn-label-danger" id="cancelEditButton" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" id="editButton">Edit</button>
                        <button class="btn btn-dark" id="saveChangesButton" style="display: none;">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>

{{-- New Team Form --}}
<div class="row mb-2" id="teamFormRow" style="display: none;">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <form id="teamForm">
                    <div class="row mb-2">
                        <div class="col-md">
                            <label for="defaultFormControlInput" class="form-label required">Group Name</label>
                            <input type="text" class="form-control" id="team" name="name" placeholder="" aria-describedby="defaultFormControlHelp"/>
                            <div id="validateTeamName"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-label-danger" id=cancelTeamButton>Cancel</button>
                            <button type= "submit" class="btn btn-dark" id="addTeamButton">Add Group</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Create Team Button --}}
<div class="row mb-2">
    <div class="col-md d-flex justify-content-end">
        <button class="btn btn-primary" id="createTeamButton">Create Group</button>
    </div>
</div>

{{-- Users Table --}}
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="teamTable" class="table table-bordered table-hover" style="width:100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('components.specific_page_scripts')
<script>

    $(document).ready(function () {
        let id = null;
        $(document).on('click', '.edit-btn', function () {
             id = $(this).data('id');
            const name = $(this).data('name');
            const status = $(this).data('status');

            $('#edit_team').val(name);
            $('#edit_status').val(status).trigger('change');

        });

         // Datatable Initilization
         const teamTable = $('#teamTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('team.list') }}",
                type: 'GET',
            }, 
            pageLength: 10,
            paging: true,
            responsive: true,
            dom: '<"top"lf>rt<"bottom"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search..."
            },
            "ordering": true, // Ensures ordering is enabled
            "order": [[1, "asc"]], //Orders the first column in descending order
            columns: [
                { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'name', name: 'name', title: 'Group Name' },
                { data: 'updated_at', name: 'updated_at', title: 'Created At' },
                { data: 'updated_by', name: 'updated_by', title: 'Created By' },
                { data: 'status', name: 'status', title: 'Status' },
                {
                    data: 'action',
                    name: 'action',
                    title: 'Action',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editTeamModal" data-id="${row.id}" data-name="${row.name}" data-status="${row.status}" data-created-by="${row.created_by}" data-updated-by="${row.updated_by}">
                                <span class="tf-icons bx bx-pencil bx-22px"></span>
                            </button>
                        `;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: '_all', // Apply to all columns
                    render: function (data, type, row) {
                        // Guard against null/undefined so a null cell can't
                        // throw and freeze the table on "Processing...".
                        return type === 'display' && typeof data === 'string' ? data.toUpperCase() : data;
                    }
                }
            ],
        });

        // Edit Team
        $('#editTeamForm').submit(function(e){
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: `/team/update/${id}`,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response){
                    teamTable.ajax.reload();
                    $('#editTeamModal').modal('hide');
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success',
                    });
                },
                error: function(xhr, status, error){
                    Swal.fire({
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'An error occurred.',
                        icon: 'error',
                    });
                }
            });
        });

        // Create Team
        $('#teamForm').submit(function(e){
            e.preventDefault();
            const formData = $(this).serialize();

            const teamName = $('#team').val().trim();
            if (!teamName) {
                $('#team').addClass('is-invalid');
                $('#validateTeamName').html('<span style="color: red;">Please enter a group name</span>').show();
                return;
            } else {
                $('#team').removeClass('is-invalid');
                $('#validateTeamName').hide();
            }

            $.ajax({
                url: "{{ route('team.create') }}",
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response){
                    teamTable.ajax.reload();
                    $('#teamFormRow').hide();
                    $('#teamForm').trigger('reset');
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success',
                    });
                },
                error: function(xhr, status, error){
                    Swal.fire({
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'An error occurred.',
                        icon: 'error',
                    });
                }
            });
        });

        // Real-time Uppercase Transformation
        $("input[type='text'], textarea").on("input", function () {
            $(this).val($(this).val().toUpperCase());
        });

       

    });

    // hide show & reload Team Form
    $(document).ready(function () {
        // Show the team form when the "Create Team" button is clicked
        $('#createTeamButton').click(function () {
            $('#teamFormRow').show();
        });

        // Hide the team form and reset it when the "Cancel" button is clicked
        $('#cancelTeamButton').click(function () {
            $('#teamForm').trigger('reset');
            $('#teamFormRow').hide();

        });
    });

    // Edit Modal
    $(document).ready(function () {
        // Initially disable all form fields
        $('#editTeamForm input, #editTeamForm select').prop('disabled', true);

        // When the edit button is clicked
        $('#editButton').click(function (e) {
            e.preventDefault(); // Prevent form submission if it's a button inside a form

            // Enable all fields in the form
            $('#editTeamForm input, #editTeamForm select').prop('disabled', false);

            // Show the Save Changes button
            $('#saveChangesButton').show();

            // Hide the Edit button
            $(this).hide();
        });

        // When the cancel button is clicked
        $('#cancelEditButton').click(function (e) {
            e.preventDefault(); // Prevent form submission

            // Disable all fields again
            $('#editTeamForm input, #editTeamForm select').prop('disabled', true);

            // Show the Edit button
            $('#editButton').show();

            // Hide the Save Changes button
            $('#saveChangesButton').hide();
        });
    });




</script>
@endsection
